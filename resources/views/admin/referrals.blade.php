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

    <!-- Page header -->
    <div class="section-header mb-6">
        <div class="section-header__group">
            <p class="section-header__eyebrow">Network monitoring</p>
            <h2 class="section-header__title">Every referral, every hospital, live.</h2>
        </div>
    </div>
    <p class="max-w-2xl text-sm text-slate-500">A platform-wide view of referral movement across all connected hospitals. Filter and drill into any stage of the lifecycle.</p>

    <!-- Metrics -->
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="stat-card">
            <div class="stat-card__top">
                <span class="stat-card__icon stat-card__icon--accent"><i data-lucide="share-2" aria-hidden="true"></i></span>
                <span class="stat-card__trend">Open</span>
            </div>
            <p class="stat-card__value">{{ $overview['open_referrals'] }}</p>
            <p class="stat-card__label">Open referrals</p>
        </div>
        <div class="stat-card">
            <div class="stat-card__top">
                <span class="stat-card__icon"><i data-lucide="layout-list" aria-hidden="true"></i></span>
                <span class="stat-card__trend">All time</span>
            </div>
            <p class="stat-card__value">{{ $overview['total_referrals'] }}</p>
            <p class="stat-card__label">Total referrals</p>
        </div>
        <div class="stat-card">
            <div class="stat-card__top">
                <span class="stat-card__icon stat-card__icon--amber"><i data-lucide="calendar" aria-hidden="true"></i></span>
                <span class="stat-card__trend">Last 7 days</span>
            </div>
            <p class="stat-card__value">{{ $overview['references_last_7d'] }}</p>
            <p class="stat-card__label">Last 7 days</p>
        </div>
        <div class="stat-card">
            <div class="stat-card__top">
                <span class="stat-card__icon stat-card__icon--red"><i data-lucide="triangle-alert" aria-hidden="true"></i></span>
                <span class="stat-card__trend">Emergency</span>
            </div>
            <p class="stat-card__value text-red-600">{{ $overview['pre_alerts'] }}</p>
            <p class="stat-card__label">Emergency pre-alerts</p>
        </div>
        <div class="stat-card">
            <div class="stat-card__top">
                <span class="stat-card__icon stat-card__icon--emerald"><i data-lucide="clock" aria-hidden="true"></i></span>
                <span class="stat-card__trend">Average</span>
            </div>
            <p class="stat-card__value">{{ $overview['avg_age_hours'] }}<span class="text-base text-slate-500"> hrs</span></p>
            <p class="stat-card__label">Avg case age</p>
        </div>
    </div>

    <!-- Two insight cards -->
    <div class="grid gap-6 xl:grid-cols-2">
        <section class="card p-6 overflow-hidden">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <p class="section-eyebrow">Lifecycle</p>
                    <h3 class="dashboard-section-title mt-1.5">Funnel</h3>
                </div>
                <span class="badge bg-brand-50 text-brand-700">{{ $overview['total_referrals'] }} total</span>
            </div>
            @php($maxFunnel = max(1, collect($overview['funnel'])->max()))
            <div class="mt-5 space-y-4">
                @foreach ($funnelSteps as $step)
                @php($count = $overview['funnel'][$step['key']] ?? 0)
                <div>
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span class="soft">{{ $step['label'] }}</span>
                        <span class="word mono font-bold text-brand-950">{{ $count }}</span>
                    </div>
                    <div class="progress-track mt-1 h-1.5"><div class="progress-fill h-full bg-brand-500 rounded-full" style="width: {{ round($count / $maxFunnel * 100) }}%"></div></div>
                </div>
                @endforeach
            </div>
        </section>

        <section class="card p-6 overflow-hidden">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <p class="section-eyebrow">Demand</p>
                    <h3 class="dashboard-section-title mt-1.5">Urgency mix</h3>
                </div>
                <span class="badge bg-brand-50 text-brand-700">Flagged cases</span>
            </div>
            <div class="mt-5 space-y-4">
                @foreach ($overview['urgency'] as $urgency => $count)
                <div>
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span class="soft">{{ ReferralFormat::urgencyLabel($urgency) }}</span>
                        <span class="word mono font-bold text-brand-950">{{ $count }}</span>
                    </div>
                    <div class="progress-track mt-1 h-1.5"><div class="progress-fill h-full bg-accent-500 rounded-full" style="width: {{ round($count / max(1, collect($overview['urgency'])->max()) * 100) }}%"></div></div>
                </div>
                @endforeach
                @if (empty($overview['urgency']))
                <div class="empty-state py-4"><p>No urgency-flagged referrals.</p></div>
                @endif
            </div>
        </section>
    </div>

    <!-- Per-hospital caseload -->
    <section class="card overflow-hidden">
        <div class="flex items-center justify-between px-6 pt-6">
            <div>
                <p class="section-eyebrow">Load</p>
                <h3 class="dashboard-section-title mt-1.5">Per-hospital caseload</h3>
            </div>
        </div>
        <div class="mt-4 divide-y divide-slate-100 px-6 pb-6">
            @forelse ($hospitalLoad as $hospital)
            <div class="flex flex-wrap items-center justify-between gap-4 py-3">
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-slate-700">{{ $hospital->name }}</p>
                    <p class="faint text-xs">{{ $hospital->short_name }} · {{ $hospital->code }} · {{ $hospital->location }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs">
                    <span class="soft">{{ $hospital->users_count }} users</span>
                    <span class="badge bg-accent-50 text-accent-700">{{ $hospital->open_sent }} outgoing</span>
                    <span class="badge bg-brand-50 text-brand-700">{{ $hospital->open_received }} incoming</span>
                    <span class="mono faint">{{ $hospital->outgoing_referrals_count + $hospital->incoming_referrals_count }} all-time</span>
                </div>
            </div>
            @empty
            <div class="empty-state py-8"><p>No hospitals connected yet.</p></div>
            @endforelse
        </div>
    </section>

    <!-- All referrals table -->
    <section class="card overflow-hidden">
        <div class="px-6 pt-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="section-eyebrow">Registry</p>
                <h3 class="dashboard-section-title mt-1.5">All referrals</h3>
            </div>
            <form class="filter-bar" method="GET">
                <label class="sr-only" for="admin-referral-status">Status</label>
                <select id="admin-referral-status" class="input" name="status">
                    <option value="">Any status</option>
                    @foreach($statuses as $s)
                    <option value="{{ $s }}" @selected($filters['status'] === $s)>{{ ReferralFormat::statusLabel($s) }}</option>
                    @endforeach
                </select>
                <label class="sr-only" for="admin-referral-urgency">Urgency</label>
                <select id="admin-referral-urgency" class="input" name="urgency">
                    <option value="">Any urgency</option>
                    @foreach($urgencies as $u)
                    <option value="{{ $u }}" @selected($filters['urgency'] === $u)>{{ ReferralFormat::urgencyLabel($u) }}</option>
                    @endforeach
                </select>
                <label class="sr-only" for="admin-referral-hospital">Hospital</label>
                <select id="admin-referral-hospital" class="input" name="hospital_id">
                    <option value="">Any hospital</option>
                    @foreach($hospitals as $h)
                    <option value="{{ $h->id }}" @selected($filters['hospital_id'] == $h->id)>{{ $h->name }}</option>
                    @endforeach
                </select>
                <label class="sr-only" for="admin-referral-search">Search</label>
                <input id="admin-referral-search" class="input" name="q" value="{{ $filters['q'] }}" placeholder="Search number or patient">
                <button class="btn-secondary btn-sm" type="submit">Filter</button>
                @if($filters['status'] || $filters['urgency'] || $filters['hospital_id'] || $filters['q'])
                <a href="{{ route('admin.referrals') }}" class="btn-ghost btn-sm">Reset</a>
                @endif
            </form>
        </div>
        <div class="overflow-x-auto mt-4 px-6 pb-6">
            <table class="data-table">
                <thead>
                <tr>
                    <th class="w-28">Referral</th>
                    <th class="w-24 text-center">Status</th>
                    <th class="w-24 text-center">Urgency</th>
                    <th>Patient</th>
                    <th class="hidden md:table-cell">From</th>
                    <th class="hidden md:table-cell">To</th>
                    <th class="text-right w-32">Created</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($referrals as $referral)
                <tr class="group">
                    <td>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('referrals.show', $referral) }}" class="font-mono text-xs text-slate-400 group-hover:text-brand-600">{{ $referral->referral_number }}</a>
                            @if($referral->is_emergency)
                            <span class="badge bg-red-50 text-red-600 text-[10px] px-2 py-1 ml-1">Pre-alert</span>
                            @endif
                        </div>
                    </td>
                    <td class="text-center">{!! ReferralFormat::statusPill($referral->status instanceof \BackedEnum ? $referral->status->value : $referral->status) !!}</td>
                    <td class="text-center">{!! ReferralFormat::urgencyPill($referral->urgency instanceof \BackedEnum ? $referral->urgency->value : $referral->urgency) !!}</td>
                    <td class="soft text-sm">{{ $referral->patient?->name ?? '—' }}</td>
                    <td class="soft text-sm hidden md:table-cell">{{ $referral->referringHospital?->short_name ?? '—' }}</td>
                    <td class="soft text-sm hidden md:table-cell">{{ $referral->receivingHospital?->short_name ?? '—' }}</td>
                    <td class="faint text-sm text-right tabular-nums">{{ $referral->created_at?->format('d M Y, H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state py-8"><div class="empty-state__icon">∅</div><p>No referrals match these filters.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
            @include('partials.pager', ['pager' => $referrals])
        </div>
    </section>

</div>
@endsection