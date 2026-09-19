<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\AuditLogger;
use App\Support\AuditActions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function edit(): View
    {
        return view('settings.edit', ['user' => request()->user()->load(['role', 'hospital'])]);
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $updates = $request->safe()->except('avatar');

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $updates['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($updates);

        return back()->with('status', 'Profile updated.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->update(['password' => $request->validated('password')]);

        $this->auditLogger->record($user, AuditActions::AUTH_PASSWORD_CHANGED, $user, [
            'email' => $user->email,
            'source' => 'profile',
        ]);

        return back()->with('status', 'Password updated.');
    }
}
