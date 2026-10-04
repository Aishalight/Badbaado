@php
    $me = auth()->user();
    $roleSlug = $me->role?->slug;

    $nav = [
        'healthcare_worker' => [
            ['group' => 'Workspace', 'items' => [
                ['route' => 'dashboard', 'label' => 'Dashboard', 'key' => 'dashboard', 'icon' => 'home'],
                ['route' => 'referrals.index', 'label' => 'Referrals', 'key' => 'referrals', 'icon' => 'list'],
                ['route' => 'referrals.create', 'label' => 'New referral', 'key' => 'new-referral', 'icon' => 'plus'],
                ['route' => 'notifications.index', 'label' => 'Notifications', 'key' => 'notifications', 'icon' => 'bell'],
            ]],
        ],
        'referral_coordinator' => [
            ['group' => 'Workspace', 'items' => [
                ['route' => 'dashboard', 'label' => 'Dashboard', 'key' => 'dashboard', 'icon' => 'home'],
                ['route' => 'referrals.index', 'label' => 'Referrals', 'key' => 'referrals', 'icon' => 'list'],
                ['route' => 'referrals.create', 'label' => 'New referral', 'key' => 'new-referral', 'icon' => 'plus'],
                ['route' => 'notifications.index', 'label' => 'Notifications', 'key' => 'notifications', 'icon' => 'bell'],
            ]],
        ],
        'hospital_admin' => [
            ['group' => 'Operations', 'items' => [
                ['route' => 'admin.analytics', 'label' => 'Overview', 'key' => 'analytics', 'icon' => 'chart'],
                ['route' => 'referrals.index', 'label' => 'Referrals', 'key' => 'referrals', 'icon' => 'list'],
            ]],
            ['group' => 'People', 'items' => [
                ['route' => 'admin.users', 'label' => 'Team Members', 'key' => 'users', 'icon' => 'users'],
            ]],
            ['group' => 'Communications', 'items' => [
                ['route' => 'notifications.index', 'label' => 'Notifications', 'key' => 'notifications', 'icon' => 'bell'],
            ]],
        ],
        'system_admin' => [
            ['group' => 'Operations', 'items' => [
                ['route' => 'admin.command', 'label' => 'Command Center', 'key' => 'admin.command', 'icon' => 'home'],
                ['route' => 'admin.referrals', 'label' => 'Referral Network', 'key' => 'admin.referrals', 'icon' => 'network'],
                ['route' => 'admin.hospitals', 'label' => 'Hospitals', 'key' => 'admin.hospitals', 'icon' => 'building'],
            ]],
            ['group' => 'People & Access', 'items' => [
                ['route' => 'admin.users', 'label' => 'Users & Access', 'key' => 'admin.users', 'icon' => 'users'],
            ]],
            ['group' => 'Security & Monitoring', 'items' => [
                ['route' => 'admin.security', 'label' => 'Security & SOC', 'key' => 'admin.security', 'icon' => 'shield'],
                ['route' => 'admin.audit', 'label' => 'Audit Logs', 'key' => 'admin.audit', 'icon' => 'clipboard'],
                ['route' => 'admin.health', 'label' => 'System Health', 'key' => 'admin.health', 'icon' => 'activity'],
            ]],
            ['group' => 'Content & Communications', 'items' => [
                ['route' => 'admin.alerts', 'label' => 'Notifications & Alerts', 'key' => 'admin.alerts', 'icon' => 'megaphone'],
                ['route' => 'admin.cms', 'label' => 'CMS & Public Content', 'key' => 'admin.cms', 'icon' => 'document'],
            ]],
            ['group' => 'System', 'items' => [
                ['route' => 'admin.reports', 'label' => 'Reports & Analytics', 'key' => 'admin.reports', 'icon' => 'chart'],
                ['route' => 'admin.config', 'label' => 'System Configuration', 'key' => 'admin.config', 'icon' => 'settings'],
                ['route' => 'admin.backups', 'label' => 'Backups & Data', 'key' => 'admin.backups', 'icon' => 'database'],
            ]],
        ],
    ][$roleSlug] ?? [];

    $currentRoute = request()->route()?->getName();
    $isActive = function (string $key, string $route) use ($currentRoute): bool {
        return match ($key) {
            'dashboard' => $currentRoute === 'dashboard',
            'referrals' => in_array($currentRoute, ['referrals.index', 'referrals.create', 'referrals.show'], true),
            'admin.command' => in_array($currentRoute, ['admin.command', 'dashboard'], true),
            default => $currentRoute === $route,
        };
    };
    $unread = $me->notifications()->whereNull('read_at')->count();
    $unreadAlarms = $me->notifications()
        ->whereNull('read_at')
        ->whereIn('severity', array_map(fn ($severity) => $severity->value, \App\Enums\NotificationSeverity::interrupting()))
        ->count();

    $primary = match ($roleSlug) {
        'healthcare_worker', 'referral_coordinator' => [
            ['route' => 'dashboard', 'label' => 'Home', 'key' => 'dashboard', 'icon' => 'home'],
            ['route' => 'referrals.index', 'label' => 'Referrals', 'key' => 'referrals', 'icon' => 'list'],
            ['route' => 'referrals.create', 'label' => 'New', 'key' => 'new-referral', 'icon' => 'plus'],
            ['route' => 'notifications.index', 'label' => 'Alerts', 'key' => 'notifications', 'icon' => 'bell'],
        ],
        'hospital_admin' => [
            ['route' => 'admin.analytics', 'label' => 'Overview', 'key' => 'analytics', 'icon' => 'chart'],
            ['route' => 'referrals.index', 'label' => 'Referrals', 'key' => 'referrals', 'icon' => 'list'],
            ['route' => 'admin.users', 'label' => 'Team', 'key' => 'users', 'icon' => 'users'],
            ['route' => 'notifications.index', 'label' => 'Alerts', 'key' => 'notifications', 'icon' => 'bell'],
        ],
        'system_admin' => [
            ['route' => 'admin.command', 'label' => 'Home', 'key' => 'admin.command', 'icon' => 'home'],
            ['route' => 'admin.referrals', 'label' => 'Network', 'key' => 'admin.referrals', 'icon' => 'network'],
            ['route' => 'admin.hospitals', 'label' => 'Hospitals', 'key' => 'admin.hospitals', 'icon' => 'building'],
            ['route' => 'admin.users', 'label' => 'Users', 'key' => 'admin.users', 'icon' => 'users'],
        ],
        default => [],
    };

    $icons = [
        'home' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0"><path d="M3 12l9-9 9 9"/><path d="M5 10v9a1 1 0 001 1h4v-5h4v5h4a1 1 0 001-1v-9"/></svg>',
        'list' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0"><path d="M8 6h13M8 12h13M8 18h13"/><circle cx="3" cy="6" r="1" fill="currentColor"/><circle cx="3" cy="12" r="1" fill="currentColor"/><circle cx="3" cy="18" r="1" fill="currentColor"/></svg>',
        'plus' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>',
        'bell' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>',
        'chart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>',
        'users' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>',
        'network' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0"><circle cx="12" cy="5" r="2"/><circle cx="5" cy="19" r="2"/><circle cx="19" cy="19" r="2"/><path d="M12 7v4m-5.5 7L10 12m8.5 8L14 12"/></svg>',
        'building' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0"><path d="M4 21V3h16v18"/><path d="M9 21V14h6v7"/><path d="M8 6h.01M12 6h.01M16 6h.01M8 10h.01M12 10h.01M16 10h.01"/></svg>',
        'shield' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
        'clipboard' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0"><path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M8 12h8M8 16h5"/></svg>',
        'activity' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
        'megaphone' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0"><path d="M3 11l18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 01-5.8-1.6"/></svg>',
        'document' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>',
        'database' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>',
        'more' => '<svg viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 shrink-0"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>',
        'settings' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" class="h-4 w-4 shrink-0"><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/></svg>',
        'logout' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
    ];
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#185A9D">
    <script>
        (function () {
            var key = 'badbaado-theme';
            var saved = null;
            try { saved = localStorage.getItem(key); } catch (e) {}
            var light = saved ? saved === 'light' : false;
            if (saved === null && window.matchMedia) { light = window.matchMedia('(prefers-color-scheme: light)').matches; }
            document.documentElement.classList.toggle('theme-light', light);
            var meta = document.querySelector('meta[name="theme-color"]');
            if (meta) { meta.setAttribute('content', light ? '#e9f1fa' : '#040B15'); }
        })();
    </script>
    <title>@yield('title', 'BADBAADO Console') · BADBAADO</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/badbaado-logo.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/console.js'])
    @stack('head')
</head>
<body class="console-shell min-h-full font-sans antialiased">
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <div class="flex min-h-screen">
        <aside class="console-sidebar fixed inset-y-0 left-0 z-40 hidden h-screen w-60 shrink-0 flex-col border-r border-slate-200/80 lg:flex">
            <a href="{{ route('dashboard') }}" class="flex h-14 shrink-0 items-center gap-2.5 border-b border-slate-100 px-4">
                <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="BADBAADO logo" class="h-7 w-7 rounded-lg ring-1 ring-slate-200">
                <span class="text-[13px] font-bold tracking-tight text-brand-950">BADBAADO</span>
            </a>
            <nav class="console-nav-scroll flex-1 space-y-0 overflow-y-auto px-2.5 py-3 text-sm font-medium">
                @foreach ($nav as $section)
                    <p class="console-nav-group px-2.5 pb-1 pt-4 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 first:pt-0">{{ $section['group'] }}</p>
                    @foreach ($section['items'] as $item)
                        @php($active = $isActive($item['key'], $item['route']))
                        <a href="{{ route($item['route']) }}" class="console-nav-link flex items-center gap-2.5 rounded-md px-2.5 py-[7px] text-[13px] {{ $active ? 'is-active' : '' }}">
                            <span class="text-slate-400 {{ $active ? 'text-accent-600' : '' }}">{!! $icons[$item['icon']] !!}</span>
                            <span class="flex-1 truncate">{{ $item['label'] }}</span>
                            @if ($item['key'] === 'notifications' && $unread > 0)<span class="nav-badge rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $unreadAlarms > 0 ? 'bg-red-500 text-white' : '' }}">{{ $unread }}</span>@endif
                        </a>
                    @endforeach
                @endforeach
            </nav>
            <div class="shrink-0 border-t border-slate-200/80 p-2">
                <div class="console-profile flex items-center gap-2 rounded-md px-2 py-1.5">
                    <a href="{{ route('settings.edit') }}" class="flex min-w-0 flex-1 items-center gap-2.5">
                        <span class="relative flex h-7 w-7 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-50 text-[11px] font-bold text-brand-700">@if ($me->avatar_path)<img src="{{ $me->avatar_url }}" alt="" class="h-full w-full object-cover">@else{{ strtoupper(substr($me->name, 0, 1)) }}@endif</span>
                        <span class="min-w-0">
                            <span class="block truncate text-[13px] font-semibold leading-tight text-slate-700">{{ $me->name }}</span>
                            <span class="block truncate text-[11px] leading-tight text-slate-400">{{ $me->role?->name ?? 'Platform' }}</span>
                        </span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" aria-label="Sign out" title="Sign out" class="console-icon-btn inline-flex h-7 w-7 items-center justify-center rounded-md text-slate-400 transition hover:text-slate-700">{!! $icons['logout'] !!}</button>
                    </form>
                </div>
            </div>
        </aside>
        <div class="flex min-w-0 flex-1 flex-col lg:pl-60">
            <header class="console-topbar flex items-center justify-between border-b border-slate-200/80 px-4 py-3 lg:hidden">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2"><img src="{{ asset('images/badbaado-logo.jpg') }}" alt="BADBAADO logo" class="h-8 w-8 rounded-lg ring-1 ring-slate-200"><span class="text-sm font-extrabold text-brand-950">BADBAADO</span></a>
                <div class="flex items-center gap-2">
                    <button type="button" data-theme-toggle aria-label="Toggle light and dark theme" class="console-icon-btn inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-white/15 text-brand-950 transition hover:bg-white/10">
                        <svg data-theme-icon="moon" viewBox="0 0 24 24" fill="none" class="h-4.5 w-4.5"><path d="M12 3a7.5 7.5 0 0 0 9 9 8.5 8.5 0 1 1-9-9Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                        <svg data-theme-icon="sun" viewBox="0 0 24 24" fill="none" class="h-4.5 w-4.5"><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.6"/><path d="M12 2v2.5M12 19.5V22M2 12h2.5M19.5 12H22M4.9 4.9l1.8 1.8M17.3 17.3l1.8 1.8M4.9 19.1l1.8-1.8M17.3 6.7l1.8-1.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                    </button>
                    <a href="{{ route('notifications.index') }}" class="relative inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-white/15 text-brand-950 transition hover:bg-white/10" aria-label="Notifications">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4.5 w-4.5"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M13.73 21a2 2 0 01-3.46 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                        @if ($unread > 0)<span class="nav-dot absolute right-2 top-1.5 h-1.5 w-1.5 rounded-full bg-red-500"></span>@endif
                    </a>
                </div>
            </header>
            <header class="console-topbar hidden h-20 items-center justify-between border-b border-slate-200/80 px-8 lg:-ml-[16px] lg:-mt-px lg:flex">
                <div><p class="text-[11px] font-bold uppercase tracking-[0.16em] text-accent-600">{{ $me->hospital?->name ?? 'National network' }}</p><h1 class="mt-1 text-lg font-extrabold tracking-tight text-brand-950">@yield('heading', 'Care console')</h1></div>
                <div class="flex items-center gap-2"><button type="button" data-theme-toggle aria-label="Toggle light and dark theme" class="console-icon-btn inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/15 text-brand-950 transition hover:bg-white/10"><svg data-theme-icon="moon" viewBox="0 0 24 24" fill="none" class="h-4.5 w-4.5"><path d="M12 3a7.5 7.5 0 0 0 9 9 8.5 8.5 0 1 1-9-9Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg><svg data-theme-icon="sun" viewBox="0 0 24 24" fill="none" class="h-4.5 w-4.5"><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.6"/><path d="M12 2v2.5M12 19.5V22M2 12h2.5M19.5 12H22M4.9 4.9l1.8 1.8M17.3 17.3l1.8 1.8M4.9 19.1l1.8-1.8M17.3 6.7l1.8-1.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></button><a href="{{ route('settings.edit') }}" class="btn-ghost">Settings</a><a href="{{ route('notifications.index') }}" class="btn-ghost">Notifications @if($unread > 0)<span class="rounded-full px-1.5 text-[11px] {{ $unreadAlarms > 0 ? 'bg-red-500 text-white' : 'bg-accent-100 text-accent-700' }}">{{ $unread }}</span>@endif</a>@can('create', \App\Models\Referral::class)<a href="{{ route('referrals.create') }}" class="btn-accent">New referral</a>@endcan</div>
            </header>
            <main id="main-content" tabindex="-1" class="min-w-0 flex-1 px-4 pt-6 pb-24 sm:px-6 lg:px-10 lg:pt-8 lg:pb-8"><div class="mx-auto w-full max-w-[1400px]">@yield('content')</div></main>
        </div>
    </div>
    @if (count($primary) > 0)
    <nav class="console-bottomnav fixed inset-x-0 bottom-0 z-40 border-t lg:hidden" aria-label="Mobile navigation" style="padding-bottom:env(safe-area-inset-bottom,0px)">
        <div class="flex items-stretch">
            @foreach ($primary as $item)
                @php($active = $isActive($item['key'], $item['route']))
                <a href="{{ route($item['route']) }}" class="console-bottomnav-item relative flex flex-1 flex-col items-center justify-center gap-1 py-2.5 text-[10px] font-semibold transition {{ $active ? 'is-active text-brand-600' : 'text-slate-400' }}">
                    @if ($item['key'] === 'notifications' && $unread > 0)<span class="nav-dot absolute right-2.5 top-1.5 h-1.5 w-1.5 rounded-full bg-red-500"></span>@endif
                    {!! $icons[$item['icon']] !!}
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
            <button type="button" data-nav-more class="console-bottomnav-item flex flex-1 flex-col items-center justify-center gap-1 py-2.5 text-[10px] font-semibold text-slate-400 transition">
                {!! $icons['more'] !!}
                <span>More</span>
            </button>
        </div>
    </nav>

    <div data-nav-sheet class="console-nav-sheet fixed inset-0 z-50 hidden lg:hidden" aria-hidden="true">
        <div data-nav-backdrop class="console-nav-backdrop absolute inset-0"></div>
        <div data-nav-panel class="console-nav-panel absolute inset-x-0 bottom-0 max-h-[85vh] overflow-y-auto rounded-t-3xl border-t border-slate-200/80">
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200/80 bg-white px-5 py-4">
                <h2 class="text-base font-extrabold tracking-tight text-brand-950">Menu</h2>
                <button type="button" data-nav-close aria-label="Close menu" class="inline-flex h-8 w-8 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="h-5 w-5" aria-hidden="true" focusable="false"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <nav class="px-4 py-3">
                @foreach ($nav as $section)
                    <p class="mb-1 px-2 pb-1 pt-4 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 first:pt-0">{{ $section['group'] }}</p>
                    @foreach ($section['items'] as $item)
                        @php($active = $isActive($item['key'], $item['route']))
                        <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-md px-2.5 py-2 text-[13px] font-semibold {{ $active ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-100' }}">
                            <span class="text-slate-400 {{ $active ? 'text-accent-600' : '' }}">{!! $icons[$item['icon']] !!}</span>
                            <span class="flex-1 truncate">{{ $item['label'] }}</span>
                            @if ($item['key'] === 'notifications' && $unread > 0)<span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $unreadAlarms > 0 ? 'bg-red-500 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $unread }}</span>@endif
                        </a>
                    @endforeach
                @endforeach
                <div class="mt-4 border-t border-slate-200/80 pt-3">
                    <a href="{{ route('settings.edit') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ $currentRoute === 'settings.edit' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-100' }}">
                        {!! $icons['settings'] !!}
                        <span>Settings</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                            {!! $icons['logout'] !!}
                            <span>Sign out</span>
                        </button>
                    </form>
                </div>
            </nav>
        </div>
    </div>

    <script>
        (function () {
            const sheet = document.querySelector('[data-nav-sheet]');
            if (!sheet) return;
            const close = () => { sheet.classList.add('hidden'); sheet.setAttribute('aria-hidden', 'true'); document.body.style.overflow = ''; };
            document.querySelector('[data-nav-more]')?.addEventListener('click', () => { sheet.classList.remove('hidden'); sheet.setAttribute('aria-hidden', 'false'); document.body.style.overflow = 'hidden'; });
            document.querySelector('[data-nav-close]')?.addEventListener('click', close);
            sheet.querySelector('[data-nav-backdrop]')?.addEventListener('click', close);
        })();
    </script>
    @endif

    @stack('scripts')
</body>
</html>