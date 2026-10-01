<?php

namespace Tests\Feature;

use App\Enums\ReferralStatus;
use App\Enums\Urgency;
use App\Models\Hospital;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\Referral;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\ReferralMonitoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesReferralStaff;
use Tests\TestCase;

/**
 * Regression coverage for metrics and pages that silently read the wrong data
 * or crashed on legitimately nullable columns.
 */
class ReferralMetricsCorrectnessTest extends TestCase
{
    use CreatesReferralStaff;
    use RefreshDatabase;

    private function systemAdmin(): User
    {
        $this->role('system_admin');

        return User::factory()->withRole('system_admin')->create();
    }

    private function referral(Hospital $from, Hospital $to, array $attributes = []): Referral
    {
        return Referral::factory()->create(array_merge([
            'referring_hospital_id' => $from->getKey(),
            'receiving_hospital_id' => $to->getKey(),
            'patient_id' => Patient::factory()->create()->getKey(),
            'urgency' => Urgency::ROUTINE->value,
            'status' => ReferralStatus::SENT->value,
        ], $attributes));
    }

    public function test_dashboard_counts_referrals_that_are_still_in_motion(): void
    {
        $from = Hospital::factory()->create();
        $to = Hospital::factory()->create();
        $worker = $this->staff($from, 'healthcare_worker');

        $this->referral($from, $to);
        $this->referral($from, $to, ['status' => ReferralStatus::COMPLETED->value]);

        $this->actingAs($worker)->get('/dashboard')->assertOk()
            ->assertSee(
                '<p class="dashboard-stat__value mt-5">1</p><p class="dashboard-stat__label">Active transfers</p>',
                escape: false
            );
    }

    public function test_dashboard_counts_high_acuity_referrals(): void
    {
        $from = Hospital::factory()->create();
        $to = Hospital::factory()->create();
        $worker = $this->staff($from, 'healthcare_worker');

        $this->referral($from, $to, ['urgency' => Urgency::CRITICAL->value]);

        $this->actingAs($worker)->get('/dashboard')->assertOk()
            ->assertSee(
                '<p class="dashboard-stat__value mt-5">1</p><p class="dashboard-stat__label">Critical / high</p>',
                escape: false
            );
    }

    public function test_dashboard_counts_incoming_referrals_for_a_coordinator(): void
    {
        $from = Hospital::factory()->create();
        $to = Hospital::factory()->create();
        $coordinator = $this->staff($to, 'referral_coordinator');

        $this->referral($from, $to);

        $this->actingAs($coordinator)->get('/dashboard')->assertOk()
            ->assertSee(
                '<p class="dashboard-stat__value mt-5">1</p><p class="dashboard-stat__label">Incoming to review</p>',
                escape: false
            );
    }

    public function test_analytics_reports_active_transfers(): void
    {
        $from = Hospital::factory()->create();
        $to = Hospital::factory()->create();
        $admin = $this->systemAdmin();

        $this->referral($from, $to);
        $this->referral($from, $to, ['status' => ReferralStatus::COMPLETED->value]);

        $overview = app(AnalyticsService::class)->overview($admin);

        $this->assertSame(2, $overview['total_referrals']);
        $this->assertSame(1, $overview['active_transfers'],
            'Only referrals still in motion should count as active transfers.');
    }

    public function test_urgency_mix_is_ordered_by_count(): void
    {
        $from = Hospital::factory()->create();
        $to = Hospital::factory()->create();

        $this->referral($from, $to, ['urgency' => Urgency::ROUTINE->value]);
        $this->referral($from, $to, ['urgency' => Urgency::ROUTINE->value]);
        $this->referral($from, $to, ['urgency' => Urgency::CRITICAL->value]);

        $urgency = app(ReferralMonitoringService::class)->overview()['urgency'];

        $this->assertSame(
            [Urgency::ROUTINE->value, Urgency::CRITICAL->value],
            $urgency->keys()->all(),
            'The urgency mix must be sorted by descending count.'
        );
    }

    public function test_notification_inbox_shows_the_stored_title_and_body(): void
    {
        $hospital = Hospital::factory()->create();
        $worker = $this->staff($hospital, 'healthcare_worker');

        Notification::create([
            'user_id' => $worker->getKey(),
            'type' => 'referral_update',
            'title' => 'Referral accepted by receiving team',
            'body' => 'Riverside General accepted the handover.',
        ]);

        $this->actingAs($worker)->get('/notifications')->assertOk()
            ->assertSee('Referral accepted by receiving team')
            ->assertSee('Riverside General accepted the handover.');
    }

    public function test_analytics_page_renders_a_referral_without_urgency(): void
    {
        $from = Hospital::factory()->create();
        $to = Hospital::factory()->create();
        $admin = $this->systemAdmin();

        $this->referral($from, $to, ['urgency' => null]);

        $this->actingAs($admin)->get('/admin/analytics')->assertOk();
    }

    public function test_console_renders_for_a_user_without_a_role(): void
    {
        $hospital = Hospital::factory()->create();
        $roleless = User::factory()->atHospital($hospital->getKey())->create();

        $this->assertNull($roleless->role_id);

        $this->actingAs($roleless)->get('/dashboard')->assertOk();
    }

    public function test_hospital_admin_cannot_move_a_user_into_another_hospital(): void
    {
        $own = Hospital::factory()->create();
        $other = Hospital::factory()->create();
        $admin = $this->staff($own, 'hospital_admin');
        $worker = $this->staff($own, 'healthcare_worker');

        Sanctum::actingAs($admin);

        $this->patchJson("/api/admin/users/{$worker->getKey()}", [
            'hospital_id' => $other->getKey(),
        ])->assertUnprocessable()->assertJsonValidationErrors('hospital_id');

        $this->assertSame($own->getKey(), $worker->refresh()->hospital_id);
    }

    public function test_system_admin_can_still_move_a_user_into_another_hospital(): void
    {
        $from = Hospital::factory()->create();
        $to = Hospital::factory()->create();
        $admin = $this->systemAdmin();
        $worker = $this->staff($from, 'hospital_admin');

        Sanctum::actingAs($admin);

        $this->patchJson("/api/admin/users/{$worker->getKey()}", [
            'hospital_id' => $to->getKey(),
        ])->assertOk();

        $this->assertSame($to->getKey(), $worker->refresh()->hospital_id);
    }
}
