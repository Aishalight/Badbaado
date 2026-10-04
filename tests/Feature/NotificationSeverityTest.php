<?php

namespace Tests\Feature;

use App\Enums\NotificationSeverity;
use App\Enums\ReferralStatus;
use App\Models\Hospital;
use App\Models\Notification;
use App\Models\Referral;
use App\Models\User;
use App\Services\ReferralNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesReferralStaff;
use Tests\TestCase;

class NotificationSeverityTest extends TestCase
{
    use CreatesReferralStaff;
    use RefreshDatabase;

    public function test_a_rejection_raises_a_critical_alarm(): void
    {
        $referral = $this->referralInMotion(ReferralStatus::REJECTED);

        app(ReferralNotifier::class)->notifyOnTransition($referral);

        $this->assertDatabaseHas('notifications', [
            'referral_id' => $referral->getKey(),
            'type' => 'referral_rejected',
            'severity' => NotificationSeverity::CRITICAL->value,
        ]);
    }

    /**
     * @return array<string, array{0: ReferralStatus, 1: string, 2: NotificationSeverity}>
     */
    public static function transitionSeverities(): array
    {
        return [
            'sent is a warning because the patient is waiting on acknowledgement' => [
                ReferralStatus::SENT, 'referral_sent', NotificationSeverity::WARNING,
            ],
            'received is informational' => [
                ReferralStatus::RECEIVED, 'referral_received', NotificationSeverity::INFO,
            ],
            'under review is informational' => [
                ReferralStatus::UNDER_REVIEW, 'referral_under_review', NotificationSeverity::INFO,
            ],
            'accepted is informational' => [
                ReferralStatus::ACCEPTED, 'referral_accepted', NotificationSeverity::INFO,
            ],
            'rejected is critical' => [
                ReferralStatus::REJECTED, 'referral_rejected', NotificationSeverity::CRITICAL,
            ],
            'transfer in progress is a warning because a patient is mid-journey' => [
                ReferralStatus::TRANSFER_IN_PROGRESS, 'transfer_in_progress', NotificationSeverity::WARNING,
            ],
            'arrived is informational' => [
                ReferralStatus::ARRIVED, 'patient_arrived', NotificationSeverity::INFO,
            ],
            'completed is informational' => [
                ReferralStatus::COMPLETED, 'referral_completed', NotificationSeverity::INFO,
            ],
            'cancelled is a warning' => [
                ReferralStatus::CANCELLED, 'referral_cancelled', NotificationSeverity::WARNING,
            ],
        ];
    }

    /**
     * @param  array{0: ReferralStatus, 1: string, 2: NotificationSeverity}  $case
     */
    #[DataProvider('transitionSeverities')]
    public function test_each_transition_carries_its_declared_severity(ReferralStatus $status, string $type, NotificationSeverity $expected): void
    {
        $referral = $this->referralInMotion($status);

        app(ReferralNotifier::class)->notifyOnTransition($referral);

        $this->assertDatabaseHas('notifications', [
            'referral_id' => $referral->getKey(),
            'type' => $type,
            'severity' => $expected->value,
        ]);
    }

    public function test_every_notifier_type_is_covered_by_the_severity_map(): void
    {
        $mapped = array_keys(NotificationSeverity::legacyTypeMap());
        $emitted = [
            'referral_sent', 'referral_received', 'referral_under_review', 'referral_accepted',
            'referral_rejected', 'transfer_in_progress', 'patient_arrived', 'referral_completed',
            'referral_cancelled',
        ];

        foreach ($emitted as $type) {
            $this->assertContains($type, $mapped, "Notification type {$type} has no severity mapping.");
        }
    }

    public function test_platform_broadcasts_are_announcements_not_alarms(): void
    {
        $admin = $this->staff(Hospital::factory()->create(), 'system_admin');
        $recipient = $this->staff(Hospital::factory()->create(), 'healthcare_worker');

        Sanctum::actingAs($admin, ['web']);

        $this->postJson('/api/admin/alerts', [
            'audience' => 'healthcare_worker',
            'title' => 'Network maintenance window',
            'body' => 'The console will be unavailable between 02:00 and 03:00.',
        ])->assertCreated();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $recipient->getKey(),
            'type' => 'platform_alert',
            'severity' => NotificationSeverity::ANNOUNCEMENT->value,
        ]);
    }

    public function test_interrupting_scope_returns_only_unread_critical_and_warning(): void
    {
        $user = User::factory()->create();

        $critical = Notification::factory()->for($user)->create(['severity' => NotificationSeverity::CRITICAL]);
        $warning = Notification::factory()->for($user)->create(['severity' => NotificationSeverity::WARNING]);
        $info = Notification::factory()->for($user)->create(['severity' => NotificationSeverity::INFO]);
        $readCritical = Notification::factory()->for($user)->create([
            'severity' => NotificationSeverity::CRITICAL,
            'read_at' => now(),
        ]);

        $ids = Notification::query()
            ->where('user_id', $user->getKey())
            ->unread()
            ->interrupting()
            ->pluck('id')
            ->all();

        sort($ids);
        $expected = [$critical->getKey(), $warning->getKey()];
        sort($expected);

        $this->assertSame($expected, $ids);
        $this->assertNotContains($info->getKey(), $ids);
        $this->assertNotContains($readCritical->getKey(), $ids);
    }

    public function test_notifications_page_shows_the_alarm_banner_only_for_interrupting_severities(): void
    {
        $user = User::factory()->create();

        Notification::factory()->for($user)->create([
            'severity' => NotificationSeverity::INFO,
            'title' => 'Referral received',
        ]);

        $this->actingAs($user)
            ->get('/notifications')
            ->assertOk()
            ->assertSee('Referral received')
            ->assertSee('Info')
            ->assertDontSee('unacknowledged');

        Notification::factory()->for($user)->create([
            'severity' => NotificationSeverity::CRITICAL,
            'title' => 'Referral rejected',
        ]);

        $this->actingAs($user)
            ->get('/notifications')
            ->assertOk()
            ->assertSee('unacknowledged')
            ->assertSee('Critical');
    }

    /**
     * The notifier fans out by role, so both hospitals need staff that the
     * transition is allowed to reach.
     */
    public function test_pending_endpoint_returns_only_unseen_unread_notifications_oldest_first(): void
    {
        $user = User::factory()->create();

        $first = Notification::factory()->for($user)->create([
            'severity' => NotificationSeverity::WARNING,
        ]);
        $second = Notification::factory()->for($user)->create([
            'severity' => NotificationSeverity::CRITICAL,
        ]);

        $response = $this->actingAs($user)->getJson('/api/notifications/pending?after=0');

        $response->assertOk()
            ->assertJsonPath('data.0.id', $first->getKey())
            ->assertJsonPath('data.1.id', $second->getKey())
            ->assertJsonPath('data.0.severity', 'warning')
            ->assertJsonPath('data.0.interrupting', true)
            ->assertJsonPath('data.1.severity', 'critical')
            ->assertJsonPath('data.1.interrupting', true);

        // Advancing the cursor drops what the browser has already seen.
        $this->actingAs($user)
            ->getJson('/api/notifications/pending?after='.$first->getKey())
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $second->getKey());
    }

    public function test_pending_endpoint_never_returns_a_read_notification(): void
    {
        $user = User::factory()->create();

        Notification::factory()->for($user)->create([
            'severity' => NotificationSeverity::CRITICAL,
            'read_at' => now(),
        ]);
        Notification::factory()->for($user)->create([
            'severity' => NotificationSeverity::INFO,
        ]);

        $this->actingAs($user)
            ->getJson('/api/notifications/pending')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.severity', 'info');
    }

    public function test_pending_endpoint_never_leaks_another_users_alarm(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Notification::factory()->for($other)->create([
            'severity' => NotificationSeverity::CRITICAL,
        ]);

        $this->actingAs($user)
            ->getJson('/api/notifications/pending')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_settings_page_exposes_the_alarm_toggle_and_starts_switched_off(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/settings')
            ->assertOk()
            ->assertSee('Alarm notifications')
            // Off by default: nothing is requested from the browser until asked.
            ->assertSee('Off by default', escape: false)
            ->assertSee('data-alarm-toggle', escape: false)
            ->assertSee('data-alarm-test', escape: false);
    }

    private function referralInMotion(ReferralStatus $status): Referral
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();

        $this->staff($referring, 'referral_coordinator');
        $this->staff($receiving, 'referral_coordinator');
        $this->staff($receiving, 'healthcare_worker');

        return Referral::factory()->create([
            'status' => $status,
            'referring_hospital_id' => $referring->getKey(),
            'receiving_hospital_id' => $receiving->getKey(),
        ]);
    }
}
