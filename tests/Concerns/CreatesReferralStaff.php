<?php

namespace Tests\Concerns;

use App\Models\Hospital;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;

trait CreatesReferralStaff
{
    protected function role(string $slug): Role
    {
        return Role::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => Str::title(str_replace('_', ' ', $slug)),
                'description' => '',
            ]
        );
    }

    protected function staff(Hospital $hospital, string $roleSlug): User
    {
        $this->role($roleSlug);

        return User::factory()
            ->atHospital($hospital->getKey())
            ->withRole($roleSlug)
            ->create();
    }
}
