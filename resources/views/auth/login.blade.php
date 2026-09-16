@extends('layouts.app')

@section('title', 'Sign in — BADBAADO')

@section('body')
<div class="flex min-h-full">
    {{-- BRAND PANEL --}}
    <div class="brand-gradient relative hidden overflow-hidden lg:flex lg:w-1/2 lg:flex-col lg:justify-between lg:p-12">
        <div class="brand-glow pointer-events-none absolute -top-32 -right-24 h-[480px] w-[480px] opacity-60"></div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.18]" style="background-image:linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size:46px 46px; mask-image:radial-gradient(ellipse 80% 70% at 50% 100%, black 20%, transparent 75%); -webkit-mask-image:radial-gradient(ellipse 80% 70% at 50% 100%, black 20%, transparent 75%);"></div>

        <div class="relative flex items-center gap-3">
            <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="BADBAADO logo" class="h-11 w-11 rounded-xl ring-1 ring-white/25">
            <div>
                <div class="text-xl font-extrabold tracking-tight text-white">BADBAADO</div>
                <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-accent-300">Connecting Hospitals</div>
            </div>
        </div>

        <div class="relative max-w-md">
            <div class="flex items-center gap-3 text-accent-300">
                <span class="flow-line inline-block h-0.5 w-10 rounded-full bg-accent-400/60"></span>
                <span class="text-[11px] font-bold uppercase tracking-[0.2em]">Referral gateway</span>
            </div>
            <blockquote class="mt-5">
                <p class="text-[28px] font-extrabold leading-snug tracking-tight text-white">
                    Information arrives before the patient.
                </p>
                <footer class="mt-4 text-sm leading-relaxed text-brand-100">
                    The principle every referral is built around — so the receiving team is ready,
                    briefed and waiting.
                </footer>
            </blockquote>
        </div>

        <div class="relative flex items-center gap-3 text-[13px] text-brand-100">
            <span class="status-pill bg-white/10 text-accent-200">Live referrals</span>
            <span class="status-pill bg-white/10 text-white/80">Emergency pre-alerts</span>
            <span class="status-pill bg-white/10 text-white/80">Coordination</span>
        </div>
    </div>

    {{-- FORM PANEL --}}
    <div class="flex flex-1 items-center justify-center bg-[#F6F8FB] px-4 py-12">
        <div class="w-full max-w-md">
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="BADBAADO logo" class="h-10 w-10 rounded-xl ring-1 ring-slate-200">
                <div>
                    <span class="text-lg font-extrabold tracking-tight text-brand-950">BADBAADO</span>
                    <div class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">Connecting Hospitals</div>
                </div>
            </div>

            <h1 class="page-heading text-2xl">Welcome back</h1>
            <p class="mt-1 text-sm text-slate-500">Sign in to the referral coordination console.</p>

            <form id="login-form" class="mt-8 space-y-5" novalidate>
                <div>
                    <label for="email" class="label">Email address</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        required
                        autocomplete="email"
                        placeholder="you@hospital.bd"
                        class="input"
                    >
                </div>

                <div>
                    <label for="password" class="label">Password</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="input"
                    >
                </div>

                <div id="login-error" class="hidden rounded-lg border border-red-100 bg-red-50 px-3.5 py-2.5 text-sm text-red-700"></div>

                <button type="submit" id="login-submit" class="btn-primary w-full justify-center py-2.5">
                    Sign in
                </button>
            </form>

            <div class="mt-6">
                <div class="flex items-center gap-3">
                    <span class="h-px flex-1 bg-slate-200"></span>
                    <span class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Try a demo role</span>
                    <span class="h-px flex-1 bg-slate-200"></span>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <button type="button" data-demo-email="admin@badbaado.bd" class="demo-login btn btn-ghost justify-center">System admin</button>
                    <button type="button" data-demo-email="hospital.admin@alhilal.bd" class="demo-login btn btn-ghost justify-center">Hospital admin</button>
                    <button type="button" data-demo-email="coordinator@alhilal.bd" class="demo-login btn btn-ghost justify-center">Coordinator</button>
                    <button type="button" data-demo-email="dr.rahman@alhilal.bd" class="demo-login btn btn-ghost justify-center">Healthcare worker</button>
                </div>
                <p class="mt-3 text-center text-xs text-slate-400">
                    Each role opens its own console. All use the password
                    <code class="font-mono text-slate-500">password</code>.
                </p>
            </div>

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