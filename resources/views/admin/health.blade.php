@extends('layouts.console')

@php
    $all = collect($checks)->flatMap(fn (array $items) => $items);
    $summary = ['ok' => $all->where('status', 'ok')->count(), 'warn' => $all->where('status', 'warn')->count(), 'fail' => $all->where('status', 'fail')->count()];
@endphp

@section('title', 'System Health')
@section('heading', 'System Health')
@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="section-eyebrow">Live probes</p>
            <h2 class="page-heading mt-2 text-3xl">System health & monitoring.</h2>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">Real probes of the runtime: database, cache, storage, scheduled backup coverage, and queue state.</p>
        </div>
        <div class="flex gap-2">
            <span class="badge bg-emerald-50 text-emerald-600">{{ $summary['ok'] }} ok</span>
            <span class="badge bg-amber-50 text-amber-700">{{ $summary['warn'] }} warn</span>
            @if($summary['fail'])<span class="badge bg-red-50 text-red-600">{{ $summary['fail'] }} failing</span>@endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        @foreach ($checks as $group => $items)
            <section class="card p-6">
                <div class="flex items-center justify-between">
                    <div><p class="section-eyebrow">Probe</p><h3 class="dashboard-section-title mt-1.5">{{ $group }}</h3></div>
                    <span class="health-dot {{ collect($items)->contains(fn ($c) => $c['status'] === 'fail') ? 'health-dot--fail' : (collect($items)->contains(fn ($c) => $c['status'] === 'warn') ? 'health-dot--warn' : 'health-dot--ok') }}"></span>
                </div>
                <div class="mt-4 divide-y divide-slate-100">
                    @foreach ($items as $check)
                        <div class="health-row -mx-3">
                            <span class="health-dot health-dot--{{ $check['status'] }}"></span>
                            <div class="min-w-0"><p class="text-sm font-semibold text-slate-700">{{ $check['label'] }}</p><p class="faint text-xs mt-0.5">{{ $check['detail'] }}</p></div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    <section class="card p-6">
        <div><p class="section-eyebrow">Continuity</p><h3 class="dashboard-section-title mt-1.5">Scheduled operations</h3></div>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div class="tile"><p class="tile__label">Automated backups</p><p class="tile__value mt-1 text-sm">dailyAt 02:00</p><p class="faint mt-1 text-xs">Prunes archives older than the retention window.</p></div>
            <div class="tile"><p class="tile__label">Queue worker</p><p class="tile__value mt-1 text-sm">{{ ucfirst(config('queue.default')) }}</p><p class="faint mt-1 text-xs">Run `queue:work` for async jobs in production.</p></div>
        </div>
    </section>
</div>
@endsection