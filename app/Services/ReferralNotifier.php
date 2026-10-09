<?php

namespace App\Services;

use App\Enums\NotificationSeverity;
use App\Enums\ReferralStatus;
use App\Models\Notification;
use App\Models\Referral;
use App\Models\User;

class ReferralNotifier
{
    /**
     * The severity each transition carries. Rejections and cancellations change
     * the clinical plan, an unacknowledged or in-flight transfer means a patient
     * is waiting, and the remaining milestones are routine progress records.
     *
     * @var array<string, NotificationSeverity>
     */
    private const SEVERITIES = [
        'referral_sent' => NotificationSeverity::WARNING,
        'referral_received' => NotificationSeverity::INFO,
        'referral_under_review' => NotificationSeverity::INFO,
        'referral_accepted' => NotificationSeverity::INFO,
        'referral_rejected' => NotificationSeverity::CRITICAL,
        'transfer_in_progress' => NotificationSeverity::WARNING,
        'patient_arrived' => NotificationSeverity::INFO,
        'referral_completed' => NotificationSeverity::INFO,
        'referral_cancelled' => NotificationSeverity::WARNING,
    ];

    public function notifyOnTransition(Referral $referral): void
    {
        $referral->loadMissing(['assignedTo', 'referringHospital', 'receivingHospital', 'intendedUser']);

        match ($referral->status) {
            ReferralStatus::SENT => $referral->assignedTo
                ? $this->notifyAssignee($referral, 'referral_sent', 'New referral assigned to you', $referral->referral_number.' was referred to '.$this->destinationName($referral).' and assigned to you')
                : $this->notifyReceivingStaff($referral, 'referral_sent', 'New referral received', $referral->referral_number.' from '.$referral->referringHospital->short_name),
            ReferralStatus::RECEIVED => $this->notifyReferringStaff($referral, 'referral_received', 'Referral received', $referral->referral_number.' was acknowledged by '.$this->destinationName($referral)),
            ReferralStatus::UNDER_REVIEW => $this->notifyCoordinator($referral, 'referral_under_review', 'Referral under review', $referral->referral_number.' is being reviewed by '.$this->destinationName($referral)),
            ReferralStatus::ACCEPTED => $this->notifyReferringStaff($referral, 'referral_accepted', 'Referral accepted', $referral->referral_number.' was accepted for admission'),
            ReferralStatus::REJECTED => $this->notifyReferringStaff($referral, 'referral_rejected', 'Referral rejected', $referral->referral_number.' was rejected'),
            ReferralStatus::TRANSFER_IN_PROGRESS => $this->notifyReferringStaff($referral, 'transfer_in_progress', 'Transfer in progress', $referral->referral_number.' — patient transfer has started'),
            ReferralStatus::ARRIVED => $this->notifyReferringStaff($referral, 'patient_arrived', 'Patient arrived', $referral->referral_number.' — patient arrived at '.$this->destinationName($referral)),
            ReferralStatus::COMPLETED => $this->notifyReferringStaff($referral, 'referral_completed', 'Referral completed', $referral->referral_number.' — referral closed successfully'),
            ReferralStatus::CANCELLED => $this->notifyReferringStaff($referral, 'referral_cancelled', 'Referral cancelled', $referral->referral_number.' was cancelled'),
            default => null,
        };
    }

    public function notifyOnMessage(Referral $referral, User $sender): void
    {
        $referral->loadMissing(['referringHospital', 'receivingHospital', 'intendedUser']);

        $users = User::query()
            ->where(function ($query) use ($referral): void {
                $query->where('hospital_id', $referral->referring_hospital_id);

                if ($referral->receiving_hospital_id !== null) {
                    $query->orWhere('hospital_id', $referral->receiving_hospital_id);
                }
            })
            ->whereHas('role', fn ($query) => $query->whereIn('slug', [
                'healthcare_worker',
                'referral_coordinator',
                'hospital_admin',
            ]))
            ->whereKeyNot($sender->getKey())
            ->get();

        if ($referral->intendedUser !== null && $referral->intendedUser->isNot($sender)) {
            $users->push($referral->intendedUser);
        }

        $this->notifyUsers(
            $users->unique('id'),
            $referral,
            'message_received',
            'New referral message',
            $sender->name.' added a message to '.$referral->referral_number,
        );
    }

    private function destinationName(Referral $referral): string
    {
        return $referral->receivingHospital?->short_name
            ?? $referral->intendedUser?->name
            ?? 'the receiving care team';
    }

    private function severityFor(string $type): NotificationSeverity
    {
        return self::SEVERITIES[$type] ?? NotificationSeverity::INFO;
    }

    private function notifyReceivingStaff(Referral $referral, string $type, string $title, string $body): void
    {
        if ($referral->receiving_hospital_id === null) {
            $this->notifyUsers(
                $referral->intendedUser ? [$referral->intendedUser] : [],
                $referral,
                $type,
                $title,
                $body,
            );

            return;
        }

        $this->notifyUsers(
            User::where('hospital_id', $referral->receiving_hospital_id)
                ->whereHas('role', fn ($q) => $q->whereIn('slug', ['referral_coordinator', 'hospital_admin', 'healthcare_worker']))
                ->get(),
            $referral,
            $type,
            $title,
            $body,
        );
    }

    private function notifyAssignee(Referral $referral, string $type, string $title, string $body): void
    {
        $this->notifyUsers([$referral->assignedTo], $referral, $type, $title, $body);
    }

    private function notifyReferringStaff(Referral $referral, string $type, string $title, string $body): void
    {
        $this->notifyUsers(
            User::where('hospital_id', $referral->referring_hospital_id)
                ->whereHas('role', fn ($q) => $q->whereIn('slug', ['referral_coordinator', 'hospital_admin', 'healthcare_worker']))
                ->get(),
            $referral,
            $type,
            $title,
            $body,
        );
    }

    private function notifyCoordinator(Referral $referral, string $type, string $title, string $body): void
    {
        $this->notifyUsers(
            User::where('hospital_id', $referral->receiving_hospital_id)
                ->whereHas('role', fn ($q) => $q->where('slug', 'referral_coordinator'))
                ->get(),
            $referral,
            $type,
            $title,
            $body,
        );
    }

    private function notifyUsers(iterable $users, Referral $referral, string $type, string $title, string $body): void
    {
        $severity = $this->severityFor($type);

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'referral_id' => $referral->id,
                'type' => $type,
                'severity' => $severity,
                'title' => $title,
                'body' => $body,
            ]);
        }
    }
}
