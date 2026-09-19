<?php

namespace App\Listeners;

use App\Services\AuditLogger;
use App\Support\AuditActions;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;

/**
 * Bridges Laravel's authentication lifecycle into the audit log so the
 * Security & SOC module is fed by genuine authentication events.
 */
class AuditAuthEvents
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function onLogin(Login $event): void
    {
        $user = $event->user;

        $this->auditLogger->record($user, AuditActions::AUTH_LOGIN, $user, [
            'guard' => $event->guard,
            'email' => $user->email,
        ]);
    }

    public function onLogout(Logout $event): void
    {
        $this->auditLogger->record($event->user, AuditActions::AUTH_LOGOUT, $event->user, [
            'guard' => $event->guard,
            'email' => $event->user->email,
        ]);
    }

    public function onPasswordReset(PasswordReset $event): void
    {
        $this->auditLogger->record($event->user, AuditActions::AUTH_PASSWORD_CHANGED, $event->user, [
            'email' => $event->user->email,
            'source' => 'broker',
        ]);
    }
}
