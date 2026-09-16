<?php

namespace Tests\Feature;

use App\Models\Hospital;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesReferralStaff;
use Tests\TestCase;

class ReferralAttachmentFeatureTest extends TestCase
{
    use CreatesReferralStaff;
    use RefreshDatabase;

    private function payload(Hospital $receiving): array
    {
        return [
            'receiving_hospital_id' => $receiving->getKey(),
            'department' => 'Cardiology',
            'referral_reason' => 'Chest pain with ECG abnormality.',
            'urgency' => 'critical',
            'patient' => ['name' => 'Test Patient', 'age' => 61, 'gender' => 'male'],
        ];
    }

    public function test_referral_with_attachments_persists_and_stores_files(): void
    {
        Storage::fake('local');
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');

        Sanctum::actingAs($worker);

        $response = $this->post('/api/referrals', $this->payload($receiving) + [
            'attachments' => [
                UploadedFile::fake()->create('ecg.pdf', 128, 'application/pdf'),
                UploadedFile::fake()->create('consent.pdf', 60, 'application/pdf'),
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.attachments.0.original_name', 'ecg.pdf')
            ->assertJsonCount(2, 'data.attachments');

        $attachmentId = $response->json('data.attachments.0.id');

        $this->assertDatabaseHas('referral_attachments', [
            'id' => $attachmentId,
            'original_name' => 'ecg.pdf',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'referral_attachments_added',
        ]);
        Storage::disk('local')->assertExists(
            "referrals/{$response->json('data.id')}/".$response->json('data.attachments.0.filename')
        );
    }

    public function test_referral_rejects_non_whitelisted_file_type(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();

        Sanctum::actingAs($this->staff($referring, 'healthcare_worker'));

        $this->post('/api/referrals', $this->payload($receiving) + [
            'attachments' => [UploadedFile::fake()->create('script.php', 10)],
        ])->assertUnprocessable();
    }

    public function test_referral_rejects_more_than_five_attachments(): void
    {
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();

        Sanctum::actingAs($this->staff($referring, 'healthcare_worker'));

        $files = collect(range(1, 6))
            ->map(fn (int $i) => UploadedFile::fake()->create("file-{$i}.pdf", 10))
            ->all();

        $this->post('/api/referrals', $this->payload($receiving) + [
            'attachments' => $files,
        ])->assertUnprocessable();
    }

    public function test_involved_staff_can_download_attachment(): void
    {
        Storage::fake('local');
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $worker = $this->staff($referring, 'healthcare_worker');

        Sanctum::actingAs($worker);

        $created = $this->post('/api/referrals', $this->payload($receiving) + [
            'attachments' => [UploadedFile::fake()->create('scan.pdf', 64, 'application/pdf')],
        ])->assertCreated()->json('data');

        $this->get("/api/referrals/{$created['id']}/attachments/{$created['attachments'][0]['id']}")
            ->assertOk()
            ->assertDownload('scan.pdf');
    }

    public function test_unrelated_hospital_cannot_download_attachment(): void
    {
        Storage::fake('local');
        $referring = Hospital::factory()->create();
        $receiving = Hospital::factory()->create();
        $unrelated = Hospital::factory()->create();

        Sanctum::actingAs($this->staff($referring, 'healthcare_worker'));

        $created = $this->post('/api/referrals', $this->payload($receiving) + [
            'attachments' => [UploadedFile::fake()->create('scan.pdf', 64, 'application/pdf')],
        ])->assertCreated()->json('data');

        Sanctum::actingAs($this->staff($unrelated, 'healthcare_worker'));

        $this->get("/api/referrals/{$created['id']}/attachments/{$created['attachments'][0]['id']}")
            ->assertForbidden();
    }
}
