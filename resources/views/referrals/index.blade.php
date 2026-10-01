@extends('layouts.console')

@php
    $statusFilter = request()->string('status')->toString();
    $urgencyFilter = request()->string('urgency')->toString();
    $statusOptions = \App\Support\ReferralFormat::statusOptions();
    $urgencyOptions = \App\Support\ReferralFormat::urgencyOptions();
@endphp

@section('heading', 'Referrals')
@section('content')
    <div class="mx-auto max-w-6xl">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <div class="section-eyebrow">Referrals</div>
                <h2 class="page-heading mt-1">All referrals</h2>
            </div>
            @can('create', \App\Models\Referral::class)
            <a href="{{ route('referrals.create') }}" class="btn btn-primary">
                <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                New referral
            </a>
            @endcan
        </div>

        <form method="GET" action="{{ route('referrals.index') }}" class="mt-5 flex flex-wrap items-end gap-3">
            <div>
                <label for="filter-status" class="mb-1 block text-xs font-semibold text-slate-600">Status</label>
                <select id="filter-status" name="status" class="input sm:w-52" onchange="this.form.submit()">
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter-urgency" class="mb-1 block text-xs font-semibold text-slate-600">Urgency</label>
                <select id="filter-urgency" name="urgency" class="input sm:w-44" onchange="this.form.submit()">
                    @foreach ($urgencyOptions as $value => $label)
                        <option value="{{ $value }}" @selected($urgencyFilter === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="sr-only">Apply filters</button>
            @if ($statusFilter || $urgencyFilter)
                <a href="{{ route('referrals.index') }}" class="btn btn-ghost btn-sm">Clear filters</a>
            @endif
        </form>

        <div class="card mt-4 overflow-hidden">
            @if ($referrals->isEmpty())
                <div class="px-6 py-12 text-center">
                    <p class="text-sm text-slate-500">No referrals match.</p>
                    @if ($statusFilter || $urgencyFilter)
                        <a href="{{ route('referrals.index') }}" class="btn btn-ghost mt-3">Clear filters</a>
                    @endif
                </div>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($referrals as $referral)
                        <li>
                            <a href="{{ route('referrals.show', $referral) }}" class="flex items-center gap-4 px-5 py-4 transition hover:bg-brand-50/40">
                                @if ($referral->receivingHospital)
                                    @include('partials.hospital-logo', ['hospital' => $referral->receivingHospital, 'size' => 'h-9 w-9'])
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-mono text-xs text-slate-400">{{ $referral->referral_number }}</span>
                                        <span class="truncate text-sm font-semibold text-slate-800">
                                            {{ $referral->patient?->name ?? 'Unnamed patient' }}
                                        </span>
                                        @if ($referral->is_emergency)
                                            <span class="badge bg-red-50 text-red-600">Emergency</span>
                                        @endif
                                    </div>
                                    <div class="mt-0.5 flex flex-wrap items-center gap-x-1.5 gap-y-1 text-xs text-slate-500">
                                        <span class="truncate">{{ $referral->referringHospital?->short_name ?? $referral->referringHospital?->name ?? '-' }}</span>
                                        <span class="text-slate-300">→</span>
                                        <span class="truncate">{{ $referral->receivingHospital?->short_name ?? $referral->receivingHospital?->name ?? '-' }}</span>
                                        <span class="text-slate-300">·</span>
                                        <span class="truncate">{{ $referral->department ?? 'General' }}</span>
                                        @if ($referral->intendedUser)
                                            <span class="text-slate-300">·</span>
                                            <span class="badge bg-accent-50 text-accent-700">for {{ $referral->intendedUser->name }}</span>
                                        @endif
                                        <span class="text-slate-300">·</span>
                                        <span class="truncate">{{ $referral->assignedTo?->name ?? $referral->referringUser?->name ?? '-' }}</span>
                                    </div>
                                </div>
                                <div class="hidden items-center gap-2 md:flex">
                                    {!! \App\Support\ReferralFormat::urgencyPill($referral->urgency?->value) !!}
                                    {!! \App\Support\ReferralFormat::statusPill($referral->status?->value) !!}
                                </div>
                                <div class="text-xs tabular-nums text-slate-400">{{ $referral->created_at?->format('M j, H:i') }}</div>
                            </a>
                        </li>
                    @endforeach
                </ul>
                @if ($referrals->hasPages())
                    <div class="border-t border-slate-100 px-5 py-3">
                        {{ $referrals->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection