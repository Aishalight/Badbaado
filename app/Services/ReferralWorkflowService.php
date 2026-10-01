<?php

namespace App\Services;

use App\Enums\ReferralStatus;
use App\Exceptions\InvalidReferralTransitionException;
use App\Models\Patient;
use App\Models\Referral;
use App\Models\ReferralAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ReferralWorkflowService
{
    /**
     * Attachment paths written during the current createReferral() call, so a
     * failed transaction does not leave unreferenced clinical files on disk.
     *
     * @var array<int, string>
     */
    private array $storedAttachmentPaths = [];

    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly ReferralNumberGenerator $referralNumberGenerator,
        private readonly PatientReferenceGenerator $patientReferenceGenerator,
        private readonly ReferralNotifier $notifier,
        private readonly UrgencySuggestionService $urgencySuggestion,
        private readonly ReceivingOwnerAssigner $ownerAssigner,
    ) {}

    public function createReferral(User $user, array $data): Referral
    {
        $this->storedAttachmentPaths = [];

        try {
            return DB::transaction(function () use ($user, $data) {
                $patient = Patient::create([
                    'name' => $data['patient']['name'],
                    'age' => $data['patient']['age'] ?? null,
                    'gender' => $data['patient']['gender'] ?? null,
                    'blood_group' => $data['patient']['blood_group'] ?? null,
                    'reference' => $this->patientReferenceGenerator->generate(),
                ]);

                $referral = Referral::create([
                    'referral_number' => $this->referralNumberGenerator->generate(),
                    'referring_hospital_id' => $user->hospital_id,
                    'receiving_hospital_id' => $data['receiving_hospital_id'],
                    'intended_user_id' => $data['intended_user_id'] ?? null,
                    'referring_user_id' => $user->id,
                    'patient_id' => $patient->id,
                    'status' => ReferralStatus::DRAFT,
                    'urgency' => $data['urgency'] ?? null,
                    'is_emergency' => $data['is_emergency'] ?? false,
                    'department' => $data['department'],
                    'referral_reason' => $data['referral_reason'],
                    'symptoms' => $data['symptoms'] ?? null,
                    'vitals' => $data['vitals'] ?? null,
                    'consciousness' => $data['consciousness'] ?? null,
                    'trauma_indicator' => $data['trauma_indicator'] ?? false,
                    'existing_conditions' => $data['existing_conditions'] ?? null,
                    'current_interventions' => $data['current_interventions'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);

                $referral->refresh();
                $referral->update([
                    'ai_suggestion' => $this->urgencySuggestion->suggest($referral),
                ]);

                $this->storeAttachments($user, $referral, $data['attachments'] ?? []);

                $this->auditLogger->record($user, 'referral_created', $referral, [
                    'referral_number' => $referral->referral_number,
                    'receiving_hospital_id' => $referral->receiving_hospital_id,
                    'intended_user_id' => $referral->intended_user_id,
                ]);
                $this->auditLogger->record($user, 'ai_urgency_suggested', $referral, [
                    'suggestion' => $referral->ai_suggestion,
                ]);

                return $referral;
            });
        } catch (Throwable $exception) {
            $this->discardStoredAttachmentFiles();

            throw $exception;
        } finally {
            $this->storedAttachmentPaths = [];
        }
    }

    /**
     * Removes attachment files written for a referral that never committed, so a
     * failed submission cannot leak clinical documents onto disk.
     */
    private function discardStoredAttachmentFiles(): void
    {
        foreach ($this->storedAttachmentPaths as $path) {
            Storage::disk('local')->delete($path);
        }
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    private function storeAttachments(User $user, Referral $referral, array $files): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $storedName = $this->generateStoredFileName($file);

            $file->storeAs("referrals/{$referral->id}", $storedName, 'local');

            $this->storedAttachmentPaths[] = "referrals/{$referral->id}/{$storedName}";

            ReferralAttachment::create([
                'referral_id' => $referral->id,
                'user_id' => $user->id,
                'original_name' => $file->getClientOriginalName(),
                'filename' => $storedName,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        if ($files) {
            $this->auditLogger->record($user, 'referral_attachments_added', $referral, [
                'attachments' => count($files),
            ]);
        }
    }

    private function generateStoredFileName(UploadedFile $file): string
    {
        return Str::random(32).'.'.$file->getClientOriginalExtension();
    }

    public function transition(User $user, Referral $referral, ReferralStatus $next, ?string $rejectionReason = null): Referral
    {
        return DB::transaction(function () use ($user, $referral, $next, $rejectionReason) {
            $referral = Referral::whereKey($referral->getKey())->lockForUpdate()->firstOrFail();

            if (! $referral->status->canTransitionTo($next)) {
                throw new InvalidReferralTransitionException(
                    "Cannot transition referral from {$referral->status->value} to {$next->value}."
                );
            }

            $previous = $referral->status;

            $referral->update([
                'status' => $next,
                'rejection_reason' => $next === ReferralStatus::REJECTED ? $rejectionReason : null,
            ]);

            if ($next === ReferralStatus::SENT) {
                $owner = $this->ownerAssigner->assign($referral);

                if ($owner !== null) {
                    $this->auditLogger->record($user, 'referral_assigned', $referral, [
                        'assigned_to_user_id' => $owner->getKey(),
                        'assigned_to_hospital_id' => $owner->hospital_id,
                    ]);
                }
            }

            $this->auditLogger->record($user, 'referral_status_changed', $referral, [
                'from' => $previous->value,
                'to' => $next->value,
            ]);

            $this->notifier->notifyOnTransition($referral);

            return $referral;
        });
    }
}
