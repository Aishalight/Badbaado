<?php

namespace App\Policies;

use App\Models\User;

class SystemSettingPolicy
{
    public function update(User $user): bool
    {
        return $user->hasRole('system_admin');
    }
}
