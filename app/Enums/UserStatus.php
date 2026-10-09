<?php

namespace App\Enums;

enum UserStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case REJECTED = 'rejected';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending Verification',
            self::ACTIVE => 'Active',
            self::REJECTED => 'Rejected',
            self::SUSPENDED => 'Suspended',
        };
    }

    /**
     * Whether the account may reach the console.
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Shown to an account that cannot sign in, per status.
     */
    public function blockedMessage(): string
    {
        return match ($this) {
            self::PENDING => 'Your account is awaiting verification. We will email you once a system administrator has reviewed it.',
            self::REJECTED => 'Your application was not approved. Contact your administrator for details.',
            self::SUSPENDED => 'This account is disabled. Contact your administrator.',
            self::ACTIVE => '',
        };
    }

    /**
     * The active status as a raw database value, for query constraints.
     *
     * @return array<int, string>
     */
    public static function activeValues(): array
    {
        return [self::ACTIVE->value];
    }
}
