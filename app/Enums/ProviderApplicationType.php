<?php

namespace App\Enums;

enum ProviderApplicationType: string
{
    case DOCTOR = 'doctor';
    case HOSPITAL = 'hospital';

    public function label(): string
    {
        return match ($this) {
            self::DOCTOR => 'Independent doctor',
            self::HOSPITAL => 'Hospital',
        };
    }

    /**
     * The facility kind created when an application of this type is approved.
     */
    public function facilityKind(): string
    {
        return match ($this) {
            self::DOCTOR => 'practice',
            self::HOSPITAL => 'hospital',
        };
    }
}
