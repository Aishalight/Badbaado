<?php

namespace App\Http\Controllers;

use App\Enums\ReferralStatus;
use App\Enums\Urgency;
use App\Models\Referral;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * The queue only renders the most recent hand-offs, so only that many rows
     * are loaded. Headline metrics are counted in the database instead.
     */
    private const QUEUE_LIMIT = 25;

    public function __construct(private readonly AnalyticsService $analytics) {}

    public function __invoke(): View
    {
        $user = auth()->user();

        if ($user->hasRole('hospital_admin') || $user->hasRole('system_admin')) {
            return view('admin.analytics', $this->analytics->overview($user));
        }

        return view('dashboard', [
            'user' => $user,
            'referrals' => $this->queueFor($user),
            'counts' => $this->countsFor($user),
            'role' => $user->role?->slug,
        ]);
    }

    /**
     * @return Collection<int, Referral>
     */
    private function queueFor(User $user): Collection
    {
        return Referral::visibleTo($user)
            ->orderByDesc('created_at')
            ->limit(self::QUEUE_LIMIT)
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
    }

    /**
     * @return array{total: int, active: int, emergencies: int, urgent: int, incoming: int}
     */
    private function countsFor(User $user): array
    {
        return [
            'total' => Referral::visibleTo($user)->count(),
            'active' => Referral::visibleTo($user)->whereIn('status', ReferralStatus::inMotion())->count(),
            'emergencies' => Referral::visibleTo($user)->where('is_emergency', true)->count(),
            'urgent' => Referral::visibleTo($user)
                ->whereIn('urgency', [Urgency::CRITICAL, Urgency::EMERGENT])
                ->count(),
            'incoming' => Referral::visibleTo($user)
                ->where('receiving_hospital_id', $user->hospital_id)
                ->whereIn('status', [ReferralStatus::SENT, ReferralStatus::RECEIVED, ReferralStatus::UNDER_REVIEW])
                ->count(),
        ];
    }
}
