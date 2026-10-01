<?php

namespace App\Support;

final class ReferralFormat
{
    /**
     * @var array<string, string>
     */
    private const STATUS_LABELS = [
        'draft' => 'Draft',
        'sent' => 'Sent',
        'received' => 'Received',
        'under_review' => 'Under review',
        'accepted' => 'Accepted',
        'transfer_in_progress' => 'Transfer in progress',
        'arrived' => 'Arrived',
        'completed' => 'Completed',
        'rejected' => 'Rejected',
        'cancelled' => 'Cancelled',
    ];

    /**
     * @var array<string, string>
     */
    private const STATUS_COLORS = [
        'draft' => 'bg-slate-100 text-slate-600',
        'sent' => 'bg-brand-50 text-brand-700',
        'received' => 'bg-accent-50 text-accent-700',
        'under_review' => 'bg-amber-50 text-amber-700',
        'accepted' => 'bg-emerald-50 text-emerald-700',
        'transfer_in_progress' => 'bg-orange-50 text-orange-600',
        'arrived' => 'bg-purple-50 text-purple-700',
        'completed' => 'bg-emerald-100 text-emerald-700',
        'rejected' => 'bg-red-50 text-red-600',
        'cancelled' => 'bg-slate-100 text-slate-500',
    ];

    /**
     * Severity colours keyed by urgency value.
     *
     * @var array<string, array{label: string, klass: string}>
     */
    private const URGENCY = [
        'critical' => ['label' => 'Critical', 'klass' => 'bg-red-50 text-red-600'],
        'emergent' => ['label' => 'Emergent', 'klass' => 'bg-orange-50 text-orange-600'],
        'urgent' => ['label' => 'Urgent', 'klass' => 'bg-amber-50 text-amber-700'],
        'routine' => ['label' => 'Routine', 'klass' => 'bg-emerald-50 text-emerald-600'],
    ];

    public static function statusLabel(?string $status): string
    {
        return self::STATUS_LABELS[$status ?? ''] ?? ucfirst((string) $status);
    }

    public static function urgencyLabel(?string $urgency): string
    {
        if (! $urgency) {
            return '';
        }

        return self::URGENCY[$urgency]['label'] ?? ucfirst($urgency);
    }

    /**
     * Filter options for the referral status select, ordered by workflow.
     *
     * The blank entry always keeps the `''` key so it renders as
     * `<option value="">` and keeps matching an empty query string.
     *
     * @return array<string, string>
     */
    public static function statusOptions(?string $blankLabel = 'All statuses'): array
    {
        $options = self::STATUS_LABELS;

        return $blankLabel === null ? $options : ['' => $blankLabel] + $options;
    }

    /**
     * Filter options for the urgency select, most acute first.
     *
     * @return array<string, string>
     */
    public static function urgencyOptions(?string $blankLabel = 'All urgencies'): array
    {
        $options = [];

        foreach (self::URGENCY as $value => $meta) {
            $options[$value] = $meta['label'];
        }

        return $blankLabel === null ? $options : ['' => $blankLabel] + $options;
    }

    public static function statusPill(?string $status): string
    {
        $label = self::statusLabel($status);
        $klass = self::STATUS_COLORS[$status ?? ''] ?? 'bg-slate-100 text-slate-600';

        return '<span class="status-pill '.$klass.'">'.$label.'</span>';
    }

    public static function urgencyPill(?string $urgency): string
    {
        if (! $urgency) {
            return '';
        }
        $meta = self::URGENCY[$urgency] ?? ['label' => ucfirst($urgency), 'klass' => 'bg-slate-100 text-slate-600'];

        return '<span class="urgency-pill '.$meta['klass'].'">'.$meta['label'].'</span>';
    }

    /**
     * @param  array<string, mixed>|null  $vitals
     * @return array<int, array{label: string, value: string}>
     */
    public static function vitals(?array $vitals): array
    {
        $labels = ['bp' => 'BP', 'hr' => 'HR', 'rr' => 'RR', 'spo2' => 'SpO₂', 'temp' => 'Temp'];

        return collect($labels)
            ->filter(fn ($label, string $key) => isset($vitals[$key]) && $vitals[$key] !== '')
            ->map(fn (string $label, string $key) => [
                'label' => $label,
                'value' => $vitals[$key].($key === 'temp' ? '°C' : ''),
            ])
            ->values()
            ->all();
    }

    public static function bytes(?int $bytes): string
    {
        if (! $bytes) {
            return '';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = min(count($units) - 1, (int) floor(log($bytes, 1024)));

        return round($bytes / 1024 ** $index, $index ? 1 : 0).' '.$units[$index];
    }
}
