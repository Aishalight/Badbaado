@extends('layouts.console')

@php
    use App\Support\ReferralFormat;
    $statuses = ['draft', 'sent', 'received', 'under_review', 'accepted', 'transfer_in_progress', 'arrived', 'completed', 'rejected', 'cancelled'];
    $urgencies = ['critical', 'emergent', 'urgent', 'routine'];
@endphp

@section('title', 'Referral Network')
@section('heading', 'Referral Network')
@section('content')
<div class="space-y-6">
    <div>
        <p class="section-eyebrow">Network monitoring</p>
        <h2 class="page-heading mt-2 text-3xl">Every referral, every hospital, live.</h2>
        <p class="mt-2 max-w-2xl text-sm text-slate-500">A platform-wide view of referral movement across all connected hospitals. Filter and drill into any stage of the lifecycle.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="tile tile--accent"><p class="tile__label">Open referrals</p><p class="tile__value">{{ $overview['open_referrals'] }}</p></div>
        <div class="tile tile--blue"><p class="tile__label">Total referrals</p><p class="tile__value">{{ $overview['total_referrals'] }}</p></div>
        <div class="tile tile--amber"><p class="tile__label">Last 7 days</p><p class="tile__value">{{ $overview['references_last_7d'] }}</p></div>
        <div class="tile tile--red"><p class="tile__label">Emergency pre-alerts</p><p class="tile__value">{{ $overview['pre_alerts'] }}</p></div>
        <div class="tile"><p class="tile__label">Avg case age</p><p class="tile__value">{{ $overview['avg_age_hours'] }}<span class="tile__label text-base"> hrs</span></p></div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="card p-6 overflow-hidden">
            <div class="flex items-center justify-between"><div><p class="section-eyebrow">Lifecycle</p><h3 class="dashboard-section-title mt-1.5">Funnel</h3></div><span class="badge bg-brand-50 text-brand-700">{{ $overview['total_referrals'] }} total</span></div>
            @php($maxFunnel = max(1, collect($overview['funnel'])->max()))
            <div class="mt-5 space-y-4">
                @foreach ($funnelSteps as $step)
                    @php($count = $overview['funnel'][$step['key']] ?? 0)
                    <div>
                        <div class="flex items-center justify-between text-sm"><span class="soft">{{ $step['label'] }}</span><span class="word mono">{{ $count }}</span></div>
                        <div class="progress-track mt-2"><div class="progress-fill" style="width: {{ round($count / $maxFunnel * 100) }}%"></div></div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="card p-6 overflow-hidden">
            <div class="flex items-center justify-between"><div><p class="section-eyebrow">Demand</p><h3 class="dashboard-section-title mt-1.5">Urgency mix</h3></div><span class="badge bg-brand-50 text-brand-700">flagged cases</span></div>
            <div class="mt-5 space-y-4">
                @foreach ($overview['urgency'] as $urgency => $count)
                    <div>
                        <div class="flex items-center justify-between text-sm"><span class="soft">{{ \App\Enums\Urgency::tryFrom($urgency)?->label() ?? ucfirst($urgency) }}</span><span class="word mono">{{ $count }}</span></div>
                        <div class="progress-track mt-2"><div class="progress-fill" style="width: {{ round($count / max(1, collect($overview['urgency'])->max()) * 100) }}%"></div></div>
                    </div>
                @endforeach
                @if (empty($overview['urgency']))
                    <div class="empty-state"><p>No urgency-flagged referrals.</p></div>
                @endif
            </div>
        </section>
    </div>

    <section class="card p-6 overflow-hidden">
        <div class="flex items-center justify-between"><div><p class="section-eyebrow">Load</p><h3 class="dashboard-section-title mt-1.5">Per-hospital caseload</h3></div></div>
        <div class="mt-4 divide-y divide-slate-100">
            @forelse ($hospitalLoad as $hospital)
                <div class="flex flex-wrap items-center justify-between gap-4 py-3">
                    <div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-700">{{ $hospital->name }}</p><p class="faint text-xs">{{ $hospital->short_name }} · {{ $hospital->code }} · {{ $hospital->location }}</p></div>
                    <div class="flex flex-wrap items-center gap-3 text-xs">
                        <span class="soft">{{ $hospital->users_count }} users</span>
                        <span class="badge bg-accent-50 text-accent-700">{{ $hospital->open_sent }} outgoing open</span>
                        <span class="badge bg-brand-50 text-brand-700">{{ $hospital->open_received }} incoming open</span>
                        <span class="mono faint">{{ $hospital->outgoing_referrals_count + $hospital->incoming_referrals_count }} all-time</span>
                    </div>
                </div>
            @empty
                <div class="empty-state"><p>No hospitals connected yet.</p></div>
            @endforelse
        </div>
    </section>

    <section class="card overflow-hidden">
        <div class="px-6 pt-6 flex flex-wrap items-end justify-between gap-4"><div><p class="section-eyebrow">Registry</p><h3 class="dashboard-section-title mt-1.5">All referrals</h3></div>
            <form class="filter-bar" method="GET">
                <select class="input" name="status"><option value="">Any status</option>@foreach($statuses as $s)<option value="{{ $s }}" @selected($filters['status'] === $s)>{{ ReferralFormat::statusLabel($s) }}</option>@endforeach</select>
                <select class="input" name="urgency"><option value="">Any urgency</option>@foreach($urgencies as $u)<option value="{{ $u }}" @selected($filters['urgency'] === $u)>{{ ucfirst($u) }}</option>@endforeach</select>
                <select class="input" name="hospital_id"><option value="">Any hospital</option>@foreach($hospitals as $h)<option value="{{ $h->id }}" @selected($filters['hospital_id'] == $h->id)>{{ $h->name }}</option>@endforeach</select>
                <input class="input" name="q" value="{{ $filters['q'] }}" placeholder="Search number or patient">
                <button class="btn-secondary btn-sm" type="submit">Filter</button>
                @if($filters['status'] || $filters['urgency'] || $filters['hospital_id'] || $filters['q'])<a href="{{ route('admin.referrals') }}" class="btn-ghost btn-sm">Reset</a>@endif
            </form>
        </div>
        <div class="overflow-x-auto mt-4">
            <table class="data-table">
                <thead><tr><th>Referral</th><th>Status</th><th>Urgency</th><th>Patient</th><th>From</th><th>To</th><th>Created</th></tr></thead>
                <tbody>
                    @forelse ($referrals as $referral)
                        <tr>
                            <td><a href="{{ route('referrals.show', $referral) }}" class="word mono">{{ $referral->referral_number }}</a>@if($referral->is_emergency)<span class="badge bg-red-50 text-red-600 ml-2">Pre-alert</span>@endif</td>
                            <td>{!! ReferralFormat::statusPill($referral->status instanceof \BackedEnum ? $referral->status->value : $referral->status) !!}</td>
                            <td>{!! ReferralFormat::urgencyPill($referral->urgency instanceof \BackedEnum ? $referral->urgency->value : $referral->urgency) !!}</td>
                            <td class="soft">{{ $referral->patient?->name ?? '—' }}</td>
                            <td class="soft">{{ $referral->referringHospital?->short_name ?? '—' }}</td>
                            <td class="soft">{{ $referral->receivingHospital?->short_name ?? '—' }}</td>
                            <td class="faint">{{ $referral->created_at?->format('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty-state"><div class="empty-state__icon">∅</div><p>No referrals match these filters.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
            @include('partials.pager', ['pager' => $referrals])
        </div>
    </section>
</div>
@endsection