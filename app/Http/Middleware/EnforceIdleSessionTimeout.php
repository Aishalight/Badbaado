<?php

namespace App\Http\Middleware;

use App\Services\SettingsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceIdleSessionTimeout
{
    /**
     * Session key holding the timestamp of the last authenticated request.
     */
    private const LAST_ACTIVITY_KEY = 'last_activity_at';

    public function __construct(private readonly SettingsService $settings) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $this->timeoutMinutes() === null) {
            return $next($request);
        }

        $session = $request->session();
        $now = $session->get(self::LAST_ACTIVITY_KEY);

        if (is_int($now) && ($now + $this->timeoutSeconds()) < time()) {
            Auth::guard('web')->logout();

            $session->invalidate();
            $session->regenerateToken();

            return redirect()
                ->route('login')
                ->with('status', 'Your session expired due to inactivity. Please sign in again.');
        }

        $session->put(self::LAST_ACTIVITY_KEY, time());

        return $next($request);
    }

    /**
     * Configured idle lifetime in minutes, or null when it is not enforceable.
     */
    private function timeoutMinutes(): ?int
    {
        $minutes = (int) $this->settings->get('auth.session_timeout_minutes', 0);

        return $minutes > 0 ? $minutes : null;
    }

    private function timeoutSeconds(): int
    {
        return $this->timeoutMinutes() * 60;
    }
}
