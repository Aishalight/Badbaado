@extends('layouts.console')
@section('title', 'Audit Logs')
@section('heading', 'Audit Logs')
@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="section-eyebrow">Immutability</p>
            <h2 class="page-heading mt-2 text-3xl">Advanced audit log.</h2>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">Every auditable mutation and authentication event across the platform, with full filter, search, and CSV export. Logs are append-only.</p>
        </div>
        <a href="{{ route('admin.reports.export', 'audit') }}" class="btn-secondary btn-sm">Export CSV</a>
    </div>

    <section class="card p-6">
        <form class="filter-bar" method="GET">
            <select class="input" name="action"><option value="">Any action</option>@foreach($actionLabels as $action => $label)<option value="{{ $action }}" @selected($filters['action'] === $action)>{{ $label }}</option>@endforeach</select>
            <select class="input" name="user_id"><option value="">Any user</option>@foreach($users as $u)<option value="{{ $u->id }}" @selected($filters['user_id'] == $u->id)>{{ $u->name }}</option>@endforeach</select>
            <select class="input" name="entity"><option value="">Any entity</option><option value="Referral" @selected($filters['entity'] === 'Referral')>Referral</option><option value="User" @selected($filters['entity'] === 'User')>User</option><option value="Hospital" @selected($filters['entity'] === 'Hospital')>Hospital</option><option value="Backup" @selected($filters['entity'] === 'Backup')>Backup</option></select>
            <input class="input" name="q" value="{{ $filters['q'] }}" placeholder="Search action, IP, metadata">
            <input class="input" name="from" type="date" value="{{ $filters['from'] }}" aria-label="From">
            <input class="input" name="to" type="date" value="{{ $filters['to'] }}" aria-label="To">
            <button class="btn-secondary btn-sm" type="submit">Filter</button>
            @if($filters['action'] || $filters['user_id'] || $filters['entity'] || $filters['q'] || $filters['from'] || $filters['to'])<a href="{{ route('admin.audit') }}" class="btn-ghost btn-sm">Reset</a>@endif
        </form>
    </section>

    <section class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Action</th><th>User</th><th>Entity</th><th>IP address</th><th>Metadata</th><th>When</th></tr></thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="word">{{ $actionLabels[$log->action] ?? $log->action }}</td>
                            <td class="soft">{{ $log->user?->name ?? 'System' }}@if($log->user?->title)<span class="faint text-xs block">{{ $log->user->title }}</span>@endif</td>
                            <td class="soft">{{ $log->entity_type ? $log->entity_type.($log->entity_id ? ' <span class="mono">#'.$log->entity_id.'</span>' : '') : '—' }}</td>
                            <td class="mono faint">{{ $log->ip_address ?? '—' }}</td>
                            <td class="faint mono max-w-[16rem] truncate">{{ $log->metadata ? json_encode($log->metadata, JSON_UNESCAPED_SLASHES) : '—' }}</td>
                            <td class="faint">{{ $log->created_at?->format('d M Y, H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state"><div class="empty-state__icon">∅</div><p>No audit entries match these filters.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pager', ['pager' => $logs])
    </section>
</div>
@endsection