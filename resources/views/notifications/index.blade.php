@extends('layouts.console')

@section('title', 'Notifications · BADBAADO')
@section('heading', 'Notifications')

@section('content')
<div class="mx-auto max-w-4xl">
    <x-page-header
        eyebrow="Your inbox"
        title="Notifications"
        description="Referral updates, pre-alerts, and messages that need your attention."
    >
        <button class="btn btn-secondary" data-mark-all-read type="button">Mark all read</button>
    </x-page-header>

    @if ($unreadAlarms > 0)
        <section class="rounded-[12px] border border-red-200 bg-red-50 p-4 mb-6 flex items-start gap-3">
            <div class="mt-0.5 shrink-0 text-red-600">
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-red-800">{{ $unreadAlarms }} unacknowledged {{ \Illuminate\Support\Str::plural('alarm', $unreadAlarms) }} need attention@if ($unreadCritical > 0), including {{ $unreadCritical }} critical@endif.</p>
            </div>
        </section>
    @endif

    <section class="rounded-[16px] border border-slate-200/80 bg-white shadow-card overflow-hidden">
        <div class="divide-y divide-slate-50">
            @forelse ($notifications as $notification)
                @php($severity = $notification->severity ?? \App\Enums\NotificationSeverity::INFO)
                <a href="{{ $notification->referral ? route('referrals.show', $notification->referral) : '#' }}" class="block px-6 py-4 transition hover:bg-brand-50/30 group {{ $notification->isUnread() ? 'bg-brand-25/40 border-l border-l-brand-300' : '' }}">
                    <div class="flex items-start gap-4">
                        <span class="mt-0.5 shrink-0">
                            <x-badge :variant="$severity->value === 'critical' ? 'critical' : ($severity->value === 'high' ? 'emergent' : ($severity->value === 'medium' ? 'urgent' : 'info'))" class="text-[10px]">{{ $severity->label() }}</x-badge>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm {{ $notification->isUnread() ? 'font-semibold text-brand-900' : 'text-slate-700' }}">{{ $notification->title }}</p>
                            @if ($notification->body)
                                <p class="mt-0.5 text-xs text-slate-500 line-clamp-2">{{ $notification->body }}</p>
                            @endif
                            <p class="mt-1 flex items-center gap-2 text-xs text-slate-400">
                                <span>{{ $notification->created_at?->diffForHumans() }}</span>
                                @if ($notification->referral) · <span class="font-semibold text-brand-600 truncate max-w-xs">{{ $notification->referral->referral_number }}</span>@endif
                                @if ($notification->isUnread()) <span class="ml-auto"><button class="text-xs font-bold text-brand-600 hover:text-brand-700" data-read-notification="{{ $notification->id }}" type="button">Mark read</button></span>@endif
                            </p>
                        </div>
                    </div>
                </a>
            @empty
                <div class="px-6 py-12 text-center">
                    <div class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-4">
                        <i data-lucide="bell" class="h-6 w-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-brand-950">You're all caught up.</h3>
                    <p class="mt-2 text-sm text-slate-500">No new notifications. Check back later for referral updates.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
