<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewProviderApplicationRequest;
use App\Models\ProviderApplication;
use App\Services\ProviderRegistrationService;
use Illuminate\Http\RedirectResponse;

class ProviderApplicationReviewController extends Controller
{
    public function __construct(private readonly ProviderRegistrationService $registrations) {}

    public function approve(ProviderApplication $application): RedirectResponse
    {
        $this->registrations->approve($application, auth()->user());

        return redirect()
            ->route('admin.applications')
            ->with('status', sprintf('%s has been approved and can now sign in.', $application->user?->name));
    }

    public function reject(ReviewProviderApplicationRequest $request, ProviderApplication $application): RedirectResponse
    {
        $this->registrations->reject(
            $application,
            auth()->user(),
            $request->validated('rejection_reason'),
        );

        return redirect()
            ->route('admin.applications')
            ->with('status', sprintf('%s has been rejected.', $application->user?->name));
    }
}
