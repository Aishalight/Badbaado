<?php

namespace App\Services;

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

        $owner = $this->preferredOwner($referral)
            ?? $this->fallbackOwner($referral, 'hospital_admin')
            ?? $this->fallbackOwner($referral, 'referral_coordinator');

        if ($owner === null) {
            return null;
        }

        $referral->update(['assigned_to_user_id' => $owner->getKey()]);

        return $owner;
    }

    private function preferredOwner(Referral $referral): ?User
    {
        return User::query()
            ->where('hospital_id', $referral->receiving_hospital_id)
            ->where('is_active', true)
            ->whereHas('role', fn ($query) => $query->where('slug', 'healthcare_worker'))
            ->withCount(['referralsAssigned' => fn ($query) => $query->needsTriage()])
            ->orderBy('referrals_assigned_count')
            ->orderBy('id')
            ->first();
    }

    private function fallbackOwner(Referral $referral, string $roleSlug): ?User
    {
        return User::query()
            ->where('hospital_id', $referral->receiving_hospital_id)
            ->where('is_active', true)
            ->whereHas('role', fn ($query) => $query->where('slug', $roleSlug))
            ->orderBy('id')
            ->first();
    }
}
