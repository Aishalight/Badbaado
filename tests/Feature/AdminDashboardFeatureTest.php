<?php

namespace Tests\Feature;

use App\Models\Hospital;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesReferralStaff;
use Tests\TestCase;

class AdminDashboardFeatureTest extends TestCase
{
    use CreatesReferralStaff;
    use RefreshDatabase;

    private function referralBetween(Hospital $from, Hospital $to): Referral
    {
        return Referral::factory()->create([
            'referring_hospital_id' => $from->getKey(),
            'receiving_hospital_id' => $to->getKey(),
            'urgency' => 'urgent',
        ]);
    }

    public function test_hospital_admin_analytics_are_scoped_to_own_hospital(): void
    {
        $a = Hospital::factory()->create();
        $b = Hospital::factory()->create();
        $c = Hospital::factory()->create();

        $this->referralBetween($a, $b);
        $this->referralBetween($a, $b);
        $this->referralBetween($c, $b);

        Sanctum::actingAs($this->staff($a, 'hospital_admin'));

        $this->getJson('/api/analytics/overview')
            ->assertOk()
            ->assertJsonPath('data.scope', 'hospital')
            ->assertJsonPath('data.total_referrals', 2);
    }

    public function test_system_admin_analytics_span_the_whole_platform(): void
    {
        $this->role('system_admin');
        $a = Hospital::factory()->create();
        $b = Hospital::factory()->create();
        $c = Hospital::factory()->create();

        $this->referralBetween($a, $b);
        $this->referralBetween($a, $b);
        $this->referralBetween($c, $b);

        Sanctum::actingAs(User::factory()->withRole('system_admin')->create());

        $this->getJson('/api/analytics/overview')
            ->assertOk()
            ->assertJsonPath('data.scope', 'system')
            ->assertJsonPath('data.total_referrals', 3)
            ->assertJsonPath('data.total_hospitals', 3);
    }

    public function test_healthcare_worker_cannot_access_analytics(): void
    {
        $hospital = Hospital::factory()->create();

        Sanctum::actingAs($this->staff($hospital, 'healthcare_worker'));

        $this->getJson('/api/analytics/overview')->assertForbidden();
    }

    public function test_hospital_admin_creates_user_within_own_hospital_and_logs_audit(): void
    {
        $hospital = Hospital::factory()->create();
        $admin = $this->staff($hospital, 'hospital_admin');

        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/users', [
            'name' => 'New Nurse',
            'email' => 'nurse@alhilal.bd',
            'password' => 'password123',
            'title' => 'Senior Nurse',
            'role_slug' => 'referral_coordinator',
        ])->assertCreated()
            ->assertJsonPath('data.hospital_id', $hospital->getKey());

        $this->assertDatabaseHas('users', [
            'email' => 'nurse@alhilal.bd',
            'hospital_id' => $hospital->getKey(),
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user_created',
            'user_id' => $admin->getKey(),
        ]);
    }

    public function test_hospital_admin_cannot_create_system_admin(): void
    {
        $hospital = Hospital::factory()->create();

        Sanctum::actingAs($this->staff($hospital, 'hospital_admin'));

        $this->postJson('/api/admin/users', [
            'name' => 'Intruder',
            'email' => 'intruder@example.com',
            'password' => 'password123',
            'role_slug' => 'system_admin',
        ])->assertUnprocessable();
    }

    public function test_hospital_admin_cannot_manage_another_hospitals_user(): void
    {
        $own = Hospital::factory()->create();
        $other = Hospital::factory()->create();
        $admin = $this->staff($own, 'hospital_admin');
        $foreignUser = $this->staff($other, 'healthcare_worker');

        Sanctum::actingAs($admin);

        $this->patchJson("/api/admin/users/{$foreignUser->getKey()}", [
            'role_slug' => 'referral_coordinator',
        ])->assertForbidden();
    }

    public function test_hospital_admin_list_sees_only_own_hospital_users(): void
    {
        $own = Hospital::factory()->create();
        $other = Hospital::factory()->create();
        $admin = $this->staff($own, 'hospital_admin');
        $this->staff($own, 'healthcare_worker');
        $this->staff($other, 'healthcare_worker');

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/users')->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_system_admin_can_create_hospital_but_hospital_admin_cannot(): void
    {
        $this->role('system_admin');
        Sanctum::actingAs(User::factory()->withRole('system_admin')->create());

        $this->postJson('/api/admin/hospitals', [
            'name' => 'Central Hospital',
            'short_name' => 'CH',
            'code' => 'CH01',
            'location' => 'Mogadishu',
            'level' => 'Tertiary',
        ])->assertCreated()
            ->assertJsonPath('data.code', 'CH01');

        $this->assertDatabaseHas('audit_logs', ['action' => 'hospital_created']);

        Sanctum::actingAs($this->staff(Hospital::factory()->create(), 'hospital_admin'));

        $this->postJson('/api/admin/hospitals', [
            'name' => 'Sneaky',
            'short_name' => 'S',
            'code' => 'S01',
        ])->assertForbidden();
    }

    public function test_system_admin_sees_activity_log_and_hospital_admin_does_not(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();

        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/activity')->assertOk()->assertJsonStructure(['data' => []]);

        Sanctum::actingAs($this->staff(Hospital::factory()->create(), 'hospital_admin'));

        $this->getJson('/api/admin/activity')->assertForbidden();
    }

    public function test_system_admin_can_change_role_of_any_user(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();
        $worker = $this->staff(Hospital::factory()->create(), 'healthcare_worker');

        Sanctum::actingAs($admin);

        $this->patchJson("/api/admin/users/{$worker->getKey()}", [
            'role_slug' => 'hospital_admin',
        ])->assertOk()
            ->assertJsonPath('data.role.slug', 'hospital_admin');
    }
}
