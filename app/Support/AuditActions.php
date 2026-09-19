<?php

namespace App\Support;

/**
 * Canonical audit-log actions. Kept in one place so every writer uses the same
 * strings the command centre filters on.
 */
final class AuditActions
{
    public const REFERRAL_CREATED = 'referral_created';

    public const AI_URGENCY_SUGGESTED = 'ai_urgency_suggested';

    public const REFERRAL_ATTACHMENTS_ADDED = 'referral_attachments_added';

    public const REFERRAL_ASSIGNED = 'referral_assigned';

    public const REFERRAL_STATUS_CHANGED = 'referral_status_changed';

    public const USER_CREATED = 'user_created';

    public const USER_UPDATED = 'user_updated';

    public const USER_DELETED = 'user_deleted';

    public const HOSPITAL_CREATED = 'hospital_created';

    public const HOSPITAL_UPDATED = 'hospital_updated';

    public const HOSPITAL_DELETED = 'hospital_deleted';

    public const AUTH_LOGIN = 'auth.login';

    public const AUTH_LOGIN_FAILED = 'auth.login_failed';

    public const AUTH_LOGOUT = 'auth.logout';

    public const AUTH_PASSWORD_CHANGED = 'auth.password_changed';

    public const AUTH_INACTIVE_ACCOUNT = 'auth.inactive_account';

    public const MESSAGE_SENT = 'message_sent';

    public const ATTACHMENT_DOWNLOADED = 'attachment_downloaded';

    public const NOTIFICATION_SENT = 'notification_sent';

    public const SETTINGS_UPDATED = 'settings_updated';

    public const CMS_UPDATED = 'cms_updated';

    public const ANNOUNCEMENT_CREATED = 'announcement_created';

    public const ANNOUNCEMENT_UPDATED = 'announcement_updated';

    public const ANNOUNCEMENT_DELETED = 'announcement_deleted';

    public const FAQ_CREATED = 'faq_created';

    public const FAQ_UPDATED = 'faq_updated';

    public const FAQ_DELETED = 'faq_deleted';

    public const PLATFORM_ALERT_SENT = 'platform_alert_sent';

    public const BACKUP_CREATED = 'backup_created';

    public const BACKUP_DELETED = 'backup_deleted';

    public const BACKUP_FAILED = 'backup_failed';

    /**
     * Actions that the Security & SOC module treats as security-sensitive.
     *
     * @return array<int, string>
     */
    public static function securitySensitive(): array
    {
        return [
            self::USER_CREATED,
            self::USER_UPDATED,
            self::USER_DELETED,
            self::HOSPITAL_CREATED,
            self::HOSPITAL_UPDATED,
            self::HOSPITAL_DELETED,
            self::AUTH_PASSWORD_CHANGED,
            self::SETTINGS_UPDATED,
            self::CMS_UPDATED,
            self::ANNOUNCEMENT_CREATED,
            self::ANNOUNCEMENT_UPDATED,
            self::ANNOUNCEMENT_DELETED,
            self::FAQ_CREATED,
            self::FAQ_UPDATED,
            self::FAQ_DELETED,
            self::PLATFORM_ALERT_SENT,
            self::BACKUP_CREATED,
            self::BACKUP_DELETED,
        ];
    }

    /**
     * @return array<string, string> action => human label
     */
    public static function labels(): array
    {
        return [
            self::REFERRAL_CREATED => 'Referral created',
            self::AI_URGENCY_SUGGESTED => 'AI urgency suggested',
            self::REFERRAL_ATTACHMENTS_ADDED => 'Attachments added',
            self::REFERRAL_ASSIGNED => 'Referral assigned',
            self::REFERRAL_STATUS_CHANGED => 'Referral status changed',
            self::USER_CREATED => 'User created',
            self::USER_UPDATED => 'User updated',
            self::USER_DELETED => 'User deleted',
            self::HOSPITAL_CREATED => 'Hospital created',
            self::HOSPITAL_UPDATED => 'Hospital updated',
            self::HOSPITAL_DELETED => 'Hospital deleted',
            self::AUTH_LOGIN => 'Login',
            self::AUTH_LOGIN_FAILED => 'Failed login',
            self::AUTH_LOGOUT => 'Logout',
            self::AUTH_PASSWORD_CHANGED => 'Password changed',
            self::AUTH_INACTIVE_ACCOUNT => 'Inactive account rejected',
            self::MESSAGE_SENT => 'Message sent',
            self::ATTACHMENT_DOWNLOADED => 'Attachment downloaded',
            self::NOTIFICATION_SENT => 'Notification sent',
            self::SETTINGS_UPDATED => 'Settings updated',
            self::CMS_UPDATED => 'CMS content updated',
            self::ANNOUNCEMENT_CREATED => 'Announcement created',
            self::ANNOUNCEMENT_UPDATED => 'Announcement updated',
            self::ANNOUNCEMENT_DELETED => 'Announcement deleted',
            self::FAQ_CREATED => 'FAQ created',
            self::FAQ_UPDATED => 'FAQ updated',
            self::FAQ_DELETED => 'FAQ deleted',
            self::PLATFORM_ALERT_SENT => 'Platform alert sent',
            self::BACKUP_CREATED => 'Backup created',
            self::BACKUP_DELETED => 'Backup deleted',
            self::BACKUP_FAILED => 'Backup failed',
        ];
    }
}
