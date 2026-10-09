<?php

namespace App\Enums;

enum ProviderApplicationStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending review',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * The user status an application moves to once decided.
     */
    public function resultingUserStatus(): UserStatus
    {
        return match ($this) {
            self::APPROVED => UserStatus::ACTIVE,
            self::REJECTED => UserStatus::REJECTED,
            self::PENDING => UserStatus::PENDING,
        };
    }
}
