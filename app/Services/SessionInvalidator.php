<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Invalidates sessions that were established with a now-stale credential.
 *
 * Only the `database` session driver can be enumerated, so other drivers rely on
 * the session rotation alone.
 */
class SessionInvalidator
{
    /**
     * Drop every other session belonging to the user and rotate the current one.
     */
    public function invalidateOthers(Request $request, User $user): void
    {
        $this->deleteOtherSessions($user, $request->session()->getId());

        if ($user->remember_token !== null) {
            $user->forceFill(['remember_token' => null])->save();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Remove session rows for the user other than the active one.
     */
    private function deleteOtherSessions(User $user, ?string $currentId): void
    {
        if (config('session.driver') !== 'database' || ! Schema::hasTable('sessions')) {
            return;
        }

        DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', $user->getAuthIdentifier())
            ->when($currentId !== null, fn ($query) => $query->where('id', '!=', $currentId))
            ->delete();
    }
}
