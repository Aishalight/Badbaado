<?php

namespace App\Http\Controllers\Api;

use App\Enums\ReferralStatus;
use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function overview(Request $request): JsonResponse
    {
        $user = $request->user();
        $isSystem = $user->hasRole('system_admin');
        $hospitalId = $isSystem ? null : $user->hospital_id;

        $referrals = Referral::query()
            ->when($isSystem, fn ($query) => $query, function ($query) use ($hospitalId) {
                $query->where(function ($scope) use ($hospitalId) {
                    $scope->where('referring_hospital_id', $hospitalId)
                        ->orWhere('receiving_hospital_id', $hospitalId);
                });
            })
            ->get(['id', 'status', 'urgency', 'is_emergency', 'created_at']);

        $statusCounts = $referrals->groupBy('status')->map->count()->sortDesc();
        $urgencyCounts = $referrals->groupBy('urgency')->map->count();
        $monthly = $referrals
            ->groupBy(fn (Referral $referral) => $referral->created_at?->format('Y-m'))
            ->map->count()
            ->sortKeys()
            ->slice(-6);

        $activeStatuses = ['sent', 'received', 'under_review', 'accepted', 'transfer_in_progress', 'arrived'];

        $users = User::when(
            $isSystem,
            fn ($query) => $query,
            fn ($query) => $query->where('hospital_id', $hospitalId),
        )->count();

        return response()->json([
            'data' => [
                'scope' => $isSystem ? 'system' : 'hospital',
                'total_referrals' => $referrals->count(),
                'emergency_count' => $referrals->where('is_emergency', true)->count(),
                'active_transfers' => $referrals->whereIn('status', $activeStatuses)->count(),
                'users_count' => $users,
                'hospitals_count' => $isSystem ? Hospital::where('is_active', true)->count() : 1,
                'total_hospitals' => $isSystem ? Hospital::count() : null,
                'status_counts' => array_merge(
                    array_fill_keys(array_map(fn (ReferralStatus $status) => $status->value, ReferralStatus::cases()), 0),
                    $statusCounts->all(),
                ),
                'urgency_counts' => $urgencyCounts->all(),
                'monthly' => $monthly
                    ->map(fn (int $count, string $month) => ['month' => $month, 'count' => $count])
                    ->values(),
            ],
        ]);
    }
}
