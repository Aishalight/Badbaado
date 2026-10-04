<?php

namespace App\Enums;

enum NotificationSeverity: string
{
    case CRITICAL = 'critical';
    case WARNING = 'warning';
    case INFO = 'info';
    case ANNOUNCEMENT = 'announcement';

    public function label(): string
    {
        return match ($this) {
            self::CRITICAL => 'Critical',
            self::WARNING => 'Warning',
            self::INFO => 'Info',
            self::ANNOUNCEMENT => 'Announcement',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CRITICAL => 'red',
            self::WARNING => 'amber',
            self::INFO => 'blue',
            self::ANNOUNCEMENT => 'slate',
        };
    }

    /**
     * What a recipient is expected to do about a notification at this severity.
     */
    public function guidance(): string
    {
        return match ($this) {
            self::CRITICAL => 'The request was denied. Re-plan the patient pathway now.',
            self::WARNING => 'Someone is waiting or a plan changed. Respond today.',
            self::INFO => 'Routine handover milestone. No action needed.',
            self::ANNOUNCEMENT => 'Platform-wide operational message.',
        };
    }

    /**
     * Whether this severity interrupts the recipient until it is acknowledged.
     *
     * @return array<int, self>
     */
    public static function interrupting(): array
    {
        return [self::CRITICAL, self::WARNING];
    }

    public function isInterrupting(): bool
    {
        return in_array($this, self::interrupting(), true);
    }

    /**
     * The severity an existing notification type implies, used when backfilling
     * rows that predate the severity column.
     *
     * @return array<int, string>
     */
    public static function legacyTypeMap(): array
    {
        return [
            'referral_rejected' => self::CRITICAL->value,
            'referral_cancelled' => self::WARNING->value,
            'transfer_in_progress' => self::WARNING->value,
            'referral_sent' => self::WARNING->value,
            'referral_under_review' => self::INFO->value,
            'referral_received' => self::INFO->value,
            'referral_accepted' => self::INFO->value,
            'patient_arrived' => self::INFO->value,
            'referral_completed' => self::INFO->value,
            'platform_alert' => self::ANNOUNCEMENT->value,
        ];
    }
}
