<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesReferralStaff;
use Tests\TestCase;

class UserStatusFeatureTest extends TestCase
{
    use CreatesReferralStaff;
    use RefreshDatabase;

    public function test_pending_user_cannot_obtain_an_api_token(): void
    {
        $user = User::factory()->pending()->create();

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'Your account is awaiting verification. We will email you once a system administrator has reviewed it.');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_rejected_user_login_returns_422_with_not_approved_message(): void
    {
        $user = User::factory()->create(['status' => UserStatus::REJECTED]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'Your application was not approved. Contact your administrator for details.');
    }

    public function test_suspended_user_login_returns_422_with_disabled_message(): void
    {
        $user = User::factory()->suspended()->create();

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'This account is disabled. Contact your administrator.');
    }

    public function test_admin_can_suspend_a_user_via_the_status_field(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();
        $target = User::factory()->withRole('healthcare_worker')->create();
        Sanctum::actingAs($admin);

        $this->patchJson("/api/admin/users/{$target->getKey()}", [
            'status' => UserStatus::SUSPENDED->value,
        ])->assertOk()
            ->assertJsonPath('data.status', UserStatus::SUSPENDED->value);

        $this->assertSame(UserStatus::SUSPENDED, $target->fresh()->status);
    }

    public function test_admin_can_activate_a_suspended_user_via_the_status_field(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();
        $target = User::factory()->suspended()->withRole('healthcare_worker')->create();
        Sanctum::actingAs($admin);

        $this->patchJson("/api/admin/users/{$target->getKey()}", [
            'status' => UserStatus::ACTIVE->value,
        ])->assertOk()
            ->assertJsonPath('data.status', UserStatus::ACTIVE->value);

        $this->assertSame(UserStatus::ACTIVE, $target->fresh()->status);
    }

    public function test_legacy_is_active_false_suspends_the_user(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();
        $target = User::factory()->withRole('healthcare_worker')->create();
        Sanctum::actingAs($admin);

        $this->patchJson("/api/admin/users/{$target->getKey()}", [
            'is_active' => false,
        ])->assertOk();

        $this->assertSame(UserStatus::SUSPENDED, $target->fresh()->status);
    }

    public function test_admin_cannot_suspend_their_own_account(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();
        Sanctum::actingAs($admin);

        $this->patchJson("/api/admin/users/{$admin->getKey()}", [
            'status' => UserStatus::SUSPENDED->value,
        ])->assertStatus(422)
            ->assertJsonPath('message', 'You cannot suspend your own account.');
    }

    public function test_status_field_rejects_an_unknown_value_with_422(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();
        $target = User::factory()->withRole('healthcare_worker')->create();
        Sanctum::actingAs($admin);

        $this->patchJson("/api/admin/users/{$target->getKey()}", [
            'status' => 'teleported',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('status');

        $this->assertSame(UserStatus::ACTIVE, $target->fresh()->status);
    }

    public function test_suspended_user_is_excluded_from_a_hospitals_referral_staff_picker(): void
    {
        $hospital = Hospital::factory()->create();
        $this->role('healthcare_worker');
        $active = User::factory()->atHospital($hospital->getKey())->withRole('healthcare_worker')->create();
        $suspended = User::factory()->atHospital($hospital->getKey())->withRole('healthcare_worker')->suspended()->create();

        $picked = $hospital->referralStaff()->pluck('users.id');

        $this->assertTrue($picked->contains($active->getKey()));
        $this->assertFalse($picked->contains($suspended->getKey()));
    }
}
