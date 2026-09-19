<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Hospital;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Real, downloadable reports. Each export streams from the database — nothing
 * is pre-generated or cached, so reports always reflect current data.
 */
class ReportService
{
    /**
     * @return array<string, string> slug => human label
     */
    public function available(): array
    {
        return [
            'referrals' => 'Referrals',
            'users' => 'Users & access',
            'hospitals' => 'Hospital directory',
            'audit' => 'Audit trail',
            'signins' => 'Authentication logins',
        ];
    }

    public function download(string $report, array $filters = []): StreamedResponse
    {
        $payload = $this->payloadFor($report, $filters);

        return response()->streamDownload(function () use ($payload) {
            $stream = fopen('php://output', 'w');

            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, $payload['headers']);

            foreach ($payload['rows'] as $row) {
                fputcsv($stream, $row);
            }

            fclose($stream);
        }, $payload['filename'], ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, string>>, filename: string}
     */
    private function payloadFor(string $report, array $filters): array
    {
        return match ($report) {
            'referrals' => $this->referrals($filters),
            'users' => $this->users(),
            'hospitals' => $this->hospitals(),
            'audit' => $this->audit($filters),
            'signins' => $this->signins(),
            default => abort(404, 'Unknown report.'),
        };
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, string>>, filename: string}
     */
    private function referrals(array $filters): array
    {
        $referrals = Referral::query()
            ->with(['referringHospital:id,name', 'receivingHospital:id,name', 'referringUser:id,name', 'assignedTo:id,name', 'patient:id,name'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['urgency'] ?? null, fn ($query, string $urgency) => $query->where('urgency', $urgency))
            ->when($filters['from'] ?? null, fn ($query, string $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, string $to) => $query->whereDate('created_at', '<=', $to))
            ->orderByDesc('created_at')
            ->get();

        return [
            'headers' => ['Referral', 'Status', 'Urgency', 'Emergency', 'Department', 'From', 'To', 'Created by', 'Assigned to', 'Patient', 'Created at'],
            'rows' => $referrals->map(fn (Referral $referral) => [
                $referral->referral_number,
                $referral->status->label(),
                $referral->urgency?->label() ?? '',
                $referral->is_emergency ? 'Yes' : 'No',
                $referral->department ?? '',
                $referral->referringHospital?->name ?? '',
                $referral->receivingHospital?->name ?? '',
                $referral->referringUser?->name ?? '',
                $referral->assignedTo?->name ?? '',
                $referral->patient?->name ?? '',
                $referral->created_at?->toDateTimeString() ?? '',
            ])->all(),
            'filename' => 'badbaado-referrals-'.now()->format('Y-m-d-Hi').'.csv',
        ];
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, string>>, filename: string}
     */
    private function users(): array
    {
        $users = User::query()
            ->with(['role:id,slug', 'hospital:id,name'])
            ->orderBy('name')
            ->get();

        return [
            'headers' => ['Name', 'Email', 'Title', 'Phone', 'Role', 'Hospital', 'Active', 'Created at'],
            'rows' => $users->map(fn (User $user) => [
                $user->name,
                $user->email,
                $user->title ?? '',
                $user->phone ?? '',
                $user->role?->slug ?? '',
                $user->hospital?->name ?? 'Platform',
                $user->is_active ? 'Yes' : 'No',
                $user->created_at?->toDateTimeString() ?? '',
            ])->all(),
            'filename' => 'badbaado-users-'.now()->format('Y-m-d-Hi').'.csv',
        ];
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, string>>, filename: string}
     */
    private function hospitals(): array
    {
        $hospitals = Hospital::query()
            ->withCount(['users', 'outgoingReferrals', 'incomingReferrals'])
            ->orderBy('name')
            ->get();

        return [
            'headers' => ['Name', 'Short name', 'Code', 'Level', 'Location', 'Active', 'Users', 'Outgoing referrals', 'Incoming referrals', 'Created at'],
            'rows' => $hospitals->map(fn (Hospital $hospital) => [
                $hospital->name,
                $hospital->short_name,
                $hospital->code,
                $hospital->level ?? '',
                $hospital->location ?? '',
                $hospital->is_active ? 'Yes' : 'No',
                (string) $hospital->users_count,
                (string) $hospital->outgoing_referrals_count,
                (string) $hospital->incoming_referrals_count,
                $hospital->created_at?->toDateTimeString() ?? '',
            ])->all(),
            'filename' => 'badbaado-hospitals-'.now()->format('Y-m-d-Hi').'.csv',
        ];
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, string>>, filename: string}
     */
    private function audit(array $filters): array
    {
        /** @var Builder<AuditLog> $query */
        $query = AuditLog::query()
            ->with('user:id,name,title')
            ->when($filters['action'] ?? null, fn ($q, string $action) => $q->where('action', $action))
            ->when($filters['entity'] ?? null, fn ($q, string $entity) => $q->where('entity_type', $entity))
            ->when($filters['from'] ?? null, fn ($q, string $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, string $to) => $q->whereDate('created_at', '<=', $to))
            ->orderByDesc('created_at')
            ->limit(2000)
            ->get();

        return [
            'headers' => ['Action', 'User', 'Entity', 'Entity ID', 'IP address', 'Metadata', 'At'],
            'rows' => $query->map(fn (AuditLog $log) => [
                $log->action,
                $log->user?->name ?? 'System',
                $log->entity_type,
                (string) $log->entity_id,
                $log->ip_address ?? '',
                json_encode($log->metadata ?? [], JSON_UNESCAPED_SLASHES),
                $log->created_at?->toDateTimeString() ?? '',
            ])->all(),
            'filename' => 'badbaado-audit-'.now()->format('Y-m-d-Hi').'.csv',
        ];
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, string>>, filename: string}
     */
    private function signins(): array
    {
        $logs = AuditLog::query()
            ->whereIn('action', ['auth.login', 'auth.login_failed'])
            ->with('user:id,name,title')
            ->orderByDesc('created_at')
            ->limit(2000)
            ->get();

        return [
            'headers' => ['Outcome', 'User', 'Email', 'IP address', 'At'],
            'rows' => $logs->map(fn (AuditLog $log) => [
                $log->action === 'auth.login' ? 'Success' : 'Failed',
                $log->user?->name ?? 'Unknown',
                $log->metadata['email'] ?? $log->user?->email ?? '',
                $log->ip_address ?? '',
                $log->created_at?->toDateTimeString() ?? '',
            ])->all(),
            'filename' => 'badbaado-signins-'.now()->format('Y-m-d-Hi').'.csv',
        ];
    }
}
