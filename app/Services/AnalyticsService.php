<?php

namespace App\Services;

use App\Models\Hospital;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(User $user): array
    {
        $isSystem = $user->hasRole('system_admin');
        $hospitalId = $isSystem ? null : $user->hospital_id;

        $referrals = $this->scopedReferrals($user)
            ->get(['id', 'status', 'urgency', 'is_emergency', 'created_at']);

        $statusCounts = $referrals->groupBy('status')->map->count()->sortDesc();
        $urgencyCounts = $referrals->groupBy('urgency')->map->count();
        $monthly = $referrals
            ->groupBy(fn (Referral $referral) => $referral->created_at?->format('Y-m'))
            ->map->count()
            ->sortKeys()
            ->slice(-6);

        $activeStatuses = ['sent', 'received', 'under_review', 'accepted', 'transfer_in_progress', 'arrived'];

        $users = User::query()
            ->when($isSystem, fn ($query) => $query, fn ($query) => $query->where('hospital_id', $hospitalId))
            ->count();

        $recent = $this->scopedReferrals($user)
            ->with(['referringHospital', 'receivingHospital', 'referringUser:id,name,title', 'patient'])
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return [
            'user' => $user,
            'scope' => $isSystem ? 'system' : 'hospital',
            'total_referrals' => $referrals->count(),
            'emergency_count' => $referrals->where('is_emergency', true)->count(),
            'active_transfers' => $referrals->whereIn('status', $activeStatuses)->count(),
            'users_count' => $users,
            'hospitals_count' => Hospital::where('is_active', true)->count(),
            'total_hospitals' => $isSystem ? Hospital::count() : null,
            'status_counts' => $statusCounts,
            'urgency_counts' => $urgencyCounts,
            'monthly' => $monthly
                ->map(fn (int $count, string $month) => ['month' => $month, 'count' => $count])
                ->values(),
            'recent' => $recent,
        ];
    }

    /**
     * @return Builder<Referral>
     */
    private function scopedReferrals(User $user): Builder
    {
        $query = Referral::query();

        if ($user->hasRole('system_admin')) {
            return $query;
        }

        return $query->where(function ($sub) use ($user) {
            $sub->where('referring_hospital_id', $user->hospital_id)
                ->orWhere('receiving_hospital_id', $user->hospital_id);
        });
    }
}
