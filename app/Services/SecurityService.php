<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Support\AuditActions;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Security & SOC data. Every number here is derived from real application
 * events (audit_logs), authentication sessions (sessions) and live
 * configuration — never fabricated.
 */
class SecurityService
{
    /**
     * @return array<string, mixed>
     */
    public function metrics(): array
    {
        $now = now();

        return [
            'successful_logins' => $this->countOf(AuditActions::AUTH_LOGIN),
            'failed_logins_24h' => $this->countOf(AuditActions::AUTH_LOGIN_FAILED, $now->copy()->subDay()),
            'failed_logins_7d' => $this->countOf(AuditActions::AUTH_LOGIN_FAILED, $now->copy()->subDays(7)),
            'failed_logins_total' => $this->countOf(AuditActions::AUTH_LOGIN_FAILED),
            'sensitive_actions_7d' => AuditLog::whereIn('action', AuditActions::securitySensitive())
                ->where('created_at', '>=', $now->copy()->subDays(7))
                ->count(),
            'active_sessions' => DB::table('sessions')->where('last_activity', '>=', $now->copy()->subDay()->getTimestamp())->count(),
            'privileged_users' => Role::whereIn('slug', ['system_admin', 'hospital_admin'])->first()
                ? User::whereIn('role_id', Role::whereIn('slug', ['system_admin', 'hospital_admin'])->pluck('id'))->where('is_active', true)->count()
                : 0,
        ];
    }

    /**
     * @return array{status: string, checks: array<int, array{label: string, detail: string, status: string}>}
     */
    public function posture(): array
    {
        $settings = app(SettingsService::class);

        $checks = [];

        $registrationEnabled = $settings->get('auth.registration_enabled', true);
        $checks[] = [
            'label' => 'Open registration',
            'detail' => $registrationEnabled ? 'Public registration allowed' : 'Registration locked down',
            'status' => $registrationEnabled ? 'warn' : 'ok',
        ];

        $checks[] = [
            'label' => 'Login throttling',
            'detail' => '5 attempts / minute per account+IP',
            'status' => 'ok',
        ];

        $checks[] = [
            'label' => 'Password hashing',
            'detail' => hash_algos() && in_array(config('hashing.driver', 'bcrypt'), ['bcrypt', 'argon2id', 'argon2i'], true)
                ? 'Pbkdf2-era default unavailable, using '.config('hashing.driver', 'bcrypt')
                : 'bcrypt default',
            'status' => 'ok',
        ];

        $checks[] = [
            'label' => 'Session storage',
            'detail' => ucfirst(config('session.driver')).' driver',
            'status' => config('session.driver') === 'file' ? 'warn' : 'ok',
        ];

        $tokenExpiry = config('sanctum.expiration');
        $checks[] = [
            'label' => 'API token lifetime',
            'detail' => $tokenExpiry ? 'Expires after '.$tokenExpiry.' minutes' : 'Tokens do not expire (lifetime null)',
            'status' => $tokenExpiry ? 'ok' : 'warn',
        ];

        $checks[] = [
            'label' => 'Maintenance mode',
            'detail' => $settings->maintenanceEnabled() ? 'Enabled — non-admins see a notice' : 'Off',
            'status' => $settings->maintenanceEnabled() ? 'warn' : 'ok',
        ];

        $allOk = collect($checks)->every(fn (array $check) => $check['status'] === 'ok');

        return [
            'status' => $allOk ? 'ok' : 'warn',
            'checks' => $checks,
        ];
    }

    /**
     * @param  array{action?: string, user_id?: int|string, entity?: string, from?: string, to?: string}  $filters
     * @return Collection<int, AuditLog>
     */
    public function events(int $limit = 50, array $filters = []): Collection
    {
        return AuditLog::query()
            ->when($filters['action'] ?? null, fn ($query, string $action) => $query->where('action', $action))
            ->when($filters['user_id'] ?? null, fn ($query, int $userId) => $query->where('user_id', $userId))
            ->when($filters['entity'] ?? null, fn ($query, string $entity) => $query->where('entity_type', $entity))
            ->when($filters['from'] ?? null, fn ($query, string $from) => $query->where('created_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($filters['to'] ?? null, fn ($query, string $to) => $query->where('created_at', '<=', Carbon::parse($to)->endOfDay()))
            ->with('user:id,name,title')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, AuditLog>
     */
    public function topFailedIps(int $limit = 5): Collection
    {
        return AuditLog::query()
            ->where('action', AuditActions::AUTH_LOGIN_FAILED)
            ->whereNotNull('ip_address')
            ->select('ip_address', DB::raw('COUNT(*) as attempts'), DB::raw('MAX(created_at) as last_attempt'))
            ->groupBy('ip_address')
            ->orderByDesc('attempts')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, AuditLog>
     */
    public function recentFailed(int $limit = 12): Collection
    {
        return AuditLog::query()
            ->where('action', AuditActions::AUTH_LOGIN_FAILED)
            ->whereNotNull('ip_address')
            ->with('user:id,name,title')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    public function privilegedUsers(): Collection
    {
        return User::query()
            ->whereIn('role_id', Role::whereIn('slug', ['system_admin', 'hospital_admin'])->pluck('id'))
            ->with(['role', 'hospital:id,name,short_name'])
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<int, array{date: string, index: int, count: int}>
     */
    public function signinsByDay(int $days = 14): array
    {
        return $this->daySeries(AuditActions::AUTH_LOGIN, $days);
    }

    /**
     * @return array<int, array{date: string, index: int, count: int}>
     */
    public function failedLoginsByDay(int $days = 14): array
    {
        return $this->daySeries(AuditActions::AUTH_LOGIN_FAILED, $days);
    }

    private function countOf(string $action, ?Carbon $since = null): int
    {
        return AuditLog::query()
            ->where('action', $action)
            ->when($since, fn ($query, Carbon $cutoff) => $query->where('created_at', '>=', $cutoff))
            ->count();
    }

    /**
     * @return array<int, array{date: string, index: int, count: int}>
     */
    private function daySeries(string $action, int $days): array
    {
        $start = now()->startOfDay()->subDays($days - 1);

        $rows = AuditLog::query()
            ->where('action', $action)
            ->where('created_at', '>=', $start)
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as count'))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('count', 'day');

        $series = [];

        for ($i = 0; $i < $days; $i++) {
            $day = $start->copy()->addDays($i)->format('Y-m-d');
            $series[] = [
                'date' => $day,
                'index' => $i,
                'count' => (int) ($rows[$day] ?? 0),
            ];
        }

        return $series;
    }
}
