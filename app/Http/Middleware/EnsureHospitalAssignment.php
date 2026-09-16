<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHospitalAssignment
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ($user->hospital_id === null && ! $user->hasRole('system_admin'))) {
            abort(403, 'This action requires a hospital assignment.');
        }

        return $next($request);
    }
}
