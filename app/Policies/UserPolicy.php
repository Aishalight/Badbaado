<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can administer accounts.
     */
    public function viewAny(User $actor): bool
    {
        return $actor->hasRole('system_admin') || $actor->hasRole('hospital_admin');
    }

    /**
     * Determine whether the user can create accounts.
     */
    public function create(User $actor): bool
    {
        return $actor->hasRole('system_admin') || $actor->hasRole('hospital_admin');
    }

    /**
     * Determine whether the user can update a target account.
     */
    public function update(User $actor, User $target): bool
    {
        if ($actor->hasRole('system_admin')) {
            return true;
        }

        if (! $actor->hasRole('hospital_admin')) {
            return false;
        }

        return $target->hospital_id === $actor->hospital_id && ! $target->hasRole('system_admin');
    }

    /**
     * Determine whether the user can delete a target account.
     */
    public function delete(User $actor, User $target): bool
    {
        if ($target->is($actor)) {
            return false;
        }

        return $this->update($actor, $target);
    }
}
