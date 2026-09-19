@extends('layouts.console')

@php
    $statusChart = collect($status_counts)->map(fn ($count, $status) => [
        'label' => str_replace('_', ' ', ucfirst($status instanceof BackedEnum ? $status->value : $status)),
        'count' => $count,
    ])->values();
    $trendChart = collect($monthly)->map(fn (array $item) => [
        'label' => \Illuminate\Support\Carbon::createFromFormat('Y-m', $item['month'])->format('M'),
        'count' => $item['count'],
    ])->values();
@endphp

@section('title', 'Overview')
@section('heading', 'Network overview')
@section('content')
<div class="analytics-page mx-auto max-w-7xl space-y-8">
    <div class="flex flex-wrap items-end justify-between gap-5">
        <div>
            <p class="section-eyebrow">{{ $scope === 'system' ? 'System operations' : 'Hospital operations' }}</p>
            <h2 class="page-heading mt-2 text-3xl">Care in motion, clearly seen.</h2>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">A live view of referral demand, transfer activity, and the teams moving patients through your network.</p>
        </div>
        <a href="{{ route('referrals.index') }}" class="btn-secondary">Open referral workspace <span aria-hidden="true">↗</span></a>
    </div>

    <div class="analytics-metrics grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="analytics-stat analytics-stat--blue"><span class="analytics-stat__icon">↗</span><p class="analytics-stat__label">Total referrals</p><p class="analytics-stat__value">{{ $total_referrals }}</p><p class="analytics-stat__note">Across your visible network</p></div>
        <div class="analytics-stat analytics-stat--red"><span class="analytics-stat__icon">!</span><p class="analytics-stat__label">Emergency cases</p><p class="analytics-stat__value">{{ $emergency_count }}</p><p class="analytics-stat__note">Require immediate attention</p></div>
        <div class="analytics-stat analytics-stat--cyan"><span class="analytics-stat__icon">◌</span><p class="analytics-stat__label">Active transfers</p><p class="analytics-stat__value">{{ $active_transfers }}</p><p class="analytics-stat__note">Currently moving through care</p></div>
        <div class="analytics-stat analytics-stat--violet"><span class="analytics-stat__icon">+</span><p class="analytics-stat__label">Care team users</p><p class="analytics-stat__value">{{ $users_count }}</p><p class="analytics-stat__note">Assigned to this workspace</p></div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[1.55fr_0.95fr]">
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

    <section class="analytics-panel overflow-hidden"><div class="analytics-panel__header px-6 pt-6"><div><p class="section-eyebrow">Live queue</p><h3 class="analytics-panel__title">Recent referrals</h3></div><a href="{{ route('referrals.index') }}" class="text-sm font-semibold text-accent-600 hover:text-accent-500">View all</a></div>@include('partials.referral-table', ['referrals' => $recent])</section>
</div>
@endsection
