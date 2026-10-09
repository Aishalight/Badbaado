<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        // Point the broker's notification at our own named route so the emailed
        // link survives a future change of URL structure.
        ResetPassword::createUrlUsing(
            fn (object $notifiable, string $token): string => URL::route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ])
        );

        RateLimiter::for('login', function ($request) {
            return Limit::perMinute(5)->by(Str::transliterate(
                Str::lower($request->string('email')).'|'.$request->ip()
            ));
        });

        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(120)->by($request->user()?->getAuthIdentifier() ?: $request->ip());
        });

        RateLimiter::for('backups', function ($request) {
            return Limit::perHour(6)->by($request->user()?->getAuthIdentifier() ?: $request->ip());
        });

        // Reset links are the one unauthenticated route that sends mail, so it is
        // capped per address and per IP rather than trusting the broker alone.
        RateLimiter::for('password-reset', function ($request) {
            return [
                Limit::perMinute(2)->by('ip|'.$request->ip()),
                Limit::perHour(5)->by('email|'.Str::lower((string) $request->input('email'))),
            ];
        });
    }
}
