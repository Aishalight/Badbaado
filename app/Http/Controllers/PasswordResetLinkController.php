<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordResetLinkRequest;
use App\Services\AuditLogger;
use App\Support\AuditActions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Email a reset link to a staff address.
     *
     * The confirmation is identical for registered and unregistered addresses, so
     * this endpoint cannot be used to enumerate who holds an account. The audit
     * trail keeps the real outcome for administrators.
     */
    public function store(PasswordResetLinkRequest $request): RedirectResponse
    {
        $status = Password::sendResetLink($request->only('email'));

        $this->auditLogger->record(null, AuditActions::AUTH_PASSWORD_RESET_REQUESTED, null, [
            'email' => $request->validated('email'),
            'dispatched' => $status === Password::RESET_LINK_SENT,
            'reason' => $status,
            'guard' => 'web',
        ]);

        return back()->with('status', __(Password::RESET_LINK_SENT));
    }
}
