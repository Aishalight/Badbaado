<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesReferralStaff;
use Tests\TestCase;

class AuthFeatureTest extends TestCase
{
    use CreatesReferralStaff;
    use RefreshDatabase;

    public function test_me_returns_401_without_token(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_login_returns_token_and_user(): void
    {
        $this->role('healthcare_worker');
        $user = User::factory()->withRole('healthcare_worker')->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonStructure(['token']);
    }

    public function test_login_rejects_invalid_credentials_with_422(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'The provided credentials are incorrect.');
    }

    public function test_register_creates_healthcare_worker_and_returns_201(): void
    {
        $role = $this->role('healthcare_worker');

        $response = $this->postJson('/api/register', [
            'name' => 'New Clinician',
            'email' => 'new.clinician@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['token']);

        $this->assertDatabaseHas('users', [
            'email' => 'new.clinician@example.com',
            'role_id' => $role->getKey(),
        ]);
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth');
        $tokenId = $token->accessToken->getKey();

        $this->withToken($token->plainTextToken)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);

        $this->app['auth']->forgetGuards();

        $this->withToken($token->plainTextToken)->getJson('/api/me')->assertStatus(401);
    }

    public function test_me_returns_the_authenticated_user_with_role_and_hospital(): void
    {
        $this->role('referral_coordinator');
        $user = User::factory()->withRole('referral_coordinator')->create();
        $user->update(['hospital_id' => null]);

        Sanctum::actingAs($user->fresh());

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.role.slug', 'referral_coordinator')
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'role', 'hospital']]);
    }
}
