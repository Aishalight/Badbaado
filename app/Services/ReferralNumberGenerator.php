<?php

namespace App\Services;

use App\Models\Referral;
use Illuminate\Support\Str;

class ReferralNumberGenerator
{
    public function generate(): string
    {
        do {
            $number = 'REF-'.now()->year.'-'.strtoupper($this->randomSegment());
        } while (Referral::where('referral_number', $number)->exists());

        return $number;
    }

    private function randomSegment(): string
    {
        return substr(Str::upper(Str::random(6)), 0, 6);
    }
}
