@extends('layouts.app')

@section('title', 'Sign in: BADBAADO')

@section('body')
<div class="auth-shell flex min-h-screen flex-col lg:flex-row">

    {{-- Brand panel: describes the product truthfully, no fabricated activity --}}
    <div class="auth-brand-panel relative flex w-full flex-col overflow-hidden px-6 py-8 sm:px-10 lg:w-[46%] lg:px-14 lg:py-12">
        <a href="{{ route('home') }}" class="relative flex items-center gap-2.5">
            <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="" class="h-8 w-8 rounded-lg ring-1 ring-white/20">
            <span class="text-sm font-bold tracking-tight text-white">BADBAADO</span>
        </a>

        <div class="relative my-auto max-w-lg py-10">
            <p class="auth-eyebrow">Inter-hospital referral network</p>

            <h2 class="auth-headline">
                {{ $tagline }}
            </h2>

            {{-- Lifecycle as a designed rail: connected nodes, not an arrow run --}}
            <div class="mt-11">
                <h3 class="auth-panel-label">Referral lifecycle</h3>
                <ol class="auth-rail mt-4">
                    @foreach ($stages as $stage)
                        <li class="auth-rail__node">
                            <span class="auth-rail__dot" aria-hidden="true"></span>
                            <span class="auth-rail__label">{{ $stage }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>

            {{-- Severity legend: what each tier means and what it demands --}}
            <div class="mt-11">
                <h3 class="auth-panel-label">Escalation tiers</h3>
                <ul class="mt-4 space-y-3">
                    @foreach ($severities as $severity)
                        <li class="auth-tier" data-tier="{{ $severity->color() }}">
                            <span class="auth-tier__dot" aria-hidden="true"></span>
                            <span class="auth-tier__text">
                                <span class="auth-tier__label">{{ $severity->label() }}</span>
                                <span class="auth-tier__guide">{{ $severity->guidance() }}</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="mt-11">
                <h3 class="auth-panel-label">Access levels</h3>
                <ul class="mt-4 grid grid-cols-2 gap-x-6 gap-y-2">
                    @foreach ($roles as $role)
                        <li class="flex items-center gap-2 text-[13px] text-white/65">
                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" class="h-3 w-3 shrink-0 text-white/30">
                                <path d="M4 10.5 8 14l8-8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ $role }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <p class="relative text-[12px] text-white/35">
            Every referral action is recorded in the audit trail.
        </p>
    </div>

    {{-- Form panel --}}
    <div class="auth-form-panel relative flex flex-1 items-center justify-center px-5 py-12 sm:px-10">
        <button type="button" data-theme-toggle aria-label="Toggle light and dark theme" class="absolute right-4 top-4 z-10 inline-flex h-9 w-9 items-center justify-center rounded-md border border-[color:var(--m-border-strong)] bg-[var(--m-panel)] text-[color:var(--m-muted)] transition hover:text-[color:var(--m-ink)] sm:right-6 sm:top-6">
            <svg data-theme-icon="moon" viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M12 3a7.5 7.5 0 0 0 9 9 8.5 8.5 0 1 1-9-9Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
            <svg data-theme-icon="sun" viewBox="0 0 24 24" fill="none" class="h-4 w-4"><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.6"/><path d="M12 2v2.5M12 19.5V22M2 12h2.5M19.5 12H22M4.9 4.9l1.8 1.8M17.3 17.3l1.8 1.8M4.9 19.1l1.8-1.8M17.3 6.7l1.8-1.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
        </button>

        <div class="auth-form-wrap w-full max-w-sm">
            <div class="flex flex-col items-center text-center">
                <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="" class="h-10 w-10 rounded-lg ring-1 ring-[color:var(--m-border-strong)] lg:hidden">
                <h1 class="mt-5 text-xl font-bold tracking-tight text-brand-950 lg:mt-0">Sign in to BADBAADO</h1>
                <p class="mt-2 text-sm text-slate-500">Use the account issued by your hospital administrator.</p>
            </div>

            <form id="login-form" class="mt-8 space-y-4" novalidate>
                @csrf

                <div>
                    <label for="email" class="label">Email address</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        required
                        autocomplete="email"
                        autofocus
                        placeholder="you@hospital.bd"
                        class="input mt-1.5"
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
                        class="input mt-1.5"
                    >
                </div>

                <label for="remember" class="flex cursor-pointer select-none items-center gap-2 text-[13px] text-slate-500">
                    <input type="checkbox" id="remember" name="remember" class="auth-check h-4 w-4 rounded">
                    Keep me signed in
                </label>

                <div id="login-error" class="hidden rounded-lg border border-red-400/30 bg-red-500/10 px-3 py-2 text-[13px] text-red-200"></div>

                <button type="submit" id="login-submit" class="btn-primary mt-2 w-full justify-center py-2.5">
                    Sign in
                </button>
            </form>

            <p class="mt-6 text-center text-[13px] text-slate-500">
                <a href="{{ route('home') }}" class="font-medium text-brand-600 transition hover:text-brand-700">Back to home</a>
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