<?php

namespace App\Policies;

use App\Models\User;

class BackupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('system_admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('system_admin');
    }

    public function download(User $user): bool
    {
        return $user->hasRole('system_admin');
    }

    public function delete(User $user): bool
    {
        return $user->hasRole('system_admin');
    }
}
