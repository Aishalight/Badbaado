<?php

namespace App\Policies;

use App\Models\CmsContent;
use App\Models\User;

class CmsContentPolicy
{
    /**
     * Only system administrators manage public content.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('system_admin');
    }

    public function update(User $user, CmsContent $content): bool
    {
        return $user->hasRole('system_admin');
    }

    public function seed(User $user): bool
    {
        return $user->hasRole('system_admin');
    }
}
