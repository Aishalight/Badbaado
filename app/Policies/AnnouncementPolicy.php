<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('system_admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('system_admin');
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $user->hasRole('system_admin');
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->hasRole('system_admin');
    }
}
