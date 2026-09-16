<?php

namespace App\Enums;

enum ReferralStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case RECEIVED = 'received';
    case UNDER_REVIEW = 'under_review';
    case ACCEPTED = 'accepted';
    case TRANSFER_IN_PROGRESS = 'transfer_in_progress';
    case ARRIVED = 'arrived';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SENT => 'Sent',
            self::RECEIVED => 'Received',
            self::UNDER_REVIEW => 'Under Review',
            self::ACCEPTED => 'Accepted',
            self::TRANSFER_IN_PROGRESS => 'Transfer In Progress',
            self::ARRIVED => 'Arrived',
            self::COMPLETED => 'Completed',
            self::REJECTED => 'Rejected',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SENT => 'blue',
            self::RECEIVED => 'cyan',
            self::UNDER_REVIEW => 'yellow',
            self::ACCEPTED => 'green',
            self::TRANSFER_IN_PROGRESS => 'orange',
            self::ARRIVED => 'purple',
            self::COMPLETED => 'emerald',
            self::REJECTED => 'red',
            self::CANCELLED => 'slate',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::COMPLETED, self::REJECTED, self::CANCELLED]);
    }

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::DRAFT => in_array($next, [self::SENT, self::CANCELLED]),
            self::SENT => in_array($next, [self::RECEIVED, self::CANCELLED]),
            self::RECEIVED => in_array($next, [self::UNDER_REVIEW]),
            self::UNDER_REVIEW => in_array($next, [self::ACCEPTED, self::REJECTED]),
            self::ACCEPTED => in_array($next, [self::TRANSFER_IN_PROGRESS]),
            self::TRANSFER_IN_PROGRESS => in_array($next, [self::ARRIVED]),
            self::ARRIVED => in_array($next, [self::COMPLETED]),
            self::COMPLETED, self::REJECTED, self::CANCELLED => false,
        };
    }
}
