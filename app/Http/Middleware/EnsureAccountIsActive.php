<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    /**
     * Keep unverified accounts out of the console. The status check runs before
     * any role check, because a pending account carries no role yet and a
     * suspended one must not reach admin pages on the strength of a stale role.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->status !== UserStatus::ACTIVE) {
            return redirect()->route('pending');
        }

        return $next($request);
    }
}
