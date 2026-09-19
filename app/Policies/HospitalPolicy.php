<?php

namespace App\Policies;

use App\Models\Hospital;
use App\Models\User;

class HospitalPolicy
{
    /**
     * Determine whether the user can view the hospital directory.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('system_admin');
    }

    /**
     * Determine whether the user can create hospitals.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('system_admin');
    }

    /**
     * Determine whether the user can update a hospital.
     */
    public function update(User $user, Hospital $hospital): bool
    {
        return $user->hasRole('system_admin');
    }

    /**
     * Determine whether the user can delete a hospital.
     */
    public function delete(User $user, Hospital $hospital): bool
    {
        return $user->hasRole('system_admin');
    }
}
