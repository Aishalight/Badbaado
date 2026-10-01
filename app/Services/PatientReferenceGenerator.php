<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Support\Str;

class PatientReferenceGenerator
{
    private const MAX_ATTEMPTS = 10;

    /**
     * Patients.reference is uniquely indexed, so a rare collision must be
     * retried rather than surfaced to the clinician as a failed referral.
     */
    public function generate(): string
    {
        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $reference = 'PT-'.strtoupper(Str::random(6));

            if (! Patient::where('reference', $reference)->exists()) {
                return $reference;
            }
        }

        throw new \RuntimeException('Unable to allocate a unique patient reference.');
    }
}
