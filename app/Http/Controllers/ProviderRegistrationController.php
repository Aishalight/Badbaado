<?php

namespace App\Http\Controllers;

use App\Enums\ProviderApplicationType;
use App\Http\Requests\RegisterProviderRequest;
use App\Models\Specialty;
use App\Services\ProviderRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProviderRegistrationController extends Controller
{
    public function __construct(private readonly ProviderRegistrationService $registrations) {}

    public function create(): View
    {
        return view('auth.register', [
            'types' => ProviderApplicationType::cases(),
            'specialties' => Specialty::ordered()->get(),
        ]);
    }

    public function store(RegisterProviderRequest $request): RedirectResponse
    {
        $application = $this->registrations->submit(
            ProviderApplicationType::from($request->validated('type')),
            $request->applicationPayload(),
        );

        return redirect()->route('login')->with('status', sprintf(
            'Thanks, %s. Your %s application is awaiting verification. Sign in to check its status.',
            $application->user->name,
            $application->type->label(),
        ));
    }
}
