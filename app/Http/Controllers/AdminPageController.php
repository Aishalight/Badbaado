<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Backup;
use App\Models\Faq;
use App\Models\Hospital;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\BackupService;
use App\Services\CmsService;
use App\Services\ReferralMonitoringService;
use App\Services\ReportService;
use App\Services\SecurityService;
use App\Services\SettingsService;
use App\Services\SystemHealthService;
use App\Support\AuditActions;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminPageController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analytics,
        private readonly ReferralMonitoringService $monitoring,
        private readonly SecurityService $security,
        private readonly SystemHealthService $health,
        private readonly ReportService $reports,
        private readonly BackupService $backups,
        private readonly CmsService $cms,
        private readonly SettingsService $settings,
    ) {}

    public function analytics(): View
    {
        return view('admin.analytics', $this->analytics->overview(auth()->user()));
    }

    /**
     * Command center: a single platform-wide operational surface.
     */
    public function command(): View
    {
        $monitoring = $this->monitoring->overview();
        $securityMetrics = $this->security->metrics();
        $healthChecks = $this->health->checks();

        $healthFlat = array_merge(...array_values($healthChecks));

        $recentActivity = AuditLog::query()
            ->with('user:id,name,title')
            ->orderByDesc('created_at')
            ->take(12)
            ->get();

        $recentAlerts = Notification::query()
            ->where('type', 'platform_alert')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('admin.command', [
            'monitoring' => $monitoring,
            'security' => $securityMetrics,
            'healthGroups' => $healthChecks,
            'healthSummary' => [
                'ok' => collect($healthFlat)->where('status', 'ok')->count(),
                'warn' => collect($healthFlat)->where('status', 'warn')->count(),
                'fail' => collect($healthFlat)->where('status', 'fail')->count(),
            ],
            'recentActivity' => $recentActivity,
            'recentAlerts' => $recentAlerts,
            'actionLabels' => AuditActions::labels(),
            'hospitalLoad' => $this->monitoring->hospitalLoad()->take(8),
        ]);
    }

    public function users(Request $request): View
    {
        $me = auth()->user();
        $isSystem = $me->hasRole('system_admin');
        $isHospitalAdmin = $me->hasRole('hospital_admin');
        $hospitalAdminRoleId = Role::where('slug', 'hospital_admin')->value('id');

        $users = User::query()
            ->when($isSystem, fn ($query) => $query->where('role_id', $hospitalAdminRoleId), fn ($query) => $query->where('hospital_id', $me->hospital_id)->whereNot('role_id', $hospitalAdminRoleId))
            ->when($request->string('q')->toString(), fn ($query, string $search) => $query->where(function ($scope) use ($search) {
                $scope->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            }))
            ->with(['role', 'hospital'])
            ->orderByDesc('id')
            ->paginate(20);

        $roles = $isSystem ? ['hospital_admin'] : ['healthcare_worker', 'referral_coordinator'];

        return view('admin.users', [
            'users' => $users,
            'roles' => $roles,
            'hospitals' => $isSystem ? Hospital::where('is_active', true)->orderBy('name')->get() : collect(),
            'isSystem' => $isSystem,
            'isHospitalAdmin' => $isHospitalAdmin,
        ]);
    }

    public function hospitals(): View
    {
        return view('admin.hospitals', [
            'hospitals' => Hospital::query()
                ->withCount('users')
                ->withCount('outgoingReferrals')
                ->withCount('incomingReferrals')
                ->withCount(['outgoingReferrals as open_outgoing' => fn ($query) => $this->monitoringOpen($query)])
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function referrals(Request $request): View
    {
        $filters = [
            'status' => $request->string('status')->toString() ?: null,
            'urgency' => $request->string('urgency')->toString() ?: null,
            'hospital_id' => $request->integer('hospital_id') ?: null,
            'q' => $request->string('q')->toString() ?: null,
        ];

        return view('admin.referrals', [
            'overview' => $this->monitoring->overview(),
            'hospitalLoad' => $this->monitoring->hospitalLoad(),
            'funnelSteps' => $this->monitoring->funnelSteps(),
            'filters' => $filters,
            'referrals' => $this->monitoring->referrals($filters),
            'hospitals' => Hospital::orderBy('name')->get(),
        ]);
    }

    public function security(Request $request): View
    {
        $filters = [
            'action' => $request->string('action')->toString() ?: null,
            'entity' => $request->string('entity')->toString() ?: null,
            'from' => $request->string('from')->toString() ?: null,
            'to' => $request->string('to')->toString() ?: null,
        ];

        return view('admin.security', [
            'metrics' => $this->security->metrics(),
            'posture' => $this->security->posture(),
            'events' => $this->security->events(50, $filters),
            'topIps' => $this->security->topFailedIps(),
            'recentFailed' => $this->security->recentFailed(),
            'privilegedUsers' => $this->security->privilegedUsers(),
            'signinsChart' => $this->security->signinsByDay(14),
            'failedChart' => $this->security->failedLoginsByDay(14),
            'actionLabels' => AuditActions::labels(),
            'filters' => $filters,
        ]);
    }

    public function audit(Request $request): View
    {
        $filters = [
            'action' => $request->string('action')->toString() ?: null,
            'entity' => $request->string('entity')->toString() ?: null,
            'user_id' => $request->integer('user_id') ?: null,
            'q' => $request->string('q')->toString() ?: null,
            'from' => $request->string('from')->toString() ?: null,
            'to' => $request->string('to')->toString() ?: null,
        ];

        $logs = AuditLog::query()
            ->when($filters['action'], fn ($query, string $action) => $query->where('action', $action))
            ->when($filters['entity'], fn ($query, string $entity) => $query->where('entity_type', $entity))
            ->when($filters['user_id'], fn ($query, int $userId) => $query->where('user_id', $userId))
            ->when($filters['from'], fn ($query, string $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'], fn ($query, string $to) => $query->whereDate('created_at', '<=', $to))
            ->when($filters['q'], fn ($query, string $q) => $query->where(function ($scope) use ($q) {
                $scope->where('action', 'like', "%{$q}%")
                    ->orWhere('entity_type', 'like', "%{$q}%")
                    ->orWhere('ip_address', 'like', "%{$q}%")
                    ->orWhere('metadata', 'like', "%{$q}%");
            }))
            ->with('user:id,name,title')
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('admin.audit', [
            'logs' => $logs,
            'filters' => $filters,
            'actionLabels' => AuditActions::labels(),
            'users' => User::query()->with('role:id,slug')->orderBy('name')->get(),
        ]);
    }

    public function health(): View
    {
        return view('admin.health', [
            'checks' => $this->health->checks(),
        ]);
    }

    public function cms(): View
    {
        $this->cms->seed();

        return view('admin.cms', [
            'contents' => $this->cms->section('hero')
                ->concat($this->cms->section('sections'))
                ->concat($this->cms->section('stats')),
            'announcements' => Announcement::with('author:id,name')->orderByDesc('published_at')->get(),
            'faqs' => Faq::orderBy('sort_order')->orderBy('id')->get(),
            'statsLabels' => $this->cms->statsLabels(),
        ]);
    }

    public function config(): View
    {
        return view('admin.config', [
            'groups' => $this->settings->groups(),
        ]);
    }

    public function reports(): View
    {
        return view('admin.reports', array_merge(
            $this->analytics->overview(auth()->user()),
            ['reports' => $this->reports->available()]
        ));
    }

    public function reportExport(Request $request, string $report): StreamedResponse
    {
        abort_unless(array_key_exists($report, $this->reports->available()), 404, 'Unknown report.');

        return $this->reports->download($report, [
            'status' => $request->string('status')->toString() ?: null,
            'urgency' => $request->string('urgency')->toString() ?: null,
            'action' => $request->string('action')->toString() ?: null,
            'entity' => $request->string('entity')->toString() ?: null,
            'from' => $request->string('from')->toString() ?: null,
            'to' => $request->string('to')->toString() ?: null,
        ]);
    }

    public function backups(): View
    {
        return view('admin.backups', [
            'backups' => $this->backups->all(),
            'retention_days' => (int) $this->settings->get('backups.retention_days', 30),
        ]);
    }

    public function backupDownload(Backup $backup): StreamedResponse
    {
        $this->authorize('download', Backup::class);

        return $this->backups->download($backup);
    }

    public function alerts(): View
    {
        $platformAlerts = Notification::query()
            ->where('type', 'platform_alert')
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->take(50)
            ->get();

        $recentNotifications = Notification::query()
            ->with('user:id,name,title')
            ->orderByDesc('created_at')
            ->take(50)
            ->get();

        return view('admin.alerts', [
            'platformAlerts' => $platformAlerts,
            'recentNotifications' => $recentNotifications,
            'unreadTotal' => Notification::whereNull('read_at')->count(),
        ]);
    }

    private function monitoringOpen($query): void
    {
        $query->whereIn('status', ['sent', 'received', 'under_review', 'accepted']);
    }
}
