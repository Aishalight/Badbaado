<?php

namespace App\Providers;

use App\Listeners\AuditAuthEvents;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Login::class => [AuditAuthEvents::class.'@onLogin'],
        Logout::class => [AuditAuthEvents::class.'@onLogout'],
        PasswordReset::class => [AuditAuthEvents::class.'@onPasswordReset'],
    ];
}
