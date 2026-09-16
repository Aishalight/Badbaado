<?php

namespace Tests\Feature;

use App\Models\Hospital;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesReferralStaff;
use Tests\TestCase;

class HospitalIsolationFeatureTest extends TestCase
{
    use CreatesReferralStaff;
    use RefreshDatabase;

    private function referralFor(Hospital $referring, Hospital $receiving): Referral
    {
        return Referral::factory()->create([
            'referring_hospital_id' => $referring->getKey(),
            'receiving_hospital_id' => $receiving->getKey(),
        ]);
    }

    public function test_unrelated_hospital_worker_cannot_view_referral(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $unrelated = Hospital::factory()->create();
        $referral = $this->referralFor($referring, $receiving);

        Sanctum::actingAs($this->staff($unrelated, 'healthcare_worker'));

        $this->getJson("/api/referrals/{$referral->getKey()}")->assertForbidden();
    }

    public function test_referring_hospital_worker_can_view_referral(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $referral = $this->referralFor($referring, $receiving);

        Sanctum::actingAs($this->staff($referring, 'healthcare_worker'));

        $this->getJson("/api/referrals/{$referral->getKey()}")
            ->assertOk()
            ->assertJsonPath('data.id', $referral->getKey());
    }

    public function test_system_admin_can_view_any_referral(): void
    {
        $this->role('system_admin');
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $referral = $this->referralFor($referring, $receiving);
        $admin = User::factory()->withRole('system_admin')->create();

        Sanctum::actingAs($admin);

        $this->getJson("/api/referrals/{$referral->getKey()}")
            ->assertOk()
            ->assertJsonPath('data.id', $referral->getKey());
    }

    public function test_unrelated_hospital_worker_cannot_message_referral(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $unrelated = Hospital::factory()->create();
        $referral = $this->referralFor($referring, $receiving);

        Sanctum::actingAs($this->staff($unrelated, 'healthcare_worker'));

        $this->postJson("/api/referrals/{$referral->getKey()}/messages", [
            'body' => 'Is there bed capacity?',
        ])->assertForbidden();
    }

    public function test_involved_staff_can_message_and_message_is_persisted(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $referral = $this->referralFor($referring, $receiving);
        $sender = $this->staff($referring, 'healthcare_worker');

        Sanctum::actingAs($sender);

        $this->postJson("/api/referrals/{$referral->getKey()}/messages", [
            'body' => 'Patient en route, ETA 20 minutes.',
        ])->assertCreated()
            ->assertJsonPath('data.body', 'Patient en route, ETA 20 minutes.');

        $this->assertDatabaseHas('messages', [
            'referral_id' => $referral->getKey(),
            'sender_user_id' => $sender->getKey(),
            'body' => 'Patient en route, ETA 20 minutes.',
        ]);
    }
}
