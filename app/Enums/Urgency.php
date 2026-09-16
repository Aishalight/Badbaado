<?php

namespace App\Enums;

enum Urgency: string
{
    case ROUTINE = 'routine';
    case URGENT = 'urgent';
    case EMERGENT = 'emergent';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::ROUTINE => 'Routine',
            self::URGENT => 'Urgent',
            self::EMERGENT => 'Emergent',
            self::CRITICAL => 'Critical',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ROUTINE => 'green',
            self::URGENT => 'yellow',
            self::EMERGENT => 'orange',
            self::CRITICAL => 'red',
        };
    }
}
