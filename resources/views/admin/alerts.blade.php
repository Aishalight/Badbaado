@extends('layouts.console')
@section('title', 'Notifications & Alerts')
@section('heading', 'Notifications & Alerts')
@section('content')
<div class="space-y-6">
    <div>
        <p class="section-eyebrow">Communications</p>
        <h2 class="page-heading mt-2 text-3xl">Notifications & alerts.</h2>
        <p class="mt-2 max-w-2xl text-sm text-slate-500">Broadcast platform alerts to a chosen audience and review what has been sent. Recipients see alerts as in-app notifications.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="tile tile--accent"><p class="tile__label">Platform alerts sent</p><p class="tile__value">{{ $platformAlerts->count() }}</p></div>
        <div class="tile tile--amber"><p class="tile__label">Unread notifications</p><p class="tile__value">{{ $unreadTotal }}</p></div>
        <div class="tile tile--blue"><p class="tile__label">Recent notifications</p><p class="tile__value">{{ $recentNotifications->count() }}</p></div>
    </div>

    <section class="card p-6">
        <div><p class="section-eyebrow">Broadcast</p><h3 class="dashboard-section-title mt-1.5">Send a platform alert</h3><p class="faint mt-1 text-xs">Creates an in-app notification for every active account matching the audience.</p></div>
        <form data-alert-form class="mt-5 grid gap-4 lg:grid-cols-[minmax(0,16rem)_minmax(0,16rem)_minmax(0,1fr)_auto]">
            <div><label class="label">Audience</label><select class="input mt-1 w-full" name="audience" required><option value="all">Everyone</option>@foreach(['hospital_admin', 'referral_coordinator', 'healthcare_worker'] as $audience)<option value="{{ $audience }}">{{ ucfirst(str_replace('_', ' ', $audience)) }}s</option>@endforeach</select></div>
            <div><label class="label">Title</label><input class="input mt-1 w-full" name="title" placeholder="e.g. Scheduled maintenance" required></div>
            <div><label class="label">Message</label><input class="input mt-1 w-full" name="body" placeholder="e.g. Platform offline tonight 02:00–03:00 for maintenance" required></div>
            <div class="flex items-end"><button class="btn-accent" type="submit">Send alert</button></div>
        </form>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="card p-6 overflow-hidden">
            <div><p class="section-eyebrow">History</p><h3 class="dashboard-section-title mt-1.5">Sent platform alerts</h3></div>
            <div class="mt-4 divide-y divide-slate-100">
                @forelse ($platformAlerts as $alert)
                    <div class="py-3"><p class="text-sm font-semibold text-slate-700">{{ $alert->body }}</p><p class="faint mt-0.5 text-xs">{{ $alert->user?->name }} · {{ $alert->created_at?->format('d M Y, H:i') }} @if($alert->read_at)<span class="ml-1">· read</span>@endif</p></div>
                @empty
                    <div class="empty-state"><p>No platform alerts sent yet.</p></div>
                @endforelse
            </div>
        </section>
        <section class="card p-6 overflow-hidden">
            <div><p class="section-eyebrow">Feed</p><h3 class="dashboard-section-title mt-1.5">Recent notifications</h3></div>
            <div class="mt-4 divide-y divide-slate-100">
                @forelse ($recentNotifications as $notification)
                    <div class="py-3"><p class="text-sm font-semibold text-slate-700">{{ $notification->title }}</p><p class="faint mt-0.5 text-xs">{{ $notification->body }} · {{ $notification->user?->name }} · {{ $notification->created_at?->diffForHumans() }}</p></div>
                @empty
                    <div class="empty-state"><p>No notifications recorded yet.</p></div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection