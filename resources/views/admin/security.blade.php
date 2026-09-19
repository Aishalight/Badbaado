@extends('layouts.console')

@php
    use App\Support\AuditActions;
    $securityActions = collect($actionLabels)->filter(fn ($label, $action) => in_array($action, AuditActions::securitySensitive(), true) || str_starts_with($action, 'auth.'));
    $signins = collect($signinsChart);
    $failed = collect($failedChart);
    $signinsMax = max(1, $signins->max('count'));
    $failedMax = max(1, $failed->max('count'));
@endphp

@section('title', 'Security & SOC')
@section('heading', 'Security & SOC')
@section('content')
<div class="space-y-6">
    <div>
        <p class="section-eyebrow">Security operations</p>
        <h2 class="page-heading mt-2 text-3xl">Security, driven by real events.</h2>
        <p class="mt-2 max-w-2xl text-sm text-slate-500">Every figure on this page is computed from the live audit trail and authentication sessions — no simulated indicators.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="tile tile--green"><p class="tile__label">Successful logins</p><p class="tile__value">{{ $metrics['successful_logins'] }}</p><p class="faint mt-2 text-xs">all-time audit trail</p></div>
        <div class="tile tile--red"><p class="tile__label">Failed logins · 24h</p><p class="tile__value">{{ $metrics['failed_logins_24h'] }}</p><p class="faint mt-2 text-xs">{{ $metrics['failed_logins_7d'] }} over 7 days</p></div>
        <div class="tile tile--amber"><p class="tile__label">Sensitive actions · 7d</p><p class="tile__value">{{ $metrics['sensitive_actions_7d'] }}</p><p class="faint mt-2 text-xs">user, hospital, config mutations</p></div>
        <div class="tile tile--accent"><p class="tile__label">Active sessions</p><p class="tile__value">{{ $metrics['active_sessions'] }}</p><p class="faint mt-2 text-xs">{{ $metrics['privileged_users'] }} privileged accounts active</p></div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="card p-6 xl:col-span-2">
            <div class="flex items-center justify-between"><div><p class="section-eyebrow">Authentication volume · 14 days</p><h3 class="dashboard-section-title mt-1.5">Sign-ins vs failed attempts</h3></div></div>
            <div class="mini-bars mt-5">
                @foreach ($signins as $day)
                    <div class="mini-bar" style="height: {{ max(3, round($day['count'] / $signinsMax * 100)) }}%" title="{{ $day['date'] }} · {{ $day['count'] }} sign-ins"></div>
                @endforeach
            </div>
            <div class="mini-bars__labels">@foreach ($signins as $day)<span>{{ \Illuminate\Support\Carbon::parse($day['date'])->format('d M') }}</span>@endforeach</div>
            <div class="mini-bars mt-4">
                @foreach ($failed as $day)
                    <div class="mini-bar mini-bar--danger" style="height: {{ max(3, round($day['count'] / $failedMax * 100)) }}%" title="{{ $day['date'] }} · {{ $day['count'] }} failures"></div>
                @endforeach
            </div>
            <div class="mini-bars__labels">@foreach ($failed as $day)<span>{{ \Illuminate\Support\Carbon::parse($day['date'])->format('d M') }}</span>@endforeach</div>
            <div class="mt-4 flex items-center gap-4 text-xs"><span class="flex items-center gap-1.5 soft"><span class="h-2 w-2 rounded-full bg-accent"></span> Successful sign-ins</span><span class="flex items-center gap-1.5 soft"><span class="h-2 w-2 rounded-full" style="background:#fb7185"></span> Failed attempts</span></div>
        </section>

        <section class="card p-6">
            <div class="flex items-center justify-between"><div><p class="section-eyebrow">Posture</p><h3 class="dashboard-section-title mt-1.5">Security posture</h3></div><span class="badge {{ $posture['status'] === 'ok' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-700' }}">{{ $posture['status'] === 'ok' ? 'Healthy' : 'Review' }}</span></div>
            <div class="mt-4 divide-y divide-slate-100">
                @foreach ($posture['checks'] as $check)
                    <div class="health-row -mx-3"><span class="health-dot health-dot--{{ $check['status'] }}"></span><span class="soft">{{ $check['label'] }}</span></div>
                    <p class="-mx-3 ml-8 -mt-2 mb-2 text-xs"><span class="faint">{{ $check['detail'] }}</span></p>
                @endforeach
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="card p-6 xl:col-span-2">
            <div class="flex flex-wrap items-end justify-between gap-4"><div><p class="section-eyebrow">Event stream</p><h3 class="dashboard-section-title mt-1.5">Security events</h3></div>
                <form class="filter-bar" method="GET">
                    <select class="input" name="action"><option value="">Any action</option>@foreach($securityActions as $action => $label)<option value="{{ $action }}" @selected($filters['action'] === $action)>{{ $label }}</option>@endforeach</select>
                    <input class="input" name="from" type="date" value="{{ $filters['from'] }}" aria-label="From">
                    <input class="input" name="to" type="date" value="{{ $filters['to'] }}" aria-label="To">
                    <button class="btn-secondary btn-sm" type="submit">Filter</button>
                    @if($filters['action'] || $filters['from'] || $filters['to'])<a href="{{ route('admin.security') }}" class="btn-ghost btn-sm">Reset</a>@endif
                </form>
            </div>
            <div class="overflow-x-auto mt-4">
                <table class="data-table">
                    <thead><tr><th>Event</th><th>User</th><th>Context</th><th>When</th></tr></thead>
                    <tbody>
                        @forelse ($events as $event)
                            <tr>
                                <td class="word">{{ $actionLabels[$event->action] ?? $event->action }}</td>
                                <td class="soft">{{ $event->user?->name ?? 'System' }}</td>
                                <td class="faint mono">{{ $event->ip_address ?? '—' }} @if($event->entity_type)<span class="ml-1 normal-case">{{ $event->entity_type }}</span>@endif</td>
                                <td class="faint">{{ $event->created_at?->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><div class="empty-state"><div class="empty-state__icon">✓</div><p>No events match these filters.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="space-y-6">
            <section class="card p-6">
                <div><p class="section-eyebrow">Threat surface</p><h3 class="dashboard-section-title mt-1.5">Top failed-login sources</h3></div>
                <div class="mt-4 divide-y divide-slate-100">
                    @forelse ($topIps as $row)
                        <div class="flex items-center justify-between gap-3 py-2.5"><span class="mono soft">{{ $row->ip_address }}</span><span class="badge bg-red-50 text-red-600">{{ $row->attempts }} ×</span></div>
                    @empty
                        <div class="empty-state"><p>No failed logins on record.</p></div>
                    @endforelse
                </div>
            </section>
            <section class="card p-6">
                <div><p class="section-eyebrow">Access</p><h3 class="dashboard-section-title mt-1.5">Privileged accounts</h3></div>
                <div class="mt-4 divide-y divide-slate-100">
                    @forelse ($privilegedUsers as $user)
                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-700">{{ $user->name }}</p><p class="faint text-xs">{{ $user->hospital?->name ?? 'Platform' }}</p></div>
                            <span class="badge bg-brand-50 text-brand-700">{{ $user->role?->slug }}</span>
                        </div>
                    @empty
                        <div class="empty-state"><p>No privileged users.</p></div>
                    @endforelse
                </div>
                <div class="mt-4"><a href="{{ route('admin.users') }}" class="btn-secondary btn-sm w-full">Manage users & access</a></div>
            </section>
        </div>
    </div>
</div>
@endsection