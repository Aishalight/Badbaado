<?php

namespace Tests\Feature;

use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesReferralStaff;
use Tests\TestCase;

class ConsoleFeatureTest extends TestCase
{
    use CreatesReferralStaff;
    use RefreshDatabase;

    public function test_public_registration_cannot_select_a_privileged_role(): void
    {
        $this->role('healthcare_worker');
        $this->role('system_admin');

        $this->postJson('/api/register', [
            'name' => 'Attempted Admin',
            'email' => 'attempted.admin@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_slug' => 'system_admin',
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('users', ['email' => 'attempted.admin@example.com']);
    }

    public function test_authenticated_users_can_render_their_server_side_dashboard(): void
    {
        $hospital = Hospital::factory()->create();
        $worker = $this->staff($hospital, 'healthcare_worker');

        $this->actingAs($worker)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Care team workspace')
            ->assertSee('Recent referrals');
    }

    public function test_hospital_admin_can_render_the_admin_console_but_workers_cannot(): void
    {
        $hospital = Hospital::factory()->create();
        $admin = $this->staff($hospital, 'hospital_admin');
        $worker = $this->staff($hospital, 'healthcare_worker');

        $this->actingAs($admin)->get('/admin/analytics')->assertOk()->assertSee('Network overview');
        $this->actingAs($worker)->get('/admin/analytics')->assertForbidden();
    }

    public function test_system_admin_can_render_hospital_management(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();

        $this->actingAs($admin)
            ->get('/admin/hospitals')
            ->assertOk()
            ->assertSee('Hospital directory');
    }

    public function test_session_authenticated_user_can_call_the_stateful_api(): void
    {
        $hospital = Hospital::factory()->create();
        $worker = $this->staff($hospital, 'healthcare_worker');

        $this->post('/login', [
            'email' => $worker->email,
            'password' => 'password',
        ])->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.email', $worker->email);
    }
}
