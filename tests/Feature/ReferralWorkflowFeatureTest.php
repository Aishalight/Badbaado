<?php

namespace Tests\Feature;

use App\Models\Hospital;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesReferralStaff;
use Tests\TestCase;

class ReferralWorkflowFeatureTest extends TestCase
{
    use CreatesReferralStaff;
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'receiving_hospital_id' => 999,
            'department' => 'Cardiology',
            'referral_reason' => 'Chest pain with ECG abnormality.',
            'symptoms' => 'Retrosternal chest pain.',
            'vitals' => ['bp' => '160/95', 'hr' => 110, 'rr' => 22, 'spo2' => 96, 'temp' => 37.1],
            'consciousness' => 'alert',
            'trauma_indicator' => false,
            'is_emergency' => true,
            'urgency' => 'critical',
            'patient' => ['name' => 'Test Patient', 'age' => 61, 'gender' => 'male'],
        ], $overrides);
    }

    public function test_healthcare_worker_creates_draft_referral_and_persists_patient_and_audit_log(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');

        Sanctum::actingAs($worker);

        $response = $this->postJson('/api/referrals', $this->validPayload([
            'receiving_hospital_id' => $receiving->getKey(),
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.is_emergency', true)
            ->assertJsonPath('data.ai_suggestion.urgency', 'emergent');

        $this->assertDatabaseHas('patients', ['name' => 'Test Patient', 'age' => 61]);
        $this->assertDatabaseHas('referrals', [
            'status' => 'draft',
            'referring_hospital_id' => $referring->getKey(),
            'receiving_hospital_id' => $receiving->getKey(),
            'urgency' => 'critical',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'referral_created',
            'user_id' => $worker->getKey(),
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'ai_urgency_suggested',
            'user_id' => $worker->getKey(),
        ]);
    }

    public function test_user_without_a_hospital_cannot_create_referral(): void
    {
        $this->role('system_admin');
        $admin = User::factory()->withRole('system_admin')->create();

        Sanctum::actingAs($admin);

        $this->postJson('/api/referrals', $this->validPayload())
            ->assertForbidden();
    }

    public function test_hospital_admin_cannot_transition_a_referral(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $admin = $this->staff($referring, 'hospital_admin');
        $referral = Referral::factory()->create([
            'referring_hospital_id' => $referring->getKey(),
            'receiving_hospital_id' => $receiving->getKey(),
            'status' => 'draft',
        ]);

        Sanctum::actingAs($admin);

        $this->postJson("/api/referrals/{$referral->id}/transition", ['status' => 'sent'])
            ->assertForbidden();
    }

    public function test_hospital_admin_cannot_create_referral(): void
    {
        $hospital = Hospital::factory()->create();
        $admin = $this->staff($hospital, 'hospital_admin');

        Sanctum::actingAs($admin);

        $this->postJson('/api/referrals', $this->validPayload([
            'receiving_hospital_id' => $hospital->getKey(),
        ]))->assertForbidden();
    }

    public function test_referring_staff_sends_draft_and_receiving_staff_are_notified(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');
        $receivingCoordinator = $this->staff($receiving, 'referral_coordinator');

        Sanctum::actingAs($worker);

        $created = $this->postJson('/api/referrals', $this->validPayload([
            'receiving_hospital_id' => $receiving->getKey(),
        ]))->assertCreated()->json('data');

        $this->postJson("/api/referrals/{$created['id']}/transition", [
            'status' => 'sent',
        ])->assertOk()
            ->assertJsonPath('data.status', 'sent');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'referral_status_changed',
        ]);
        $this->assertDatabaseHas('referrals', [
            'id' => $created['id'],
            'status' => 'sent',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $receivingCoordinator->getKey(),
            'referral_id' => $created['id'],
            'type' => 'referral_sent',
        ]);
    }

    public function test_sent_referral_is_assigned_to_a_receiving_hospital_healthcare_worker(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');
        $receivingWorker = $this->staff($receiving, 'healthcare_worker');

        Sanctum::actingAs($worker);

        $id = $this->postJson('/api/referrals', $this->validPayload([
            'receiving_hospital_id' => $receiving->getKey(),
        ]))->assertCreated()->json('data.id');

        $this->postJson("/api/referrals/{$id}/transition", ['status' => 'sent'])
            ->assertOk()
            ->assertJsonPath('data.assigned_to.id', $receivingWorker->getKey())
            ->assertJsonPath('data.assigned_to.hospital_id', $receiving->getKey())
            ->assertJsonPath('data.assigned_to.role.slug', 'healthcare_worker');

        $this->assertDatabaseHas('referrals', [
            'id' => $id,
            'assigned_to_user_id' => $receivingWorker->getKey(),
        ]);
    }

    public function test_full_journey_completes_with_acknowledgement_and_transfer_statuses(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');
        $receivingStaff = $this->staff($receiving, 'referral_coordinator');

        Sanctum::actingAs($worker);
        $id = $this->postJson('/api/referrals', $this->validPayload([
            'receiving_hospital_id' => $receiving->getKey(),
        ]))->assertCreated()->json('data.id');

        $this->postJson("/api/referrals/{$id}/transition", ['status' => 'sent'])->assertOk();

        Sanctum::actingAs($receivingStaff);

        $this->postJson("/api/referrals/{$id}/transition", ['status' => 'received'])->assertOk();
        $this->postJson("/api/referrals/{$id}/transition", ['status' => 'under_review'])->assertOk();
        $this->postJson("/api/referrals/{$id}/transition", ['status' => 'accepted'])->assertOk();
        $this->postJson("/api/referrals/{$id}/transition", ['status' => 'transfer_in_progress'])->assertOk();
        $this->postJson("/api/referrals/{$id}/transition", ['status' => 'arrived'])->assertOk();
        $this->postJson("/api/referrals/{$id}/transition", ['status' => 'completed'])->assertOk();

        $this->assertDatabaseHas('referrals', ['id' => $id, 'status' => 'completed']);
    }

    public function test_rejection_without_reason_returns_422(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');

        Sanctum::actingAs($worker);
        $id = $this->postJson('/api/referrals', $this->validPayload([
            'receiving_hospital_id' => $receiving->getKey(),
        ]))->assertCreated()->json('data.id');
        $this->postJson("/api/referrals/{$id}/transition", ['status' => 'sent'])->assertOk();

        Sanctum::actingAs($this->staff($receiving, 'referral_coordinator'));

        $this->postJson("/api/referrals/{$id}/transition", ['status' => 'received'])->assertOk();
        $this->postJson("/api/referrals/{$id}/transition", ['status' => 'under_review'])->assertOk();

        $this->postJson("/api/referrals/{$id}/transition", ['status' => 'rejected'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'The rejection reason field is required when status is rejected.');

        $this->assertDatabaseHas('referrals', ['id' => $id, 'status' => 'under_review']);
    }

    public function test_rejection_with_reason_records_the_reason(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');

        Sanctum::actingAs($worker);
        $id = $this->postJson('/api/referrals', $this->validPayload([
            'receiving_hospital_id' => $receiving->getKey(),
        ]))->assertCreated()->json('data.id');
        $this->postJson("/api/referrals/{$id}/transition", ['status' => 'sent'])->assertOk();

        Sanctum::actingAs($this->staff($receiving, 'referral_coordinator'));

        $this->postJson("/api/referrals/{$id}/transition", ['status' => 'received'])->assertOk();
        $this->postJson("/api/referrals/{$id}/transition", ['status' => 'under_review'])->assertOk();

        $this->postJson("/api/referrals/{$id}/transition", [
            'status' => 'rejected',
            'rejection_reason' => 'No ICU capacity at receiving end.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        $this->assertDatabaseHas('referrals', [
            'id' => $id,
            'status' => 'rejected',
            'rejection_reason' => 'No ICU capacity at receiving end.',
        ]);
    }

    public function test_illegal_transition_returns_422(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');

        Sanctum::actingAs($worker);
        $id = $this->postJson('/api/referrals', $this->validPayload([
            'receiving_hospital_id' => $receiving->getKey(),
        ]))->assertCreated()->json('data.id');

        $this->postJson("/api/referrals/{$id}/transition", ['status' => 'completed'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Cannot transition referral from draft to completed.');
    }

    public function test_terminal_referral_rejects_further_transitions(): void
    {
        $referral = Referral::factory()->create([
            'status' => 'rejected',
        ]);
        $receiving = Hospital::find($referral->receiving_hospital_id);

        Sanctum::actingAs($this->staff($receiving, 'referral_coordinator'));

        $this->postJson("/api/referrals/{$referral->getKey()}/transition", ['status' => 'completed'])
            ->assertStatus(422);
    }
}
