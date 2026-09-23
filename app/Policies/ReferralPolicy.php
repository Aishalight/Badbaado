<?php

namespace App\Policies;

use App\Enums\ReferralStatus;
use App\Models\Referral;
use App\Models\User;

class ReferralPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Referral $referral): bool
    {
        if ($user->hasRole('system_admin')) {
            return true;
        }

        return $user->hospital_id === $referral->referring_hospital_id
            || $user->hospital_id === $referral->receiving_hospital_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hospital_id !== null
            && in_array($user->role?->slug, ['healthcare_worker', 'referral_coordinator'], true);
    }

    /**
     * Transition authority varies by the target status.
     */
    public function transition(User $user, Referral $referral, ReferralStatus $target): bool
    {
        if ($user->hasRole('system_admin')) {
            return true;
        }

        return match ($target) {
            ReferralStatus::SENT => $this->canSend($user, $referral),
            ReferralStatus::RECEIVED => $this->canReceive($user, $referral),
            ReferralStatus::UNDER_REVIEW,
            ReferralStatus::ACCEPTED,
            ReferralStatus::REJECTED => $this->isReceivingHospitalStaff($user, $referral),
            ReferralStatus::TRANSFER_IN_PROGRESS,
            ReferralStatus::ARRIVED,
            ReferralStatus::COMPLETED => $this->isInvolvedHospitalStaff($user, $referral),
            ReferralStatus::CANCELLED => $this->isReferringHospitalStaff($user, $referral),
            default => false,
        };
    }

    private function canSend(User $user, Referral $referral): bool
    {
        return $this->isReferralStaff($user)
            && $user->hospital_id === $referral->referring_hospital_id
            && $referral->status === ReferralStatus::DRAFT;
    }

    private function canReceive(User $user, Referral $referral): bool
    {
        return $this->isReferralStaff($user)
            && $user->hospital_id === $referral->receiving_hospital_id
            && $referral->status === ReferralStatus::SENT;
    }

    private function isReceivingHospitalStaff(User $user, Referral $referral): bool
    {
        return $this->isReferralParticipant($user)
            && $user->hospital_id === $referral->receiving_hospital_id;
    }

    private function isReferringHospitalStaff(User $user, Referral $referral): bool
    {
        return $this->isReferralParticipant($user)
            && $user->hospital_id === $referral->referring_hospital_id;
    }

    private function isInvolvedHospitalStaff(User $user, Referral $referral): bool
    {
        return $this->isReferralParticipant($user)
            && ($user->hospital_id === $referral->referring_hospital_id
            || $user->hospital_id === $referral->receiving_hospital_id);
    }

    private function isReferralStaff(User $user): bool
    {
        return in_array($user->role?->slug, ['healthcare_worker', 'referral_coordinator'], true);
    }

    private function isReferralParticipant(User $user): bool
    {
        return in_array($user->role?->slug, ['healthcare_worker', 'referral_coordinator', 'hospital_admin'], true);
    }
}
