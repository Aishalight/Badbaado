<?php

namespace App\Services;

use App\Models\Referral;

class UrgencySuggestionService
{
    /**
     * @return array{urgency: string, confidence: float, reason: string}
     */
    public function suggest(Referral $referral): array
    {
        $score = 0;
        $signals = [];
        $vitals = $referral->vitals ?? [];

        if ($referral->is_emergency) {
            $score += 3;
            $signals[] = 'an emergency pre-alert was selected';
        }

        if ($referral->trauma_indicator) {
            $score += 2;
            $signals[] = 'trauma was indicated';
        }

        if (($vitals['spo2'] ?? null) !== null && (float) $vitals['spo2'] < 92) {
            $score += 3;
            $signals[] = 'oxygen saturation is below 92%';
        }

        if (($vitals['hr'] ?? null) !== null && ((float) $vitals['hr'] > 120 || (float) $vitals['hr'] < 50)) {
            $score += 2;
            $signals[] = 'heart rate is outside the usual adult range';
        }

        if ($referral->consciousness && in_array(strtolower($referral->consciousness), ['unresponsive', 'altered', 'reduced'], true)) {
            $score += 3;
            $signals[] = 'reduced consciousness was reported';
        }

        $urgency = match (true) {
            $score >= 6 => 'critical',
            $score >= 3 => 'emergent',
            $score >= 1 => 'urgent',
            default => 'routine',
        };

        $reason = $signals
            ? 'Signals considered: '.implode('; ', $signals).'.'
            : 'No high-priority structured signals were detected in the submitted information.';

        return [
            'urgency' => $urgency,
            'confidence' => round(min(0.95, 0.55 + ($score * 0.06)), 2),
            'reason' => $reason,
        ];
    }
}
