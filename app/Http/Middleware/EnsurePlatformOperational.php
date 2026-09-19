<?php

namespace App\Http\Middleware;

use App\Services\SettingsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformOperational
{
    public function __construct(private readonly SettingsService $settings) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('up', 'api/*')) {
            return $next($request);
        }

        $user = $request->user();

        if ($this->settings->maintenanceEnabled() && ($user === null || ! $user->hasRole('system_admin'))) {
            return response()->view('errors.maintenance', [], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return $next($request);
    }
}
