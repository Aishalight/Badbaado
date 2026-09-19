<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\LoginRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\AuditActions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SessionAuthController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function login(LoginRequest $request): RedirectResponse|JsonResponse
    {
        $user = User::query()->with(['role', 'hospital'])->where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            $this->auditLogger->record(null, AuditActions::AUTH_LOGIN_FAILED, null, [
                'email' => $request->validated('email'),
                'reason' => 'invalid_credentials',
                'guard' => 'web',
            ]);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            $this->auditLogger->record($user, AuditActions::AUTH_INACTIVE_ACCOUNT, $user, [
                'email' => $user->email,
                'guard' => 'web',
            ]);

            throw ValidationException::withMessages([
                'email' => ['This account is disabled. Contact your administrator.'],
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return $request->wantsJson()
            ? response()->json(['ok' => true, 'user' => $user->load(['role', 'hospital'])])
            : redirect()->intended(route('dashboard'));
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
