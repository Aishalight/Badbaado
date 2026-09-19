@extends('layouts.console')

@php
    $statusChart = collect($status_counts)->map(fn ($count, $status) => [
        'label' => str_replace('_', ' ', ucfirst($status instanceof \BackedEnum ? $status->value : $status)),
        'count' => $count,
    ])->values();
    $trendChart = collect($monthly)->map(fn (array $item) => [
        'label' => \Illuminate\Support\Carbon::createFromFormat('Y-m', $item['month'])->format('M'),
        'count' => $item['count'],
    ])->values();
@endphp

@section('title', 'Reports & Analytics')
@section('heading', 'Reports & Analytics')
@section('content')
<div class="space-y-6">
    <div>
        <p class="section-eyebrow">Insights</p>
        <h2 class="page-heading mt-2 text-3xl">Reports & analytics.</h2>
        <p class="mt-2 max-w-2xl text-sm text-slate-500">Platform-wide metrics alongside real, downloadable CSV exports generated live from current data.</p>
    </div>

    <div class="analytics-metrics grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="analytics-stat analytics-stat--blue"><span class="analytics-stat__icon">↗</span><p class="analytics-stat__label">Total referrals</p><p class="analytics-stat__value">{{ $total_referrals }}</p><p class="analytics-stat__note">Across the whole network</p></div>
        <div class="analytics-stat analytics-stat--red"><span class="analytics-stat__icon">!</span><p class="analytics-stat__label">Emergency cases</p><p class="analytics-stat__value">{{ $emergency_count }}</p><p class="analytics-stat__note">Require immediate attention</p></div>
        <div class="analytics-stat analytics-stat--cyan"><span class="analytics-stat__icon">◌</span><p class="analytics-stat__label">Active transfers</p><p class="analytics-stat__value">{{ $active_transfers }}</p><p class="analytics-stat__note">Currently in motion</p></div>
        <div class="analytics-stat analytics-stat--violet"><span class="analytics-stat__icon">+</span><p class="analytics-stat__label">Care team users</p><p class="analytics-stat__value">{{ $users_count }}</p><p class="analytics-stat__note">Across all hospitals</p></div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.55fr_0.95fr]">
        <section class="analytics-panel">
            <div class="analytics-panel__header"><div><p class="section-eyebrow">Volume</p><h3 class="analytics-panel__title">Referral activity</h3></div><span class="analytics-panel__badge">Last 6 months</span></div>
            <div class="analytics-chart analytics-chart--trend"><canvas data-dashboard-trend data-chart='@json($trendChart)'></canvas></div>
        </section>
        <section class="analytics-panel">
            <div class="analytics-panel__header"><div><p class="section-eyebrow">Lifecycle</p><h3 class="analytics-panel__title">Referral status</h3></div></div>
            <div class="analytics-chart analytics-chart--status"><canvas data-dashboard-status data-chart='@json($statusChart)'></canvas></div>
            <div class="analytics-legend">@foreach ($statusChart as $index => $item)<span><i style="--legend-color: {{ ['var(--m-accent)', 'var(--m-blue)', '#f59e0b', '#34d399', '#fb7185', '#94a3b8'][$index % 6] }}"></i>{{ $item['label'] }} <b>{{ $item['count'] }}</b></span>@endforeach</div>
        </section>
    </div>

    <section class="card p-6">
        <div><p class="section-eyebrow">Exports</p><h3 class="dashboard-section-title mt-1.5">Download reports</h3><p class="faint mt-1 text-xs">Each export streams the latest data as UTF-8 CSV.</p></div>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ($reports as $slug => $label)
                <a href="{{ route('admin.reports.export', $slug) }}" class="tile tile--accent block no-underline hover:-translate-y-0.5">
                    <p class="tile__label">Export</p>
                    <p class="mt-1.5 text-sm font-bold text-slate-700">{{ $label }}</p>
                    <p class="faint mt-1 text-xs">badbaado-{{ $slug }} · CSV</p>
                </a>
            @endforeach
        </div>
    </section>
</div>
@endsection