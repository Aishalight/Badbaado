@extends('layouts.console')
@section('title', 'Command Center')
@section('heading', 'Command Center')
@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="section-eyebrow">Platform operations</p>
            <h2 class="page-heading mt-2 text-3xl">Command Center</h2>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">A single operational surface across every hospital, referral, and identity on BADBAADO. All figures are computed live from platform data.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.reports') }}" class="btn-secondary btn-sm">Reports</a>
            <a href="{{ route('admin.health') }}" class="btn-ghost btn-sm">System health</a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="tile tile--accent"><p class="tile__label">Open referrals</p><p class="tile__value">{{ $monitoring['open_referrals'] }}</p><p class="faint mt-2 text-xs">{{ $monitoring['referrals_today'] }} created today</p></div>
        <div class="tile tile--blue"><p class="tile__label">Total referrals</p><p class="tile__value">{{ $monitoring['total_referrals'] }}</p><p class="faint mt-2 text-xs">{{ $monitoring['completed_referrals'] }} completed</p></div>
        <div class="tile tile--red"><p class="tile__label">Emergency pre-alerts</p><p class="tile__value">{{ $monitoring['pre_alerts'] }}</p><p class="faint mt-2 text-xs">is_emergency flagged</p></div>
        <div class="tile tile--amber"><p class="tile__label">Failed logins · 24h</p><p class="tile__value">{{ $security['failed_logins_24h'] }}</p><p class="faint mt-2 text-xs">{{ $security['active_sessions'] }} active sessions</p></div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="card p-6 xl:col-span-2 overflow-hidden">
            <div class="flex items-center justify-between">
                <div><p class="section-eyebrow">Live caseload</p><h3 class="dashboard-section-title mt-1.5">Referral volume by status</h3></div>
                <a href="{{ route('admin.referrals') }}" class="text-sm font-semibold text-accent-600">Open network monitor →</a>
            </div>
            @php($maxFunnel = max(1, collect($monitoring['funnel'])->max()))
            <div class="mt-6 space-y-4">
                @foreach ($monitoring['funnel'] as $status => $count)
                    <div>
                        <div class="flex items-center justify-between text-sm"><span class="soft">{{ \App\Support\ReferralFormat::statusLabel($status) }}</span><span class="word mono">{{ $count }}</span></div>
                        <div class="progress-track mt-2"><div class="progress-fill" style="width: {{ round($count / $maxFunnel * 100) }}%"></div></div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="card p-6">
            <div><p class="section-eyebrow">Posture</p><h3 class="dashboard-section-title mt-1.5">System health</h3></div>
            <div class="mt-5 space-y-1">
                @foreach ($healthGroups as $group => $checks)
                    <div class="health-row -mx-3"><span class="health-dot {{ collect($checks)->contains(fn ($check) => $check['status'] === 'fail') ? 'health-dot--fail' : (collect($checks)->contains(fn ($check) => $check['status'] === 'warn') ? 'health-dot--warn' : 'health-dot--ok') }}"></span><span class="soft">{{ $group }}</span><span class="faint ml-auto text-xs">{{ collect($checks)->where('status', 'fail')->count() + collect($checks)->where('status', 'warn')->count() }} attention</span></div>
                @endforeach
                <div class="health-row -mx-3"><span class="health-dot health-dot--ok"></span><span class="soft">Summary</span><span class="faint ml-auto text-xs">{{ $healthSummary['ok'] }} ok · {{ $healthSummary['warn'] }} warn · {{ $healthSummary['fail'] }} fail</span></div>
            </div>
            <div class="mt-5"><a href="{{ route('admin.health') }}" class="btn-secondary btn-sm w-full">Open health monitor</a></div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="card p-6 overflow-hidden">
            <div class="flex items-center justify-between"><div><p class="section-eyebrow">Audit trail</p><h3 class="dashboard-section-title mt-1.5">Recent platform activity</h3></div><a href="{{ route('admin.audit') }}" class="text-sm font-semibold text-accent-600">Full log →</a></div>
            <div class="timeline mt-5">
                @forelse ($recentActivity as $log)
                    <div class="timeline-item {{ in_array($log->action, ['auth.login_failed', 'user_deleted', 'hospital_deleted'], true) ? 'timeline-item--danger' : '' }}">
                        <p class="text-sm"><span class="word">{{ $actionLabels[$log->action] ?? $log->action }}</span><span class="faint"> · {{ $log->user?->name ?? 'System' }}</span></p>
                        <p class="faint mt-0.5 text-xs">{{ $log->entity_type ? $log->entity_type.($log->entity_id ? ' #'.$log->entity_id : '') : 'Platform' }} · {{ $log->created_at?->diffForHumans() }}</p>
                    </div>
                @empty
                    <div class="empty-state"><div class="empty-state__icon">—</div><p>No platform activity recorded yet.</p></div>
                @endforelse
            </div>
        </section>

        <div class="space-y-6">
            <section class="card p-6 overflow-hidden">
                <div class="flex items-center justify-between"><div><p class="section-eyebrow">Network</p><h3 class="dashboard-section-title mt-1.5">Hospital caseload</h3></div><a href="{{ route('admin.hospitals') }}" class="text-sm font-semibold text-accent-600">Directory →</a></div>
                <div class="mt-4 divide-y divide-slate-100">
                    @forelse ($hospitalLoad as $hospital)
                        <div class="flex items-center justify-between gap-4 py-3"><div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-700">{{ $hospital->name }}</p><p class="faint text-xs">{{ $hospital->open_sent + $hospital->open_received }} open · {{ $hospital->users_count }} users</p></div><span class="shrink-0 text-sm font-bold text-accent-600">{{ $hospital->outgoing_referrals_count + $hospital->incoming_referrals_count }}</span></div>
                    @empty
                        <div class="empty-state"><p>No hospitals connected yet.</p></div>
                    @endforelse
                </div>
            </section>

            <section class="card p-6 overflow-hidden">
                <div class="flex items-center justify-between"><div><p class="section-eyebrow">Comms</p><h3 class="dashboard-section-title mt-1.5">Latest platform alerts</h3></div><a href="{{ route('admin.alerts') }}" class="text-sm font-semibold text-accent-600">Alerts →</a></div>
                <div class="mt-4 divide-y divide-slate-100">
                    @forelse ($recentAlerts as $alert)
                        <div class="py-3"><p class="text-sm font-semibold text-slate-700">{{ $alert->title }}</p><p class="faint mt-0.5 text-xs">{{ $alert->created_at?->diffForHumans() }} · sent to {{ $alert->user?->name }}</p></div>
                    @empty
                        <div class="empty-state"><p>No platform alerts sent yet.</p></div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
@endsection