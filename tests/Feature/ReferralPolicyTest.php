<?php

namespace Tests\Feature;

use App\Enums\ReferralStatus;
use App\Models\Hospital;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\Concerns\CreatesReferralStaff;
use Tests\TestCase;

class ReferralPolicyTest extends TestCase
{
    use CreatesReferralStaff;
    use RefreshDatabase;

    private function referralBetween(Hospital $referring, Hospital $receiving, ReferralStatus $status = ReferralStatus::DRAFT): Referral
    {
        return Referral::factory()->create([
            'referring_hospital_id' => $referring->getKey(),
            'receiving_hospital_id' => $receiving->getKey(),
            'status' => $status,
        ]);
    }

    public function test_view_allows_only_involved_hospitals_and_system_admin(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $unrelated = Hospital::factory()->create();
        $referral = $this->referralBetween($referring, $receiving);

        $this->assertFalse(Gate::forUser($this->staff($unrelated, 'healthcare_worker'))->allows('view', $referral));
        $this->assertTrue(Gate::forUser($this->staff($referring, 'referral_coordinator'))->allows('view', $referral));
        $this->assertTrue(Gate::forUser($this->staff($receiving, 'hospital_admin'))->allows('view', $referral));

        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();
        $this->assertTrue(Gate::forUser($admin)->allows('view', $referral));
    }

    public function test_create_requires_a_hospital_assignment_and_referral_staff_role(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();

        $this->assertFalse(Gate::forUser($admin)->allows('create', Referral::class));

        $hospital = Hospital::factory()->create();
        $this->assertTrue(Gate::forUser($this->staff($hospital, 'healthcare_worker'))->allows('create', Referral::class));
        $this->assertFalse(Gate::forUser($this->staff($hospital, 'hospital_admin'))->allows('create', Referral::class));
    }

    public function test_send_and_cancel_require_the_referring_hospital(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $referral = $this->referralBetween($referring, $receiving);

        $this->assertTrue(
            Gate::forUser($this->staff($referring, 'healthcare_worker'))->allows('transition', [$referral, ReferralStatus::SENT])
        );
        $this->assertFalse(
            Gate::forUser($this->staff($receiving, 'referral_coordinator'))->allows('transition', [$referral, ReferralStatus::SENT])
        );
    }

    public function test_sending_a_non_draft_referral_is_denied(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $referral = $this->referralBetween($referring, $receiving, ReferralStatus::SENT);

        $this->assertFalse(
            Gate::forUser($this->staff($referring, 'healthcare_worker'))->allows('transition', [$referral, ReferralStatus::SENT])
        );
    }

    public function test_acknowledge_and_review_are_receiving_hospital_only(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $sent = $this->referralBetween($referring, $receiving, ReferralStatus::SENT);

        $this->assertTrue(
            Gate::forUser($this->staff($receiving, 'referral_coordinator'))->allows('transition', [$sent, ReferralStatus::RECEIVED])
        );
        $this->assertFalse(
            Gate::forUser($this->staff($referring, 'healthcare_worker'))->allows('transition', [$sent, ReferralStatus::RECEIVED])
        );

        $received = $this->referralBetween($referring, $receiving, ReferralStatus::RECEIVED);
        $this->assertTrue(
            Gate::forUser($this->staff($receiving, 'hospital_admin'))->allows('transition', [$received, ReferralStatus::UNDER_REVIEW])
        );
        $this->assertFalse(
            Gate::forUser($this->staff($referring, 'referral_coordinator'))->allows('transition', [$received, ReferralStatus::UNDER_REVIEW])
        );
    }

    public function test_accept_and_reject_are_receiving_hospital_only(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $underReview = $this->referralBetween($referring, $receiving, ReferralStatus::UNDER_REVIEW);

        $this->assertTrue(
            Gate::forUser($this->staff($receiving, 'referral_coordinator'))->allows('transition', [$underReview, ReferralStatus::ACCEPTED])
        );
        $this->assertTrue(
            Gate::forUser($this->staff($receiving, 'healthcare_worker'))->allows('transition', [$underReview, ReferralStatus::REJECTED])
        );
        $this->assertFalse(
            Gate::forUser($this->staff($referring, 'healthcare_worker'))->allows('transition', [$underReview, ReferralStatus::ACCEPTED])
        );
    }

    public function test_transfer_state_changes_are_open_to_both_hospitals(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $accepted = $this->referralBetween($referring, $receiving, ReferralStatus::ACCEPTED);

        $this->assertTrue(
            Gate::forUser($this->staff($receiving, 'referral_coordinator'))->allows('transition', [$accepted, ReferralStatus::TRANSFER_IN_PROGRESS])
        );
        $this->assertTrue(
            Gate::forUser($this->staff($referring, 'hospital_admin'))->allows('transition', [$accepted, ReferralStatus::TRANSFER_IN_PROGRESS])
        );
    }

    public function test_system_admin_can_transition_any_referral(): void
    {
        $this->role('system_admin');
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $referral = $this->referralBetween($referring, $receiving);
        $admin = User::factory()->withRole('system_admin')->create();

        $this->assertTrue(
            Gate::forUser($admin)->allows('transition', [$referral, ReferralStatus::SENT])
        );
    }
}
