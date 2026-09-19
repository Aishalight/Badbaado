@extends('layouts.app')

@section('title', 'Sign in: BADBAADO')

@section('body')
<div class="auth-shell flex min-h-screen">
    <div class="m-backdrop" aria-hidden="true">
        <span class="m-orb m-orb--1"></span>
        <span class="m-orb m-orb--2"></span>
        <span class="m-orb m-orb--3"></span>
        <span class="m-grid"></span>
        <span class="m-grain"></span>
    </div>

    {{-- BRAND PANEL --}}
    <div class="auth-brand-panel relative hidden overflow-hidden lg:flex lg:w-1/2 lg:flex-col lg:justify-between lg:p-14 xl:p-20">
        <div class="brand-glow pointer-events-none absolute -right-24 -top-32 h-[480px] w-[480px]"></div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.14]" style="background-image:linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size:46px 46px; mask-image:radial-gradient(ellipse 80% 70% at 50% 100%, black 20%, transparent 75%); -webkit-mask-image:radial-gradient(ellipse 80% 70% at 50% 100%, black 20%, transparent 75%);"></div>

        <div class="m-rise relative flex items-center gap-3" style="--d:.05s">
            <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="BADBAADO logo" class="h-11 w-11 rounded-xl ring-1 ring-white/25">
            <div>
                <div class="text-xl font-extrabold tracking-tight text-white">BADBAADO</div>
                <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-accent-300">Connecting Hospitals</div>
            </div>
        </div>

        <div class="m-rise relative max-w-lg" style="--d:.15s">
            <div class="mt-5">
                <span class="flex items-center gap-3 text-accent-300">
                    <span class="flow-line inline-block h-0.5 w-10 rounded-full bg-accent-400/60"></span>
                    <span class="text-[11px] font-bold uppercase tracking-[0.2em]">Referral gateway</span>
                </span>
            </div>
            <blockquote class="mt-5">
                <p class="m-gradient-text text-[30px] font-extrabold leading-snug tracking-tight text-white">
                    Information arrives before the patient.
                </p>
                <footer class="m-blur-in mt-4 text-sm leading-relaxed text-brand-100" style="--d:.3s">
                    The principle every referral is built around, so the receiving team is ready,
                    briefed and waiting.
                </footer>
            </blockquote>
        </div>

        <div class="relative space-y-5">
            <div class="m-rise auth-live-card" style="--d:.42s">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="auth-live-dot h-2 w-2 rounded-full bg-emerald-400"></span>
                        <span class="text-[11px] font-bold uppercase tracking-[0.16em] text-white/70">Live activity</span>
                    </div>
                    <span class="text-[11px] font-medium text-white/40">today</span>
                </div>
                <ul class="mt-3.5 space-y-3 text-[13px]">
                    <li class="flex items-center gap-3">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-accent-500/20 text-accent-300">
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M3 12h4l2.5-7 5 14L17 12h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div class="min-w-0">
                            <div class="truncate font-semibold text-white">STEMI pre-alert sent</div>
                            <div class="text-xs text-white/55">Al-Hilal Cath Lab · 2 min ago</div>
                        </div>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-emerald-400/15 text-emerald-300">
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="m5 12.5 4.5 4.5L19 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div class="min-w-0">
                            <div class="truncate font-semibold text-white">Referral ACC-1042 acknowledged</div>
                            <div class="text-xs text-white/55">Coordination team · 4 min ago</div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 text-[13px] text-brand-100" style="--d:.5s">
                <span class="status-pill bg-white/10 text-accent-200">Live referrals</span>
                <span class="status-pill bg-white/10 text-white/80">Emergency pre-alerts</span>
                <span class="status-pill bg-white/10 text-white/80">Coordination</span>
            </div>
        </div>
    </div>

    {{-- FORM PANEL --}}
    <div class="auth-form-panel relative flex flex-1 items-center justify-center px-5 py-12 sm:px-10 lg:w-1/2 lg:flex-none">
        <button type="button" data-theme-toggle aria-label="Toggle light and dark theme" class="public-menu-btn absolute right-4 top-4 z-10 inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/15 bg-white/5 text-brand-950 backdrop-blur transition hover:bg-white/10 sm:right-6 sm:top-6">
            <svg data-theme-icon="moon" viewBox="0 0 24 24" fill="none" class="h-4.5 w-4.5"><path d="M12 3a7.5 7.5 0 0 0 9 9 8.5 8.5 0 1 1-9-9Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
            <svg data-theme-icon="sun" viewBox="0 0 24 24" fill="none" class="h-4.5 w-4.5"><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.6"/><path d="M12 2v2.5M12 19.5V22M2 12h2.5M19.5 12H22M4.9 4.9l1.8 1.8M17.3 17.3l1.8 1.8M4.9 19.1l1.8-1.8M17.3 6.7l1.8-1.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
        </button>
        <div class="auth-form-wrap w-full max-w-[480px] xl:max-w-[520px]">
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="BADBAADO logo" class="h-10 w-10 rounded-xl ring-1 ring-white/15">
                <div>
                    <span class="text-lg font-extrabold tracking-tight text-brand-950">BADBAADO</span>
                    <div class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Connecting Hospitals</div>
                </div>
            </div>

            <div class="m-blur-in" style="--d:.1s">
                <div class="section-eyebrow">Secure console access</div>
                <h1 class="page-heading mt-3 text-4xl">Welcome back</h1>
                <p class="mt-3 text-[15px] leading-7 text-slate-500">Sign in to continue coordinating referrals across your care network.</p>
            </div>

            <form id="login-form" class="mt-8 space-y-5" novalidate>
                @csrf
                <div class="m-rise" style="--d:.25s">
                    <label for="email" class="label">Email address</label>
                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute left-4 top-1/2 z-10 -translate-y-1/2 text-slate-500">
                            <svg viewBox="0 0 24 24" fill="none" class="h-4.5 w-4.5"><rect x="3" y="5" width="18" height="14" rx="3" stroke="currentColor" stroke-width="1.6"/><path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            required
                            autocomplete="email"
                            autofocus
                            placeholder="you@hospital.bd"
                            class="input auth-input"
                        >
                    </div>
                </div>

                <div class="m-rise" style="--d:.36s">
                    <label for="password" class="label">Password</label>
                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute left-4 top-1/2 z-10 -translate-y-1/2 text-slate-500">
                            <svg viewBox="0 0 24 24" fill="none" class="h-4.5 w-4.5"><rect x="4.5" y="10.5" width="15" height="9.5" rx="2.5" stroke="currentColor" stroke-width="1.6"/><path d="M8 10.5V7a4 4 0 0 1 8 0v3.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                        </span>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="input auth-input"
                        >
                    </div>
                </div>

                <div class="m-rise flex items-center justify-between" style="--d:.44s">
                    <label for="remember" class="flex cursor-pointer items-center gap-2 text-sm text-slate-500">
                        <input type="checkbox" id="remember" name="remember" class="auth-check h-4 w-4 rounded">
                        Remember me
                    </label>
                    <a href="#" onclick="return false" class="auth-forgot text-sm font-medium">Forgot password?</a>
                </div>

                <div id="login-error" class="hidden rounded-xl border border-red-400/30 bg-red-500/10 px-3.5 py-2.5 text-sm text-red-200"></div>

                <div class="m-rise" style="--d:.5s">
                    <button type="submit" id="login-submit" class="btn-primary w-full justify-center py-3">
                        Sign in
                    </button>
                    <p class="mt-4 flex items-center justify-center gap-2 text-center text-xs text-slate-500">
                        <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5"><rect x="4.5" y="10.5" width="15" height="9.5" rx="2.5" stroke="currentColor" stroke-width="1.6"/><path d="M8 10.5V7a4 4 0 0 1 8 0v3.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                        Session is encrypted and scoped to your role.
                    </p>
                </div>
            </form>

            <p class="mt-8 text-center text-sm text-slate-500">
                <a href="{{ route('home') }}" class="font-medium text-brand-600 transition hover:text-brand-700">← Back to home</a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('head')
<script>
    window.BADBAADO = { loginOnly: true };
</script>
@endpush