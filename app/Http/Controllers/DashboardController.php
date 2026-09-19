<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Services\AnalyticsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    public function __invoke(): View
    {
        $user = auth()->user();

        if (in_array($user->role->slug, ['hospital_admin', 'system_admin'], true)) {
            return view('admin.analytics', $this->analytics->overview($user));
        }

        $referrals = Referral::visibleTo($user)
            ->get([
                'id',
                'status',
                'urgency',
                'is_emergency',
                'created_at',
                'referring_hospital_id',
                'receiving_hospital_id',
                'department',
            ])
            ->load(['referringHospital', 'receivingHospital', 'patient']);

        return view('dashboard', [
            'user' => $user,
            'referrals' => $referrals,
            'role' => $user->role->slug,
        ]);
    }
}
