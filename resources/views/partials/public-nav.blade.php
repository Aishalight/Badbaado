<header data-header class="public-header sticky top-0 z-50 border-b border-transparent">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-5 py-4 sm:px-8">
        <a href="{{ route('home') }}" class="group flex items-center gap-3" aria-label="BADBAADO home">
            <span class="relative">
                <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="BADBAADO logo" class="h-9 w-9 rounded-lg object-cover ring-1 ring-white/15">
                <span class="pointer-events-none absolute inset-0 rounded-lg ring-1 ring-accent-400/40 opacity-0 transition duration-500 group-hover:opacity-100"></span>
            </span>
            <span class="flex flex-col leading-none">
                <span class="text-[16px] font-extrabold tracking-tight text-brand-950">BADBAADO</span>
                <span class="mt-1 text-[9px] font-semibold uppercase tracking-[0.14em] text-slate-500">Connecting care</span>
            </span>
        </a>

        <nav class="hidden items-center gap-8 text-[13px] font-semibold md:flex" aria-label="Primary">
            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'is-current' : '' }}">Home</a>
            <a href="{{ route('how-it-works') }}" class="nav-link {{ request()->routeIs('how-it-works') ? 'is-current' : '' }}">How it works</a>
            <a href="{{ route('features') }}" class="nav-link {{ request()->routeIs('features') ? 'is-current' : '' }}">Features</a>
            <a href="{{ route('about') }}" class="nav-link {{ request()->routeIs('about') ? 'is-current' : '' }}">About</a>
        </nav>

        <div class="flex items-center gap-2.5">
            <button type="button" data-theme-toggle aria-label="Toggle light and dark theme" class="public-menu-btn inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/15 text-brand-950 transition hover:bg-white/10">
                <svg data-theme-icon="moon" viewBox="0 0 24 24" fill="none" class="h-4.5 w-4.5"><path d="M12 3a7.5 7.5 0 0 0 9 9 8.5 8.5 0 1 1-9-9Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                <svg data-theme-icon="sun" viewBox="0 0 24 24" fill="none" class="h-4.5 w-4.5"><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.6"/><path d="M12 2v2.5M12 19.5V22M2 12h2.5M19.5 12H22M4.9 4.9l1.8 1.8M17.3 17.3l1.8 1.8M4.9 19.1l1.8-1.8M17.3 6.7l1.8-1.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </button>
            <a href="{{ route('login') }}" class="btn-primary btn-sm hidden sm:inline-flex">Sign in</a>
            <button type="button" data-menu-toggle aria-expanded="false" aria-label="Toggle menu" class="public-menu-btn inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/15 text-brand-950 transition hover:bg-white/10 md:hidden">
                <svg viewBox="0 0 20 20" fill="none" class="h-4.5 w-4.5">
                    <path d="M3 6h14M3 10h14M3 14h14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    </div>

    <div data-mobile-menu class="m-menu border-t">
        <nav class="m-menu__panel px-4 pb-5 pt-2 text-sm font-semibold" aria-label="Mobile">
            <a href="{{ route('home') }}" class="rounded-lg px-3 py-2.5 text-slate-500 transition hover:bg-white/10 hover:text-white {{ request()->routeIs('home') ? 'bg-white/10 text-white' : '' }}">Home</a>
            <a href="{{ route('how-it-works') }}" class="rounded-lg px-3 py-2.5 text-slate-500 transition hover:bg-white/10 hover:text-white {{ request()->routeIs('how-it-works') ? 'bg-white/10 text-white' : '' }}">How it works</a>
            <a href="{{ route('features') }}" class="rounded-lg px-3 py-2.5 text-slate-500 transition hover:bg-white/10 hover:text-white {{ request()->routeIs('features') ? 'bg-white/10 text-white' : '' }}">Features</a>
            <a href="{{ route('about') }}" class="rounded-lg px-3 py-2.5 text-slate-500 transition hover:bg-white/10 hover:text-white {{ request()->routeIs('about') ? 'bg-white/10 text-white' : '' }}">About</a>
            <a href="{{ route('login') }}" class="btn-primary mt-4 justify-center">Sign in</a>
        </nav>
    </div>
</header>