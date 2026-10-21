@extends('layouts.console')

@php
    $statusFilter = request()->string('status')->toString();
    $urgencyFilter = request()->string('urgency')->toString();
    $statusOptions = \App\Support\ReferralFormat::statusOptions();
    $urgencyOptions = \App\Support\ReferralFormat::urgencyOptions();
@endphp

@section('title', 'Referrals · BADBAADO')
@section('heading', 'Referral workspace')

@section('content')
<div class="mx-auto max-w-7xl">
    <x-page-header
        eyebrow="Referrals"
        title="All referrals"
        description="Every referral across your network, most recent first. Filter by status or urgency to focus your work."
    >
        @can('create', \App\Models\Referral::class)
            <x-btn variant="accent" size="lg" href="{{ route('referrals.create') }}" class="whitespace-nowrap">
                <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4 mr-2"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                New referral
            </x-btn>
        @endcan
    </x-page-header>

    <!-- Filter bar -->
    <section class="rounded-[14px] border border-slate-200/80 bg-white shadow-card p-5 sm:p-6 mb-6">
        <form method="GET" action="{{ route('referrals.index') }}" class="grid gap-3 sm:grid-cols-4 items-end">
            <div>
                <label for="filter-status" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Status</label>
                <select id="filter-status" name="status" class="input w-full" onchange="this.form.submit()">
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter-urgency" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Urgency</label>
                <select id="filter-urgency" name="urgency" class="input w-full" onchange="this.form.submit()">
                    @foreach ($urgencyOptions as $value => $label)
                        <option value="{{ $value }}" @selected($urgencyFilter === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex h-[2.5rem]">
                <button type="submit" class="btn btn-primary">Apply filters</button>
            </div>
            @if ($statusFilter || $urgencyFilter)
                <div class="flex h-[2.5rem]">
                    <a href="{{ route('referrals.index') }}" class="btn btn-ghost">Clear filters</a>
                </div>
            @endif
        </form>
    </section>

    <!-- Referral list -->
    <section class="rounded-[16px] border border-slate-200/80 bg-white shadow-card overflow-hidden">
        @if ($referrals->isEmpty())
            <x-empty-state
                icon="list"
                title="No referrals match"
                description="There are no referrals matching your current filters."
                :action="$statusFilter || $urgencyFilter ? route('referrals.index') : null"
                action-label="Clear filters"
            />
        @else
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-50 bg-slate-25/50">
                        <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">Referral</th>
                        <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">Patient</th>
                        <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">Route</th>
                        <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">Urgency</th>
                        <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                        <th class="px-6 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400">Updated</th>
                        <th class="px-6 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach ($referrals as $referral)
                        <tr class="group hover:bg-brand-50/30 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="font-mono text-xs font-semibold text-slate-500">{{ $referral->referral_number }}</span>
                                    @if ($referral->is_emergency)
                                        <x-badge variant="critical" class="text-[10px]">Pre-alert</x-badge>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 min-w-0">
                                <p class="text-sm font-semibold text-slate-800 truncate">{{ $referral->patient?->name ?? 'Unnamed patient' }}</p>
                                <p class="text-xs text-slate-500">{{ $referral->department ?? 'General' }}</p>
                            </td>
                            <td class="px-6 py-4 min-w-0">
                                <div class="flex items-center gap-2">
                                    @include('partials.hospital-logo', ['hospital' => $referral->referringHospital, 'size' => 'h-6 w-6'])
                                    <span class="text-xs text-slate-400">→</span>
                                    @include('partials.hospital-logo', ['hospital' => $referral->receivingHospital, 'size' => 'h-6 w-6'])
                                </div>
                                <p class="mt-1 text-xs text-slate-500 truncate max-w-[200px]">{{ $referral->referringHospital?->short_name ?? '-' }} → {{ $referral->receivingHospital?->short_name ?? '-' }}</p>
                            </td>
                            <td class="px-6 py-4">{!! \App\Support\ReferralFormat::urgencyPill($referral->urgency?->value) !!}</td>
                            <td class="px-6 py-4">{!! \App\Support\ReferralFormat::statusPill($referral->status?->value) !!}</td>
                            <td class="px-6 py-4 text-right text-xs tabular-nums text-slate-500">{{ $referral->created_at?->format('M j, H:i') }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('referrals.show', $referral) }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700 transition">Open <i data-lucide="arrow-up-right" class="h-3.5 w-3.5 ml-1"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($referrals->hasPages())
                <div class="border-t border-slate-100 bg-slate-25/50 px-6 py-4">
                    {{ $referrals->links() }}
                </div>
            @endif
        @endif
    </section>
</div>
@endsection
