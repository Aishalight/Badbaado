<?php

namespace Tests\Feature;

use App\Enums\ReferralStatus;
use App\Enums\Urgency;
use App\Models\AuditLog;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\Referral;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\PatientReferenceGenerator;
use App\Services\ReferralWorkflowService;
use App\Services\ReportService;
use App\Services\SecurityService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\Concerns\CreatesReferralStaff;
use Tests\TestCase;

/**
 * Guards the data-integrity and scale fixes: identifiers must not collide,
 * failed writes must not leave files behind, aggregates must not require loading
 * whole tables, and the audit trail must be searchable.
 */
class DataIntegrityAndScaleTest extends TestCase
{
    use CreatesReferralStaff;
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function referralPayload(Hospital $receiving, array $overrides = []): array
    {
        return array_merge([
            'receiving_hospital_id' => $receiving->getKey(),
            'department' => 'Emergency',
            'referral_reason' => 'Chest pain',
            'patient' => ['name' => 'Test Patient'],
            'urgency' => Urgency::URGENT->value,
        ], $overrides);
    }

    public function test_patient_references_are_unique_across_rapid_creations(): void
    {
        $worker = $this->staff(Hospital::factory()->create(), 'healthcare_worker');
        $receiving = Hospital::factory()->create();

        Sanctum::actingAs($worker);

        for ($index = 0; $index < 12; $index++) {
            $this->postJson('/api/referrals', $this->referralPayload($receiving, [
                'patient' => ['name' => "Patient {$index}"],
            ]))->assertCreated();
        }

        $references = Patient::query()->pluck('reference');

        $this->assertCount(12, $references);
        $this->assertCount(12, $references->unique(), 'Every patient needs a distinct reference.');
        $this->assertTrue($references->every(fn (string $reference) => str_starts_with($reference, 'PT-')));
    }

    public function test_patient_reference_generator_skips_a_taken_reference(): void
    {
        $taken = Patient::factory()->create(['reference' => 'PT-AAAAAA'])->reference;
        $this->assertSame('PT-AAAAAA', $taken);

        $generator = app(PatientReferenceGenerator::class);

        $this->assertNotSame('PT-AAAAAA', $generator->generate());
    }

    public function test_rolled_back_referral_does_not_leave_attachment_files_on_disk(): void
    {
        Storage::fake('local');

        $worker = $this->staff(Hospital::factory()->create(), 'healthcare_worker');
        $receiving = Hospital::factory()->create();

        // The audit write happens after attachments land on disk, so failing it
        // rolls the referral back only once files already exist.
        $this->app->bind(AuditLogger::class, fn () => new class extends AuditLogger
        {
            public function record(?User $user, string $action, ?Model $entity = null, array $metadata = []): AuditLog
            {
                throw new RuntimeException('audit sink unavailable');
            }
        });

        try {
            app(ReferralWorkflowService::class)->createReferral($worker, [
                'receiving_hospital_id' => $receiving->getKey(),
                'department' => 'Emergency',
                'referral_reason' => 'Chest pain',
                'patient' => ['name' => 'Test Patient'],
                'attachments' => [UploadedFile::fake()->create('scan.pdf', 8)],
            ]);

            $this->fail('The referral should not have been created.');
        } catch (RuntimeException) {
            // Expected: the injected audit failure aborts the transaction.
        }

        $this->assertSame(0, Referral::count(), 'A failed referral must not leave a record.');
        $this->assertSame(0, Patient::count(), 'A failed referral must not leave a patient record.');
        $this->assertSame(
            0,
            count(Storage::disk('local')->files('referrals')),
            'A rolled back referral must not leave clinical files on disk.'
        );
    }

    public function test_referrals_cannot_be_sent_to_a_deactivated_hospital(): void
    {
        $worker = $this->staff(Hospital::factory()->create(), 'healthcare_worker');
        $inactive = Hospital::factory()->create(['is_active' => false]);

        Sanctum::actingAs($worker);

        $this->postJson('/api/referrals', $this->referralPayload($inactive))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('receiving_hospital_id');

        $this->assertSame(0, Referral::count());
    }

    public function test_referral_urgency_must_be_a_known_value(): void
    {
        $worker = $this->staff(Hospital::factory()->create(), 'healthcare_worker');
        $receiving = Hospital::factory()->create();

        Sanctum::actingAs($worker);

        $this->postJson('/api/referrals', $this->referralPayload($receiving, ['urgency' => 'panic']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('urgency');
    }

    public function test_audit_trail_search_matches_metadata_across_drivers(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();

        app(AuditLogger::class)->record($admin, 'referral_created', null, [
            'referral_number' => 'REF-2026-UNIQUE1',
        ]);

        $this->actingAs($admin)
            ->get('/admin/audit?q=UNIQUE1')
            ->assertOk()
            ->assertSee('referral_created');
    }

    public function test_security_daily_series_groups_by_calendar_day(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();

        app(AuditLogger::class)->record($admin, 'auth.login', $admin, [
            'email' => $admin->email,
        ]);

        $series = app(SecurityService::class)->signinsByDay(7);

        $this->assertCount(7, $series);
        $this->assertSame(now()->format('Y-m-d'), $series[6]['date']);
        $this->assertGreaterThanOrEqual(1, (int) $series[6]['count']);
    }

    public function test_report_exports_stream_every_row(): void
    {
        $this->role('system_admin');
        User::factory()->withRole('system_admin')->create();

        User::factory()->count(5)->create();

        $response = app(ReportService::class)->download('users');

        ob_start();
        $response->sendContent();
        $csv = (string) ob_get_clean();

        $lines = array_values(array_filter(
            explode("\n", str_replace("\r", '', $csv)),
            fn (string $line) => trim($line) !== ''
        ));

        $this->assertStringContainsString('Name,Email', $lines[0]);
        $this->assertCount(7, $lines, 'Every user must be exported: one header plus six accounts.');
    }

    public function test_dashboard_counts_reflect_all_referrals_not_just_the_queue(): void
    {
        $from = Hospital::factory()->create();
        $worker = $this->staff($from, 'healthcare_worker');
        $receiving = Hospital::factory()->create();

        for ($index = 0; $index < 30; $index++) {
            Referral::factory()->create([
                'referring_hospital_id' => $from->getKey(),
                'receiving_hospital_id' => $receiving->getKey(),
                'status' => ReferralStatus::SENT->value,
                'urgency' => Urgency::CRITICAL->value,
                'patient_id' => Patient::factory()->create()->getKey(),
            ]);
        }

        $this->actingAs($worker)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee(
                '<p class="dashboard-stat__value mt-5">30</p><p class="dashboard-stat__label">Active transfers</p>',
                escape: false
            );
    }

    public function test_operational_queries_have_supporting_indexes(): void
    {
        $expected = [
            'users_hospital_role_index',
            'referrals_created_at_index',
            'referrals_receiving_status_index',
            'referrals_referring_status_index',
            'audit_logs_action_index',
            'audit_logs_created_at_index',
            'audit_logs_action_created_index',
            // Backs the unread badge count the console layout runs per page load.
            'idx_notifications_user_read',
            // Backs the unread-alarm query that filters by severity.
            'notifications_user_severity_read_index',
        ];

        foreach ($expected as $index) {
            $this->assertTrue(
                $this->indexExists($index),
                "Expected index {$index} to exist for operational queries."
            );
        }
    }

    public function test_notifications_table_has_no_duplicate_user_read_indexes(): void
    {
        $indexes = collect(Schema::getIndexes('notifications'))
            ->filter(fn (array $index) => $index['columns'] === ['user_id', 'read_at']);

        $this->assertCount(
            1,
            $indexes,
            'The notifications table must carry exactly one (user_id, read_at) index; duplicates slow every write.'
        );
    }

    private function indexExists(string $name): bool
    {
        $table = match (true) {
            str_starts_with($name, 'users_') => 'users',
            str_starts_with($name, 'referrals_') => 'referrals',
            str_starts_with($name, 'audit_logs_') => 'audit_logs',
            default => 'notifications',
        };

        return Schema::hasIndex($table, $name);
    }
}
