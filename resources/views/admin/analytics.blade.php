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

    <!-- Page header -->
    <div class="section-header mb-6">
        <div class="section-header__group">
            <p class="section-header__eyebrow">{{ $scope === 'system' ? 'System operations' : 'Hospital operations' }}</p>
            <h2 class="section-header__title">Care in motion, clearly seen.</h2>
        </div>
        <a href="{{ route('referrals.index') }}" class="btn-secondary px-5 py-2.5">Open referral workspace <i data-lucide="arrow-up-right" aria-hidden="true"></i></a>
    </div>

    <!-- Metrics -->
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card">
            <div class="stat-card__top">
                <span class="stat-card__icon"><i data-lucide="chart-no-axes-combined" aria-hidden="true"></i></span>
                <span class="stat-card__trend">Network</span>
            </div>
            <p class="stat-card__value">{{ $total_referrals }}</p>
            <p class="stat-card__label">Total referrals</p>
        </div>
        <div class="stat-card">
            <div class="stat-card__top">
                <span class="stat-card__icon stat-card__icon--red"><i data-lucide="triangle-alert" aria-hidden="true"></i></span>
                <span class="stat-card__trend">Emergency</span>
            </div>
            <p class="stat-card__value text-red-600">{{ $emergency_count }}</p>
            <p class="stat-card__label">Emergency cases</p>
        </div>
        <div class="stat-card">
            <div class="stat-card__top">
                <span class="stat-card__icon stat-card__icon--accent"><i data-lucide="clock-3" aria-hidden="true"></i></span>
                <span class="stat-card__trend">Active</span>
            </div>
            <p class="stat-card__value">{{ $active_transfers }}</p>
            <p class="stat-card__label">Active transfers</p>
        </div>
        <div class="stat-card">
            <div class="stat-card__top">
                <span class="stat-card__icon stat-card__icon--emerald"><i data-lucide="users" aria-hidden="true"></i></span>
                <span class="stat-card__trend">Team</span>
            </div>
            <p class="stat-card__value">{{ $users_count }}</p>
            <p class="stat-card__label">Care team users</p>
        </div>
    </div>

    <!-- Insight cards -->
    <div class="grid gap-5 xl:grid-cols-[1.55fr_0.95fr]">
        <section class="card p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <p class="section-eyebrow">Volume</p>
                    <h3 class="font-bold text-brand-950 text-lg">Referral activity</h3>
                </div>
                <span class="badge bg-brand-50 text-brand-700">Last 6 months</span>
            </div>
            <div class="analytics-chart analytics-chart--trend h-64"><canvas data-dashboard-trend data-chart='@json($trendChart)'></canvas></div>
        </section>

        <section class="card p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <p class="section-eyebrow">Lifecycle</p>
                    <h3 class="font-bold text-brand-950 text-lg">Referral status</h3>
                </div>
            </div>
            <div class="analytics-chart analytics-chart--status h-64"><canvas data-dashboard-status data-chart='@json($statusChart)'></canvas></div>
            <div class="analytics-legend mt-4 flex flex-wrap gap-4">
                @foreach ($statusChart as $index => $item)
                <span class="flex items-center gap-1.5 text-sm text-slate-600"><i style="--legend-color: {{ ['var(--m-accent)', 'var(--m-blue)', '#f59e0b', '#34d399', '#fb7185', '#94a3b8'][$index % 6] }}" class="h-2.5 w-2.5 rounded-full" style="background-color: var(--legend-color, #28b8cc)"></i>{{ $item['label'] }} <b class="text-slate-800">{{ $item['count'] }}</b></span>
                @endforeach
            </div>
        </section>
    </div>

    <!-- Live queue -->
    <section class="card overflow-hidden">
        <div class="px-6 pt-6 flex items-center justify-between">
            <div>
                <p class="section-eyebrow">Live queue</p>
                <h3 class="font-bold text-brand-950 text-lg">Recent referrals</h3>
            </div>
            <a href="{{ route('referrals.index') }}" class="text-sm font-semibold text-accent-600 hover:text-accent-500">View all</a>
        </div>
        @include('partials.referral-table', ['referrals' => $recent])
    </section>

</div>
@endsection