<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Backup;
use App\Models\Hospital;
use App\Models\Referral;
use App\Models\User;
use App\Services\SettingsService;
use App\Support\AuditActions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesReferralStaff;
use Tests\TestCase;

/**
 * Locks in the platform's access-control boundaries: closed registration,
 * throttled auth endpoints, tenant isolation that fails closed, and an audit
 * trail for every bulk data export.
 */
class PlatformSecurityControlsTest extends TestCase
{
    use CreatesReferralStaff;
    use RefreshDatabase;

    private function enableRegistration(): void
    {
        app(SettingsService::class)->set('auth.registration_enabled', true, 'boolean');
    }

    private function registrationPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'New Clinician',
            'email' => 'new.clinician@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ], $overrides);
    }

    public function test_public_registration_is_disabled_until_an_admin_enables_it(): void
    {
        $this->role('healthcare_worker');

        $this->postJson('/api/register', $this->registrationPayload())->assertUnprocessable();

        $this->assertFalse(
            (bool) app(SettingsService::class)->get('auth.registration_enabled', false),
            'Platforms must not mint public accounts unless an admin opts in.'
        );

        $this->enableRegistration();

        $this->postJson('/api/register', $this->registrationPayload())->assertCreated();
    }

    public function test_api_login_is_throttled_after_repeated_failures(): void
    {
        $user = User::factory()->create(['email' => 'brute.target@example.com']);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])->assertUnprocessable();
        }

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertStatus(429);
    }

    public function test_web_login_is_throttled_after_repeated_failures(): void
    {
        $user = User::factory()->create(['email' => 'web.brute@example.com']);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertStatus(429);
    }

    public function test_referral_endpoints_are_throttled_for_authenticated_clients(): void
    {
        $worker = $this->staff(Hospital::factory()->create(), 'healthcare_worker');
        Sanctum::actingAs($worker);

        $limited = false;

        for ($request = 0; $request < 130; $request++) {
            $response = $this->getJson('/api/referrals');

            if ($response->status() === 429) {
                $limited = true;
                break;
            }
        }

        $this->assertTrue($limited, 'Authenticated API traffic must be rate limited.');
    }

    public function test_backup_creation_is_throttled(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();
        Sanctum::actingAs($admin);

        $limited = false;

        for ($attempt = 0; $attempt < 8; $attempt++) {
            $response = $this->postJson('/api/admin/backups');

            if ($response->status() === 429) {
                $limited = true;
                break;
            }
        }

        $this->assertTrue($limited, 'Repeated backup creation must be rate limited.');
    }

    public function test_a_user_without_a_hospital_cannot_reach_referral_tenant_scopes(): void
    {
        $this->role('healthcare_worker');
        $orphan = User::factory()->withRole('healthcare_worker')->create();
        $orphan->update(['hospital_id' => null]);

        $from = Hospital::factory()->create();
        $to = Hospital::factory()->create();
        Referral::factory()->create([
            'referring_hospital_id' => $from->getKey(),
            'receiving_hospital_id' => $to->getKey(),
        ]);

        Sanctum::actingAs($orphan->fresh());

        $this->getJson('/api/referrals')->assertForbidden();

        $this->assertSame(
            0,
            Referral::visibleTo($orphan)->count(),
            'A user with no hospital must resolve to no referrals, not to all of them.'
        );
    }

    public function test_a_system_admin_without_a_hospital_still_has_full_referral_visibility(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();
        $admin->update(['hospital_id' => null]);

        $from = Hospital::factory()->create();
        $to = Hospital::factory()->create();
        Referral::factory()->create([
            'referring_hospital_id' => $from->getKey(),
            'receiving_hospital_id' => $to->getKey(),
        ]);

        Sanctum::actingAs($admin->fresh());

        $this->getJson('/api/referrals')->assertOk();
        $this->assertSame(1, Referral::visibleTo($admin->fresh())->count());
    }

    public function test_report_export_is_written_to_the_audit_log(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();

        $this->actingAs($admin)
            ->get('/admin/reports/export/referrals')
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->getKey(),
            'action' => AuditActions::REPORT_EXPORTED,
        ]);

        $log = AuditLog::query()
            ->where('action', AuditActions::REPORT_EXPORTED)
            ->latest('id')
            ->first();

        $this->assertSame('referrals', $log->metadata['report'] ?? null);
    }

    public function test_backup_download_is_written_to_the_audit_log(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();

        $backup = Backup::create([
            'filename' => 'audit-check.sql',
            'disk' => 'local',
            'size' => 10,
            'status' => 'completed',
            'created_by' => $admin->getKey(),
        ]);

        $this->actingAs($admin)
            ->get("/admin/backups/{$backup->getKey()}/download");

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->getKey(),
            'action' => AuditActions::BACKUP_DOWNLOADED,
        ]);
    }

    public function test_a_hospital_admin_cannot_update_a_peer_administrator(): void
    {
        $hospital = Hospital::factory()->create();
        $actor = $this->staff($hospital, 'hospital_admin');
        $peer = $this->staff($hospital, 'hospital_admin');

        Sanctum::actingAs($actor);

        $this->patchJson("/api/admin/users/{$peer->getKey()}", [
            'name' => 'Hijacked',
        ])->assertForbidden();

        $this->assertNotSame('Hijacked', $peer->refresh()->name);
    }

    public function test_a_hospital_admin_cannot_delete_a_worker_from_another_hospital(): void
    {
        $own = Hospital::factory()->create();
        $other = Hospital::factory()->create();
        $actor = $this->staff($own, 'hospital_admin');
        $outsider = $this->staff($other, 'healthcare_worker');

        Sanctum::actingAs($actor);

        $this->deleteJson("/api/admin/users/{$outsider->getKey()}")
            ->assertForbidden();

        $this->assertModelExists($outsider);
    }

    public function test_a_hospital_admin_cannot_create_an_administrator_account(): void
    {
        $hospital = Hospital::factory()->create();
        $actor = $this->staff($hospital, 'hospital_admin');
        $this->role('system_admin');

        Sanctum::actingAs($actor);

        $this->postJson('/api/admin/users', [
            'name' => 'Escalation Attempt',
            'email' => 'escalation@example.com',
            'password' => 'password',
            'role_slug' => 'system_admin',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('role_slug');

        $this->assertDatabaseMissing('users', ['email' => 'escalation@example.com']);
    }

    public function test_a_system_admin_cannot_create_a_non_adminministrator_account(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();
        $this->role('healthcare_worker');

        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/users', [
            'name' => 'Should Not Exist',
            'email' => 'worker.rejected@example.com',
            'password' => 'password',
            'role_slug' => 'healthcare_worker',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('role_slug');

        $this->assertDatabaseMissing('users', ['email' => 'worker.rejected@example.com']);
    }

    public function test_login_of_an_unknown_email_does_not_short_circuit_the_password_check(): void
    {
        $user = User::factory()->create(['email' => 'known@example.com']);
        $this->assertTrue(Hash::check('password', $user->password));

        $unknown = $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ])->assertUnprocessable();

        $known = $this->postJson('/api/login', [
            'email' => 'known@example.com',
            'password' => 'wrong-password',
        ])->assertUnprocessable();

        $this->assertSame(
            $unknown->json('errors.email.0'),
            $known->json('errors.email.0'),
            'Unknown accounts must not be distinguishable from wrong passwords by message.'
        );
    }

    public function test_user_policy_blocks_non_admin_actors_from_user_administration(): void
    {
        $this->role('healthcare_worker');
        $this->role('system_admin');
        $worker = $this->staff(Hospital::factory()->create(), 'healthcare_worker');
        $target = $this->staff(Hospital::factory()->create(), 'healthcare_worker');

        Sanctum::actingAs($worker);

        $this->getJson('/api/admin/users')->assertForbidden();
        $this->patchJson("/api/admin/users/{$target->getKey()}", ['name' => 'Nope'])->assertForbidden();
    }
}
