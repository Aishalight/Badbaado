<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewPasswordRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\SessionInvalidator;
use App\Support\AuditActions;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function store(NewPasswordRequest $request, SessionInvalidator $sessions): RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) use ($request, $sessions): void {
                // forceFill so the plain password still passes through the
                // model's `hashed` cast; the remember token goes so that any
                // "remember me" cookie stops being valid too.
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => null,
                ])->save();

                event(new PasswordReset($user));

                $this->auditLogger->record($user, AuditActions::AUTH_PASSWORD_RESET, $user, [
                    'email' => $user->email,
                    'source' => 'reset_link',
                ]);

                // Every session opened with the old credential is now suspect.
                $sessions->invalidateOthers($request, $user);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
        }

        return redirect()
            ->route('login')
            ->with('status', __(Password::PASSWORD_RESET));
    }
}
