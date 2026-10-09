<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\Referral;
use App\Models\User;

class ReceivingOwnerAssigner
{
    /**
     * Ensure the referral has a receiving-side owner.
     */
    public function assign(Referral $referral): ?User
    {
        if ($referral->assigned_to_user_id !== null) {
            return $referral->assignedTo;
        }

        $owner = $this->intendedOwner($referral)
            ?? $this->preferredOwner($referral)
            ?? $this->fallbackOwner($referral, 'hospital_admin')
            ?? $this->fallbackOwner($referral, 'referral_coordinator');

        if ($owner === null) {
            return null;
        }

        $referral->update(['assigned_to_user_id' => $owner->getKey()]);

        return $owner;
    }

    /**
     * The doctor the referral was explicitly addressed to, if still eligible.
     */
    private function intendedOwner(Referral $referral): ?User
    {
        if ($referral->intended_user_id === null) {
            return null;
        }

        return User::query()
            ->whereKey($referral->intended_user_id)
            ->where('status', UserStatus::ACTIVE)
            ->whereHas('role', fn ($query) => $query->whereIn('slug', User::REFERRAL_STAFF_ROLES))
            ->whereHas('hospital', fn ($query) => $query
                ->where('kind', 'practice')
                ->where('is_active', true))
            ->first();
    }

    private function preferredOwner(Referral $referral): ?User
    {
        if ($referral->receiving_hospital_id === null) {
            return null;
        }

        return User::query()
            ->where('hospital_id', $referral->receiving_hospital_id)
            ->where('status', UserStatus::ACTIVE)
            ->whereHas('role', fn ($query) => $query->where('slug', 'healthcare_worker'))
            ->withCount(['referralsAssigned' => fn ($query) => $query->needsTriage()])
            ->orderBy('referrals_assigned_count')
            ->orderBy('id')
            ->first();
    }

    private function fallbackOwner(Referral $referral, string $roleSlug): ?User
    {
        if ($referral->receiving_hospital_id === null) {
            return null;
        }

        return User::query()
            ->where('hospital_id', $referral->receiving_hospital_id)
            ->where('status', UserStatus::ACTIVE)
            ->whereHas('role', fn ($query) => $query->where('slug', $roleSlug))
            ->orderBy('id')
            ->first();
    }
}
