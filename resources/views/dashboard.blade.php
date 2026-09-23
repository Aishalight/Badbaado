@extends('layouts.console')

@php
    $me = auth()->user();
    $total = $referrals->count();
    $active = $referrals->whereIn('status', ['sent', 'received', 'under_review', 'accepted', 'transfer_in_progress', 'arrived'])->count();
    $emergencies = $referrals->where('is_emergency', true)->count();
    $urgent = $referrals->whereIn('urgency', ['critical', 'emergent'])->count();
    $isCoordinator = $me->hasRole('referral_coordinator');
    $incoming = $referrals->where('receiving_hospital_id', $me->hospital_id)->whereIn('status', ['sent', 'received', 'under_review'])->count();
@endphp

@section('content')
<div class="dashboard-page mx-auto max-w-7xl space-y-8">
    <div class="dashboard-hero">
        <div><p class="section-eyebrow">{{ $isCoordinator ? 'Coordination desk' : 'Care team workspace' }}</p><h1 class="page-heading mt-2 text-3xl">{{ $isCoordinator ? 'Keep every handover moving.' : 'Good to see you, '.$me->name.'.' }}</h1><p class="mt-2 max-w-2xl text-sm text-slate-500">{{ $isCoordinator ? 'Prioritize incoming referrals and keep receiving teams ready.' : 'Your latest patient transfers, referrals, and care activity in one place.' }}</p></div>
@can('create', \App\Models\Referral::class)<a href="{{ route('referrals.create') }}" class="btn-accent"><span class="text-lg leading-none">+</span> New referral</a>@endcan
    </div>

    @if ($emergencies > 0)
        <div class="dashboard-alert"><span class="dashboard-alert__icon">!</span><div><strong>{{ $emergencies }} emergency referral{{ $emergencies === 1 ? '' : 's' }}</strong><p>Need{{ $emergencies === 1 ? '' : 's' }} attention in your workspace.</p></div><a href="{{ route('referrals.index') }}">Review queue <span aria-hidden="true">→</span></a></div>
    @endif

    <div class="dashboard-metrics grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="dashboard-stat dashboard-stat--blue"><div class="dashboard-stat__top"><span class="dashboard-stat__icon dashboard-stat__icon--blue">↗</span><span class="dashboard-stat__trend">All time</span></div><p class="dashboard-stat__value mt-5">{{ $total }}</p><p class="dashboard-stat__label">{{ $isCoordinator ? 'Network referrals' : 'Total referrals' }}</p></div>
        <div class="dashboard-stat dashboard-stat--cyan"><div class="dashboard-stat__top"><span class="dashboard-stat__icon dashboard-stat__icon--cyan">◌</span><span class="dashboard-stat__trend">Live now</span></div><p class="dashboard-stat__value mt-5">{{ $isCoordinator ? $incoming : $active }}</p><p class="dashboard-stat__label">{{ $isCoordinator ? 'Incoming to review' : 'Active transfers' }}</p></div>
        <div class="dashboard-stat dashboard-stat--red"><div class="dashboard-stat__top"><span class="dashboard-stat__icon dashboard-stat__icon--red">!</span><span class="dashboard-stat__trend">Priority</span></div><p class="dashboard-stat__value mt-5">{{ $emergencies }}</p><p class="dashboard-stat__label">Emergencies</p></div>
        <div class="dashboard-stat dashboard-stat--amber"><div class="dashboard-stat__top"><span class="dashboard-stat__icon dashboard-stat__icon--amber">◆</span><span class="dashboard-stat__trend">High acuity</span></div><p class="dashboard-stat__value mt-5">{{ $urgent }}</p><p class="dashboard-stat__label">Critical / high</p></div>
    </div>

    <div class="flex items-center justify-between"><div><p class="section-eyebrow">Your queue</p><h2 class="dashboard-section-title mt-1">Recent referrals</h2></div><a href="{{ route('referrals.index') }}" class="text-sm font-semibold text-accent-600 hover:text-accent-500">View all <span aria-hidden="true">→</span></a></div>
    <section class="dashboard-queue">
        @if ($referrals->isEmpty())
            <div class="dashboard-empty"><div class="dashboard-empty__icon">↗</div><h2>No referrals yet</h2><p>Create your first patient referral to start the handover.</p>@can('create', \App\Models\Referral::class)<a href="{{ route('referrals.create') }}" class="btn-accent mt-5">Create a referral</a>@endcan</div>
        @else
            <div class="dashboard-queue__head"><span>Patient and route</span><span>Status</span><span>Updated</span></div>
            <ul>@foreach ($referrals->take(8) as $referral)<li><a href="{{ route('referrals.show', $referral) }}" class="dashboard-queue__row"><div class="dashboard-patient"><span class="dashboard-patient__avatar">{{ strtoupper(substr($referral->patient?->name ?? 'P', 0, 1)) }}</span><span><strong>{{ $referral->patient?->name ?? 'Unnamed patient' }}</strong><small>{{ $referral->referringHospital?->short_name ?? '-' }} <b>→</b> {{ $referral->receivingHospital?->short_name ?? '-' }} · {{ $referral->department ?? 'General' }}</small></span></div><div class="hidden sm:block">{!! \App\Support\ReferralFormat::statusPill($referral->status->value) !!}</div><time>{{ $referral->created_at?->format('M j, H:i') }}</time></a></li>@endforeach</ul>
        @endif
    </section>
</div>
@endsection
