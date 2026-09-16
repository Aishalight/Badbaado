<?php

namespace App\Http\Controllers;

use App\Enums\ReferralStatus;
use App\Models\Hospital;
use App\Models\Referral;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            [
                'value' => Hospital::where('is_active', true)->count(),
                'unit' => '',
                'label' => 'Hospitals on the network',
            ],
            [
                'value' => Referral::count(),
                'unit' => '',
                'label' => 'Referrals coordinated',
            ],
            [
                'value' => Referral::where('is_emergency', true)->count(),
                'unit' => '',
                'label' => 'Emergency pre-alerts raised',
            ],
            [
                'value' => Referral::whereIn('status', [
                    ReferralStatus::TRANSFER_IN_PROGRESS,
                    ReferralStatus::ARRIVED,
                ])->count(),
                'unit' => '',
                'label' => 'Transfers currently tracked',
            ],
        ];

        return view('landing', ['stats' => $stats]);
    }
}
