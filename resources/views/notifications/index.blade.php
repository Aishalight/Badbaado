@extends('layouts.console')
@section('title', 'Notifications')
@section('heading', 'Notifications')
@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="section-eyebrow">Your inbox</p>
            <h2 class="page-heading mt-2 text-3xl">Notifications</h2>
        </div>
        <button class="btn-secondary" data-mark-all-read type="button">Mark all read</button>
    </div>

    @if ($unreadAlarms > 0)
        <div class="mb-5 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
            <span class="mt-0.5 shrink-0 text-red-600">
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <p class="text-sm text-red-800">
                <strong>{{ $unreadAlarms }} unacknowledged {{ \Illuminate\Support\Str::plural('alarm', $unreadAlarms) }}</strong>
                need attention@if ($unreadCritical > 0), including {{ $unreadCritical }} critical@endif.
            </p>
        </div>
    @endif

    <div class="card divide-y divide-slate-100">
        @forelse ($notifications as $notification)
            @php($severity = $notification->severity ?? \App\Enums\NotificationSeverity::INFO)
            <div class="notification-row notification-row--{{ $severity->value }} flex gap-4 px-5 py-4 {{ $notification->isUnread() ? 'is-unread' : '' }}">
                <span class="severity-pill severity-{{ $severity->value }} mt-0.5 shrink-0">{{ $severity->label() }}</span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm {{ $notification->isUnread() ? 'font-semibold text-slate-800' : 'text-slate-600' }}">{{ $notification->title }}</p>
                    @if ($notification->body)
                        <p class="mt-0.5 text-xs text-slate-500">{{ $notification->body }}</p>
                    @endif
                    <p class="mt-1 text-xs text-slate-400">
                        {{ $notification->created_at?->diffForHumans() }}
                        @if ($notification->referral) · <a class="font-semibold text-brand-600" href="{{ route('referrals.show', $notification->referral) }}">{{ $notification->referral->referral_number }}</a>@endif
                    </p>
                </div>
                @if ($notification->isUnread())
                    <button class="self-center text-xs font-bold text-brand-600" data-read-notification="{{ $notification->id }}" type="button">Mark read</button>
                @endif
            </div>
        @empty
            <div class="p-16 text-center text-sm text-slate-500">You are all caught up.</div>
        @endforelse
    </div>
</div>
@endsection