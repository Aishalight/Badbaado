<?php

namespace App\Support;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Keeps failed logins constant-time.
 *
 * Without this, an unknown email skips the password hash entirely and answers
 * far faster than a known email with a wrong password, which turns the login
 * endpoint into an account-existence oracle. Verifying against a real hash of
 * a random secret makes both paths cost the same bcrypt work.
 */
trait EqualizesPasswordCheckTime
{
    private static ?string $decoyPasswordHash = null;

    private function burnPasswordVerificationTime(string $password): void
    {
        self::$decoyPasswordHash ??= Hash::make(Str::random(40));

        Hash::check($password, self::$decoyPasswordHash);
    }
}
