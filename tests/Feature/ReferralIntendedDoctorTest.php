<?php

namespace Tests\Feature;

use App\Enums\ReferralStatus;
use App\Models\Hospital;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesReferralStaff;
use Tests\TestCase;

class ReferralIntendedDoctorTest extends TestCase
{
    use CreatesReferralStaff;
    use RefreshDatabase;

    /**
     * @return array<int, array<string, mixed>>
     */
    private function picker(User $worker): array
    {
        return $this->actingAs($worker)
            ->get('/referrals/new')
            ->assertOk()
            ->viewData('hospitalDirectory');
    }

    /**
     * @return array<int, int>
     */
    private function pickerDoctorIds(User $worker, Hospital $hospital): array
    {
        $entry = collect($this->picker($worker))->firstWhere('id', $hospital->getKey());

        return collect($entry['doctors'])->pluck('id')->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Hospital $receiving, array $overrides = []): array
    {
        return array_merge([
            'receiving_hospital_id' => $receiving->getKey(),
            'department' => 'Cardiology',
            'referral_reason' => 'Suspected myocardial infarction.',
            'urgency' => 'urgent',
            'patient' => ['name' => 'Jane Doe', 'age' => 54],
        ], $overrides);
    }

    public function test_referral_can_target_a_specific_doctor_at_the_receiving_hospital(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');
        $doctor = $this->staff($receiving, 'healthcare_worker');

        Sanctum::actingAs($worker);

        $this->postJson('/api/referrals', $this->payload($receiving, [
            'intended_user_id' => $doctor->getKey(),
        ]))->assertCreated();

        $referral = Referral::firstOrFail();
        $this->assertSame($doctor->getKey(), $referral->intended_user_id);
    }

    public function test_intended_doctor_becomes_the_receiving_side_owner_when_sent(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');
        $idleDoctor = $this->staff($receiving, 'healthcare_worker');
        $intended = $this->staff($receiving, 'healthcare_worker');

        Sanctum::actingAs($worker);

        $response = $this->postJson('/api/referrals', $this->payload($receiving, [
            'intended_user_id' => $intended->getKey(),
        ]))->assertCreated();

        $referral = Referral::firstOrFail();
        $this->postJson("/api/referrals/{$referral->getKey()}/transition", [
            'status' => 'sent',
        ])->assertOk();

        $this->assertSame(ReferralStatus::SENT, $referral->refresh()->status);
        $this->assertSame(
            $intended->getKey(),
            $referral->assigned_to_user_id,
            'The explicitly named doctor should own the referral instead of the least-busy worker.'
        );
        $this->assertNotSame($idleDoctor->getKey(), $referral->assigned_to_user_id);
    }

    public function test_owner_assignment_falls_back_when_no_doctor_is_named(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');
        $doctor = $this->staff($receiving, 'healthcare_worker');

        Sanctum::actingAs($worker);

        $this->postJson('/api/referrals', $this->payload($receiving))->assertCreated();
        $referral = Referral::firstOrFail();
        $this->postJson("/api/referrals/{$referral->getKey()}/transition", ['status' => 'sent'])->assertOk();

        $this->assertNull($referral->refresh()->intended_user_id);
        $this->assertSame($doctor->getKey(), $referral->assigned_to_user_id);
    }

    public function test_doctor_from_another_hospital_cannot_be_targeted(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $other = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');
        $foreignDoctor = $this->staff($other, 'healthcare_worker');

        Sanctum::actingAs($worker);

        $this->postJson('/api/referrals', $this->payload($receiving, [
            'intended_user_id' => $foreignDoctor->getKey(),
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors('intended_user_id');
    }

    public function test_inactive_doctor_cannot_be_targeted(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');
        $inactive = $this->staff($receiving, 'healthcare_worker');
        $inactive->update(['is_active' => false]);

        Sanctum::actingAs($worker);

        $this->postJson('/api/referrals', $this->payload($receiving, [
            'intended_user_id' => $inactive->getKey(),
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors('intended_user_id');
    }

    public function test_referring_doctor_cannot_be_targeted_as_the_intended_doctor(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');

        Sanctum::actingAs($worker);

        $this->postJson('/api/referrals', $this->payload($receiving, [
            'intended_user_id' => $worker->getKey(),
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors('intended_user_id');
    }

    public function test_coordinator_at_the_receiving_hospital_can_be_targeted(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');
        $coordinator = $this->staff($receiving, 'referral_coordinator');

        Sanctum::actingAs($worker);

        $this->postJson('/api/referrals', $this->payload($receiving, [
            'intended_user_id' => $coordinator->getKey(),
        ]))->assertCreated();

        $this->assertSame($coordinator->getKey(), Referral::firstOrFail()->intended_user_id);
    }

    public function test_hospital_admin_cannot_be_targeted_via_the_api(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');
        $hospitalAdmin = $this->staff($receiving, 'hospital_admin');

        Sanctum::actingAs($worker);

        $this->postJson('/api/referrals', $this->payload($receiving, [
            'intended_user_id' => $hospitalAdmin->getKey(),
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors('intended_user_id');
    }

    public function test_hospital_admins_are_not_offered_in_the_doctor_picker(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');
        $hospitalAdmin = $this->staff($receiving, 'hospital_admin');
        $doctor = $this->staff($receiving, 'healthcare_worker');

        $doctorIds = $this->pickerDoctorIds($worker, $receiving);

        $this->assertContains($doctor->getKey(), $doctorIds);
        $this->assertNotContains($hospitalAdmin->getKey(), $doctorIds);
    }

    public function test_inactive_doctors_are_not_offered_in_the_doctor_picker(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');
        $active = $this->staff($receiving, 'healthcare_worker');
        $inactive = $this->staff($receiving, 'healthcare_worker');
        $inactive->update(['is_active' => false]);

        $doctorIds = $this->pickerDoctorIds($worker, $receiving);

        $this->assertContains($active->getKey(), $doctorIds);
        $this->assertNotContains($inactive->getKey(), $doctorIds);
    }

    public function test_picker_excludes_the_referring_hospital_and_inactive_hospitals(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $deactivated = Hospital::factory()->create(['is_active' => false]);
        $worker = $this->staff($referring, 'healthcare_worker');

        $hospitalIds = collect($this->picker($worker))->pluck('id')->all();

        $this->assertContains($receiving->getKey(), $hospitalIds);
        $this->assertNotContains($referring->getKey(), $hospitalIds);
        $this->assertNotContains($deactivated->getKey(), $hospitalIds);
    }

    public function test_referral_show_page_reveals_the_intended_doctor_to_the_receiving_hospital(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');
        $coordinator = $this->staff($receiving, 'referral_coordinator');
        $doctor = $this->staff($receiving, 'healthcare_worker');

        Sanctum::actingAs($worker);
        $this->postJson('/api/referrals', $this->payload($receiving, [
            'intended_user_id' => $doctor->getKey(),
        ]))->assertCreated();

        $referral = Referral::firstOrFail();

        $this->actingAs($coordinator)
            ->get("/referrals/{$referral->getKey()}")
            ->assertOk()
            ->assertSee('Intended doctor')
            ->assertSee($doctor->name);
    }

    public function test_referral_index_shows_that_it_is_for_a_specific_doctor(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');
        $doctor = $this->staff($receiving, 'healthcare_worker');

        Sanctum::actingAs($worker);
        $this->postJson('/api/referrals', $this->payload($receiving, [
            'intended_user_id' => $doctor->getKey(),
        ]))->assertCreated();

        $this->actingAs($worker)
            ->get('/referrals')
            ->assertOk()
            ->assertSee('for '.$doctor->name);
    }

    public function test_referral_pages_still_render_when_urgency_was_never_set(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $coordinator = $this->staff($receiving, 'referral_coordinator');

        $referral = Referral::factory()->create([
            'referring_hospital_id' => $referring->getKey(),
            'receiving_hospital_id' => $receiving->getKey(),
            'urgency' => null,
        ]);

        $this->actingAs($coordinator)->get('/referrals')->assertOk();
        $this->actingAs($coordinator)
            ->get("/referrals/{$referral->getKey()}")
            ->assertOk();
    }

    public function test_api_referral_payload_exposes_the_intended_doctor(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');
        $coordinator = $this->staff($receiving, 'referral_coordinator');
        $doctor = $this->staff($receiving, 'healthcare_worker');

        Sanctum::actingAs($worker);
        $this->postJson('/api/referrals', $this->payload($receiving, [
            'intended_user_id' => $doctor->getKey(),
        ]))->assertCreated();

        $referral = Referral::firstOrFail();

        $this->actingAs($coordinator)
            ->getJson("/api/referrals/{$referral->getKey()}")
            ->assertOk()
            ->assertJsonPath('data.intended_to.id', $doctor->getKey());
    }
}
