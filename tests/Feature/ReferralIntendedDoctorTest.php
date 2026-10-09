<?php

namespace Tests\Feature;

use App\Enums\ReferralStatus;
use App\Enums\UserStatus;
use App\Models\Hospital;
use App\Models\Referral;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesReferralStaff;
use Tests\TestCase;

class ReferralIntendedDoctorTest extends TestCase
{
    use CreatesReferralStaff;
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'receiving_hospital_id' => null,
            'department' => 'Cardiology',
            'referral_reason' => 'Suspected myocardial infarction.',
            'urgency' => 'urgent',
            'patient' => ['name' => 'Jane Doe', 'age' => 54],
        ], $overrides);
    }

    public function test_referral_can_target_one_independent_doctor_without_a_receiving_hospital(): void
    {
        $referring = Hospital::factory()->create();
        $practice = Hospital::factory()->practice()->create();
        $worker = $this->staff($referring, 'healthcare_worker');
        $doctor = $this->staff($practice, 'healthcare_worker');

        Sanctum::actingAs($worker);
        $this->postJson('/api/referrals', $this->payload([
            'intended_user_id' => $doctor->getKey(),
        ]))->assertCreated();

        $referral = Referral::firstOrFail();
        $this->assertNull($referral->receiving_hospital_id);
        $this->assertSame($doctor->getKey(), $referral->intended_user_id);
    }

    public function test_hospital_and_doctor_targets_are_mutually_exclusive(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $practice = Hospital::factory()->practice()->create();
        $worker = $this->staff($referring, 'healthcare_worker');
        $doctor = $this->staff($practice, 'healthcare_worker');

        Sanctum::actingAs($worker);
        $this->postJson('/api/referrals', $this->payload([
            'receiving_hospital_id' => $receiving->getKey(),
            'intended_user_id' => $doctor->getKey(),
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors('receiving_hospital_id');
    }

    public function test_intended_doctor_becomes_the_receiving_side_owner_when_sent(): void
    {
        $referring = Hospital::factory()->create();
        $practice = Hospital::factory()->practice()->create();
        $worker = $this->staff($referring, 'healthcare_worker');
        $doctor = $this->staff($practice, 'healthcare_worker');

        Sanctum::actingAs($worker);
        $this->postJson('/api/referrals', $this->payload([
            'intended_user_id' => $doctor->getKey(),
        ]))->assertCreated();

        $referral = Referral::firstOrFail();
        $this->postJson("/api/referrals/{$referral->getKey()}/transition", ['status' => 'sent'])->assertOk();

        $this->assertSame(ReferralStatus::SENT, $referral->refresh()->status);
        $this->assertSame($doctor->getKey(), $referral->assigned_to_user_id);
    }

    public function test_foreign_inactive_and_hospital_doctors_cannot_be_targeted(): void
    {
        $practice = Hospital::factory()->practice()->create();
        $otherPractice = Hospital::factory()->practice()->create();
        $hospital = Hospital::factory()->create();
        $worker = $this->staff(Hospital::factory()->create(), 'healthcare_worker');
        $foreign = $this->staff($otherPractice, 'healthcare_worker');
        $inactive = $this->staff($practice, 'healthcare_worker');
        $hospitalDoctor = $this->staff($hospital, 'healthcare_worker');
        $inactive->update(['status' => UserStatus::SUSPENDED]);

        Sanctum::actingAs($worker);
        foreach ([$inactive, $hospitalDoctor] as $doctor) {
            $this->postJson('/api/referrals', $this->payload([
                'intended_user_id' => $doctor->getKey(),
            ]))->assertUnprocessable()->assertJsonValidationErrors('intended_user_id');
        }

        $this->postJson('/api/referrals', $this->payload([
            'intended_user_id' => $foreign->getKey(),
        ]))->assertCreated();
    }

    public function test_doctor_directory_is_authenticated_and_excludes_ineligible_users(): void
    {
        $practice = Hospital::factory()->practice()->create();
        $hospital = Hospital::factory()->create();
        $worker = $this->staff(Hospital::factory()->create(), 'healthcare_worker');
        $doctor = $this->staff($practice, 'healthcare_worker');
        $hospitalDoctor = $this->staff($hospital, 'healthcare_worker');
        $admin = $this->staff($practice, 'hospital_admin');

        $this->getJson('/api/doctors')->assertUnauthorized();

        Sanctum::actingAs($worker);
        $this->getJson('/api/doctors?name='.urlencode($doctor->name))->assertOk()
            ->assertJsonFragment(['id' => $doctor->getKey()])
            ->assertJsonMissing(['id' => $hospitalDoctor->getKey()])
            ->assertJsonMissing(['id' => $admin->getKey()]);
    }

    public function test_direct_doctor_referral_is_visible_to_the_intended_doctor_only_on_receiving_side(): void
    {
        $referring = Hospital::factory()->create();
        $practice = Hospital::factory()->practice()->create();
        $otherPractice = Hospital::factory()->practice()->create();
        $worker = $this->staff($referring, 'healthcare_worker');
        $doctor = $this->staff($practice, 'healthcare_worker');
        $otherDoctor = $this->staff($otherPractice, 'healthcare_worker');

        Sanctum::actingAs($worker);
        $this->postJson('/api/referrals', $this->payload([
            'intended_user_id' => $doctor->getKey(),
        ]))->assertCreated();
        $referral = Referral::firstOrFail();

        $this->actingAs($doctor)->get("/referrals/{$referral->getKey()}")->assertOk();
        $this->actingAs($otherDoctor)->get("/referrals/{$referral->getKey()}")->assertForbidden();
    }

    public function test_direct_doctor_referral_pages_render_without_a_receiving_hospital(): void
    {
        $referring = Hospital::factory()->create();
        $practice = Hospital::factory()->practice()->create();
        $doctor = $this->staff($practice, 'healthcare_worker');
        $referral = Referral::factory()->create([
            'referring_hospital_id' => $referring->getKey(),
            'receiving_hospital_id' => null,
            'intended_user_id' => $doctor->getKey(),
        ]);

        $this->actingAs($doctor)->get("/referrals/{$referral->getKey()}")
            ->assertOk()
            ->assertSee('Direct doctor referral')
            ->assertSee($doctor->name);
    }

    public function test_direct_doctor_receives_a_notification_for_messages_from_the_referring_team(): void
    {
        $referring = Hospital::factory()->create();
        $practice = Hospital::factory()->practice()->create();
        $sender = $this->staff($referring, 'healthcare_worker');
        $doctor = $this->staff($practice, 'healthcare_worker');
        $referral = Referral::factory()->create([
            'referring_hospital_id' => $referring->getKey(),
            'receiving_hospital_id' => null,
            'intended_user_id' => $doctor->getKey(),
        ]);

        Sanctum::actingAs($sender);
        $this->postJson("/api/referrals/{$referral->getKey()}/messages", [
            'body' => 'Please review the attached handover.',
        ])->assertCreated();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $doctor->getKey(),
            'referral_id' => $referral->getKey(),
            'type' => 'message_received',
        ]);
    }
}
