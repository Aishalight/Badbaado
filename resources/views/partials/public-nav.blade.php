<header class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/85 backdrop-blur-md">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-6 px-5 py-3.5 sm:px-6">
        <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="BADBAADO home">
            <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="BADBAADO logo" class="h-10 w-10 rounded-xl object-cover ring-1 ring-slate-200/70" >
            <span class="flex flex-col leading-none">
                <span class="text-[17px] font-extrabold tracking-tight text-brand-950">BADBAADO</span>
                <span class="mt-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-brand-500">Connecting Hospitals</span>
            </span>
        </a>

        <nav class="hidden items-center gap-7 text-sm font-medium text-slate-600 md:flex" aria-label="Primary">
            <a href="{{ route('home') }}" class="nav-link transition hover:text-brand-700 {{ request()->routeIs('home') ? 'text-brand-700 font-semibold' : '' }}">Home</a>
            <a href="{{ route('how-it-works') }}" class="nav-link transition hover:text-brand-700 {{ request()->routeIs('how-it-works') ? 'text-brand-700 font-semibold' : '' }}">How it works</a>
            <a href="{{ route('features') }}" class="nav-link transition hover:text-brand-700 {{ request()->routeIs('features') ? 'text-brand-700 font-semibold' : '' }}">Features</a>
            <a href="{{ route('about') }}" class="nav-link transition hover:text-brand-700 {{ request()->routeIs('about') ? 'text-brand-700 font-semibold' : '' }}">About</a>
        </nav>

        <div class="flex items-center gap-3">
            <a href="{{ route('login') }}" class="btn-primary btn-sm hidden sm:inline-flex">Sign in</a>
            <a href="{{ route('login') }}" class="inline-flex sm:hidden rounded-lg bg-brand-500 px-3 py-1.5 text-sm font-semibold text-white">Sign in</a>
        </div>
    </div>
</header>