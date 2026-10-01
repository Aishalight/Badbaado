<?php

namespace App\Services;

use App\Enums\ReferralStatus;
use App\Models\Hospital;
use App\Models\Referral;
use App\Models\User;
use App\Support\DatabaseExpressions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(User $user): array
    {
        $isSystem = $user->hasRole('system_admin');
        $hospitalId = $isSystem ? null : $user->hospital_id;

        $statusCounts = $this->countBy($this->scopedReferrals($user), 'status');
        $urgencyCounts = $this->countBy($this->scopedReferrals($user), 'urgency', whereNotNull: true);

        $monthQuery = $this->scopedReferrals($user);
        $monthly = $monthQuery
            ->selectRaw(DatabaseExpressions::yearMonth($monthQuery, 'created_at').' as month')
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('aggregate', 'month')
            ->sortKeys()
            ->slice(-6);

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
            'total_referrals' => $this->scopedReferrals($user)->count(),
            'emergency_count' => $this->scopedReferrals($user)->where('is_emergency', true)->count(),
            'active_transfers' => $this->scopedReferrals($user)
                ->whereIn('status', ReferralStatus::inMotion())
                ->count(),
            'users_count' => $users,
            'hospitals_count' => Hospital::where('is_active', true)->count(),
            'total_hospitals' => $isSystem ? Hospital::count() : null,
            'status_counts' => $statusCounts,
            'urgency_counts' => $urgencyCounts,
            'monthly' => $monthly
                ->map(fn (int|string $count, string $month) => ['month' => $month, 'count' => (int) $count])
                ->values(),
            'recent' => $recent,
        ];
    }

    /**
     * Groups in the database rather than loading every referral into memory.
     *
     * @return Collection<string, int>
     */
    private function countBy(Builder $query, string $column, bool $whereNotNull = false): Collection
    {
        $grouped = $query
            ->selectRaw("{$column} as bucket")
            ->selectRaw('COUNT(*) as aggregate')
            ->when($whereNotNull, fn (Builder $builder) => $builder->whereNotNull($column))
            ->groupBy($column)
            ->pluck('aggregate', 'bucket');

        return $grouped->map(fn (int|string $count): int => (int) $count);
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

        if ($user->hospital_id === null) {
            return $query->whereRaw('0 = 1');
        }

        return $query->where(function ($sub) use ($user) {
            $sub->where('referring_hospital_id', $user->hospital_id)
                ->orWhere('receiving_hospital_id', $user->hospital_id);
        });
    }
}
