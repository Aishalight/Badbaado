@extends('layouts.console')

@php
    $me = auth()->user();
    $total = $counts['total'];
    $active = $counts['active'];
    $emergencies = $counts['emergencies'];
    $urgent = $counts['urgent'];
    $isCoordinator = $me->hasRole('referral_coordinator');
    $incoming = $counts['incoming'];
@endphp

@section('title', 'Dashboard · BADBAADO')
@section('heading', $isCoordinator ? 'Coordination desk' : 'Care team workspace')

@section('content')
<div class="dashboard-page mx-auto max-w-7xl space-y-8">
    <!-- Page header -->
    <x-page-header
        eyebrow="{{ $isCoordinator ? 'Coordination desk' : 'Care team workspace' }}"
        title="{{ $isCoordinator ? 'Keep every handover moving.' : 'Good to see you, ' . $me->name . '.' }}"
        description="{{ $isCoordinator ? 'Review incoming referrals and keep teams ready.' : 'Your latest referrals and care activity.' }}"
    >
        @can('create', \App\Models\Referral::class)
            <x-btn variant="accent" size="lg" href="{{ route('referrals.create') }}"><i data-lucide="plus" class="h-4 w-4 shrink-0"></i> New referral</x-btn>
        @endcan
    </x-page-header>

    <!-- Emergency alert -->
    @if ($emergencies > 0)
        <section class="rounded-[14px] border border-red-200 bg-red-50 shadow-card p-5 sm:p-6 flex items-start gap-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600">
                <i data-lucide="triangle-alert" class="h-5 w-5"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-red-800">{{ $emergencies }} emergency referral{{ $emergencies === 1 ? '' : 's' }}</p>
                <p class="mt-0.5 text-sm text-red-700">Need immediate attention in your workspace.</p>
            </div>
            <a href="{{ route('referrals.index') }}?urgency=critical" class="btn btn-primary btn-sm whitespace-nowrap">Review queue <i data-lucide="arrow-up-right" class="h-3.5 w-3.5 ml-1.5"></i></a>
        </section>
    @endif

    <!-- Metrics -->
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-metric-card label="Total referrals" value="{{ $total }}" note="Across your visible network" icon="chart-column" variant="blue" />
        <x-metric-card label="{{ $isCoordinator ? 'Incoming to review' : 'Active transfers' }}" value="{{ $isCoordinator ? $incoming : $active }}" note="Live now" icon="clock" variant="cyan" />
        <x-metric-card label="Emergencies" value="{{ $emergencies }}" note="Priority" icon="triangle-alert" variant="red" />
        <x-metric-card label="Critical / high" value="{{ $urgent }}" note="High acuity" icon="badge-check" variant="amber" />
    </section>

    <!-- Your queue -->
    <x-section-header
        eyebrow="Your queue"
        title="Recent referrals"
        description="Pick up where you left off. Referrals are shown most recent first."
    >
        <a href="{{ route('referrals.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600 hover:text-brand-700 transition">
            View all referrals <i data-lucide="arrow-up-right" class="h-3.5 w-3.5"></i>
        </a>
    </x-section-header>

    <section class="rounded-[16px] border border-slate-200/80 bg-white shadow-card overflow-hidden">
        @if ($referrals->isEmpty())
            <x-empty-state
                icon="inbox"
                title="No referrals yet"
                description="Create your first patient referral to start the handover."
                :action="route('referrals.create')"
                action-label="Create a referral"
            />
        @else
            <div class="divide-y divide-slate-50">
                @foreach ($referrals->take(10) as $referral)
                    <a href="{{ route('referrals.show', $referral) }}" class="block px-6 py-4 transition hover:bg-brand-50/40 group">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                            <span class="font-mono text-xs text-slate-400 group-hover:text-brand-600 transition">{{ $referral->referral_number }}</span>
                            @if ($referral->is_emergency)
                                <x-badge variant="critical" class="text-[10px]">Emergency</x-badge>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-800 truncate group-hover:text-brand-700 transition">{{ $referral->patient?->name ?? 'Unnamed patient' }}</p>
                            <div class="mt-1 flex flex-wrap items-center gap-x-1.5 gap-y-1 text-xs text-slate-500">
                                <span class="truncate">{{ $referral->referringHospital?->short_name ?? $referral->referringHospital?->name ?? '-' }}</span>
                                <i data-lucide="arrow-right" class="text-slate-300 h-3 w-3" aria-hidden="true"></i>
                                <span class="truncate">{{ $referral->receivingHospital?->short_name ?? $referral->receivingHospital?->name ?? '-' }}</span>
                                <span class="text-slate-300">·</span>
                                <span class="truncate">{{ $referral->department ?? 'General' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            {!! \App\Support\ReferralFormat::urgencyPill($referral->urgency?->value) !!}
                            {!! \App\Support\ReferralFormat::statusPill($referral->status?->value) !!}
                        </div>
                        <time class="text-xs tabular-nums text-slate-400">{{ $referral->created_at?->format('M j, H:i') }}</time>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
