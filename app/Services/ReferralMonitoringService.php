<?php

namespace App\Services;

use App\Enums\ReferralStatus;
use App\Enums\Urgency;
use App\Models\Hospital;
use App\Models\Referral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Network-wide referral monitoring. System administrators see across every
 * hospital, so every figure in this module is computed from all referrals.
 */
class ReferralMonitoringService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(): array
    {
        $funnel = Referral::query()
            ->select([DB::raw('status'), DB::raw('COUNT(*) as count')])
            ->groupBy('status')
            ->pluck('count', 'status');

        $urgent = Referral::query()
            ->select([DB::raw('urgency'), DB::raw('COUNT(*) as count')])
            ->whereIn('urgency', collect(Urgency::cases())->map->value)
            ->groupBy('urgency')
            ->pluck('count', 'urgency');

        $activeStatuses = [
            ReferralStatus::SENT,
            ReferralStatus::RECEIVED,
            ReferralStatus::UNDER_REVIEW,
            ReferralStatus::ACCEPTED,
            ReferralStatus::TRANSFER_IN_PROGRESS,
            ReferralStatus::ARRIVED,
        ];

        $inMotion = Referral::whereIn('status', array_map(fn ($status) => $status->value, $activeStatuses));

        $oldest = Referral::orderByDesc('created_at')->limit(1)->value('created_at');

        return [
            'total_referrals' => Referral::count(),
            'open_referrals' => (clone $inMotion)->count(),
            'completed_referrals' => Referral::where('status', ReferralStatus::COMPLETED)->count(),
            'pre_alerts' => Referral::where('is_emergency', true)->count(),
            'funnel' => $funnel,
            'urgency' => $urgent->sortByDesc('count'),
            'avg_age_hours' => $oldest ? $this->averageAgeHours() : 0,
            'referrals_today' => Referral::where('created_at', '>=', now()->startOfDay())->count(),
            'references_last_7d' => Referral::where('created_at', '>=', now()->subDays(7))->count(),
        ];
    }

    /**
     * Per-hospital load: outgoing, incoming, open caseload.
     *
     * @return Collection<int, Hospital>
     */
    public function hospitalLoad(): Collection
    {
        return Hospital::query()
            ->withCount([
                'outgoingReferrals',
                'incomingReferrals',
                'outgoingReferrals as open_sent' => fn ($query) => $this->openWhere($query, 'referring_hospital_id'),
                'incomingReferrals as open_received' => fn ($query) => $this->openWhere($query, 'receiving_hospital_id'),
                'users',
            ])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array{status?: string, urgency?: string, hospital_id?: int|string|string|null, q?: string}  $filters
     */
    public function referrals(array $filters = [], ?int $perPage = 25)
    {
        return Referral::query()
            ->with(['referringHospital:id,name,short_name', 'receivingHospital:id,name,short_name', 'referringUser:id,name', 'patient'])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['urgency'] ?? null, fn (Builder $query, string $urgency) => $query->where('urgency', $urgency))
            ->when($filters['hospital_id'] ?? null, function (Builder $query, int $hospitalId) {
                $query->where(function (Builder $sub) use ($hospitalId) {
                    $sub->where('referring_hospital_id', $hospitalId)->orWhere('receiving_hospital_id', $hospitalId);
                });
            })
            ->when($filters['q'] ?? null, fn (Builder $query, string $q) => $query->where(function (Builder $sub) use ($q) {
                $sub->where('referral_number', 'like', "%{$q}%")
                    ->orWhereHas('patient', fn ($patient) => $patient->where('name', 'like', "%{$q}%"));
            }))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array<int, array{label: string, key: string, color: string}> statuses present in the funnel
     */
    public function funnelSteps(): array
    {
        return collect(ReferralStatus::cases())->map(fn (ReferralStatus $status) => [
            'label' => $status->label(),
            'key' => $status->value,
            'color' => $status->color(),
        ])->all();
    }

    /**
     * Average age (in hours) of ongoing referrals, computed with a portable
     * driver-specific expression so the command centre works on any database.
     */
    private function averageAgeHours(): int
    {
        $expression = match (DB::connection()->getDriverName()) {
            'sqlite' => '(julianday(CURRENT_TIMESTAMP) - julianday(created_at)) * 24',
            'mysql', 'mariadb' => 'TIMESTAMPDIFF(HOUR, created_at, NOW())',
            'pgsql' => 'EXTRACT(EPOCH FROM (NOW() - created_at)) / 3600',
            'sqlsrv' => 'DATEDIFF(HOUR, created_at, GETDATE())',
            default => 'EXTRACT(EPOCH FROM (NOW() - created_at)) / 3600',
        };

        return (int) round((float) (Referral::avg(DB::raw($expression)) ?? 0));
    }

    private function openWhere($query, string $column): void
    {
        $query->whereIn('status', [
            ReferralStatus::SENT->value,
            ReferralStatus::RECEIVED->value,
            ReferralStatus::UNDER_REVIEW->value,
            ReferralStatus::ACCEPTED->value,
        ]);
        $query->whereNotNull($column);
    }
}
