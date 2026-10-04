<?php

namespace App\Http\Controllers;

use App\Enums\NotificationSeverity;
use App\Enums\ReferralStatus;
use App\Services\CmsService;
use Illuminate\View\View;

class LoginPageController extends Controller
{
    public function __construct(private readonly CmsService $cms) {}

    public function __invoke(): View
    {
        return view('auth.login', [
            'tagline' => $this->cms->get('hero.subtitle', 'Information arrives before the patient.'),
            // The lifecycle a referral actually moves through, read from the enum
            // rather than restated here, so the panel cannot drift from the app.
            'stages' => [
                ReferralStatus::SENT->label(),
                ReferralStatus::RECEIVED->label(),
                ReferralStatus::UNDER_REVIEW->label(),
                ReferralStatus::ACCEPTED->label(),
                ReferralStatus::ARRIVED->label(),
                ReferralStatus::COMPLETED->label(),
            ],
            'roles' => [
                'Health Care Worker',
                'Referral Coordinator',
                'Hospital Admin',
                'System Admin',
            ],
            'severities' => NotificationSeverity::cases(),
        ]);
    }
}
