<?php

namespace App\Services;

use App\Enums\ReferralStatus;
use App\Models\Notification;
use App\Models\Referral;
use App\Models\User;

class ReferralNotifier
{
    public function notifyOnTransition(Referral $referral): void
    {
        $referral->loadMissing(['assignedTo', 'referringHospital', 'receivingHospital']);

        match ($referral->status) {
            ReferralStatus::SENT => $referral->assignedTo
                ? $this->notifyAssignee($referral, 'referral_sent', 'New referral assigned to you', $referral->referral_number.' was referred to '.$referral->receivingHospital->short_name.' and assigned to you')
                : $this->notifyReceivingStaff($referral, 'referral_sent', 'New referral received', $referral->referral_number.' from '.$referral->referringHospital->short_name),
            ReferralStatus::RECEIVED => $this->notifyReferringStaff($referral, 'referral_received', 'Referral received', $referral->referral_number.' was acknowledged by '.$referral->receivingHospital->short_name),
            ReferralStatus::UNDER_REVIEW => $this->notifyCoordinator($referral, 'referral_under_review', 'Referral under review', $referral->referral_number.' is being reviewed by '.$referral->receivingHospital->short_name),
            ReferralStatus::ACCEPTED => $this->notifyReferringStaff($referral, 'referral_accepted', 'Referral accepted', $referral->referral_number.' was accepted for admission'),
            ReferralStatus::REJECTED => $this->notifyReferringStaff($referral, 'referral_rejected', 'Referral rejected', $referral->referral_number.' was rejected'),
            ReferralStatus::TRANSFER_IN_PROGRESS => $this->notifyReferringStaff($referral, 'transfer_in_progress', 'Transfer in progress', $referral->referral_number.' — patient transfer has started'),
            ReferralStatus::ARRIVED => $this->notifyReferringStaff($referral, 'patient_arrived', 'Patient arrived', $referral->referral_number.' — patient arrived at '.$referral->receivingHospital->short_name),
            ReferralStatus::COMPLETED => $this->notifyReferringStaff($referral, 'referral_completed', 'Referral completed', $referral->referral_number.' — referral closed successfully'),
            ReferralStatus::CANCELLED => $this->notifyReferringStaff($referral, 'referral_cancelled', 'Referral cancelled', $referral->referral_number.' was cancelled'),
            default => null,
        };
    }

    private function notifyReceivingStaff(Referral $referral, string $type, string $title, string $body): void
    {
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
        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'referral_id' => $referral->id,
                'type' => $type,
                'title' => $title,
                'body' => $body,
            ]);
        }
    }
}
