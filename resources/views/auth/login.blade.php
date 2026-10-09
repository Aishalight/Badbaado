@extends('layouts.app')

@section('title', 'Sign in: BADBAADO')

@section('body')
    <div class="auth-shell auth-shell--split">
        <span class="auth-wash" aria-hidden="true"></span>

        <aside class="auth-brand-panel" aria-label="Badbaado platform summary">
            <span class="auth-brand-backdrop" aria-hidden="true">
                <span class="m-orb m-orb--1"></span>
                <span class="m-orb m-orb--2"></span>
                <span class="m-grain"></span>
            </span>

            <div class="auth-brand-inner">
                <a href="{{ route('home') }}" class="auth-brand-lockup">
                    <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="" aria-hidden="true"
                        class="auth-brand-logo">
                    <span class="auth-brand-word">BADBAADO</span>
                </a>

                <div class="auth-brand-copy">
                    <p class="auth-eyebrow">Inter-hospital referral network</p>
                    <h2 class="auth-motto">Information arrives before the patient.</h2>
                    <p class="auth-brand-subtitle">Secure emergency referrals. Faster coordination. Better-informed care.</p>
                </div>

                <div class="auth-network" aria-label="Referral coordination status">
                    <span class="auth-network__node">ED</span>
                    <span class="auth-network__rule"></span>
                    <span class="auth-network__signal"></span>
                    <span class="auth-network__node auth-network__node--active">BADBAADO</span>
                    <span class="auth-network__rule"></span>
                    <span class="auth-network__node">ICU</span>
                </div>
            </div>
        </aside>

        <main class="auth-form-panel relative">
            <button type="button" data-theme-toggle aria-label="Toggle light and dark theme"
                class="auth-theme-toggle absolute right-4 top-4 z-10 inline-flex h-9 w-9 items-center justify-center rounded-full border border-[color:var(--m-border-strong)] bg-[var(--m-panel)] text-[color:var(--m-muted)] shadow-sm transition hover:text-[color:var(--m-ink)] sm:right-6 sm:top-6">
                <svg data-theme-icon="moon" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                    <path d="M12 3a7.5 7.5 0 0 0 9 9 8.5 8.5 0 1 1-9-9Z" stroke="currentColor" stroke-width="1.6"
                        stroke-linejoin="round" />
                </svg>
                <svg data-theme-icon="sun" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                    <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.6" />
                    <path
                        d="M12 2v2.5M12 19.5V22M2 12h2.5M19.5 12H22M4.9 4.9l1.8 1.8M17.3 17.3l1.8 1.8M4.9 19.1l1.8-1.8M17.3 6.7l1.8-1.8"
                        stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                </svg>
            </button>

            <div class="auth-form-shell">
                <div class="auth-form-wrap w-full">
                    <a href="{{ route('home') }}" class="auth-utility-link mb-6 inline-flex items-center gap-2" aria-label="Back to BADBAADO home">
                        <span aria-hidden="true">&larr;</span> Back
                    </a>
                    <div class="auth-form-header">
                        <p class="auth-form-kicker">Welcome back</p>
                        <h1 class="text-[color:var(--m-ink)]">Sign in</h1>
                        <p>Sign in to continue to Badbaado</p>
                    </div>

                    <form id="login-form" class="space-y-5" novalidate>
                        @csrf

                        <div class="auth-field">
                            <label for="email" class="label">Work email or username</label>
                            <input type="email" name="email" id="email" required autocomplete="email" autofocus
                                placeholder="name@hospital.org" class="input mt-2">
                        </div>

                        <div class="auth-field auth-field--password">
                            <label for="password" class="label">Password</label>
                            <div class="auth-password-wrap">
                                <input type="password" name="password" id="password" required autocomplete="current-password"
                                    placeholder="Enter your password" class="input mt-2" aria-label="Password">
                                <button type="button" class="auth-password-toggle" aria-label="Show password" aria-pressed="false"
                                    title="Show password">
                                    <svg data-password-icon="show" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M2.5 12s3.2-5 9.5-5 9.5 5 9.5 5-3.2 5-9.5 5-9.5-5-9.5-5Z"
                                            stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                                        <circle cx="12" cy="12" r="2.25" stroke="currentColor" stroke-width="1.7" />
                                    </svg>
                                    <svg data-password-icon="hide" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="m3 3 18 18M10.6 6.25A10.8 10.8 0 0 1 12 6c6.3 0 9.5 6 9.5 6a16.7 16.7 0 0 1-3.4 3.75M6.2 6.9C3.8 8.35 2.5 12 2.5 12s3.2 6 9.5 6c1.4 0 2.65-.3 3.75-.75"
                                            stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="auth-meta-row">
                            <label for="remember"
                                class="flex cursor-pointer select-none items-center gap-2 text-sm text-[color:var(--m-muted)]">
                                <input type="checkbox" id="remember" name="remember" class="auth-check rounded">
                                <span>Remember me</span>
                            </label>

                            <a href="{{ route('password.request') }}" class="auth-utility-link">Forgot password?</a>
                        </div>

                        <div id="login-error"
                            class="hidden rounded-xl border border-red-400/30 bg-red-500/10 px-3 py-2 text-[13px] text-red-200">
                        </div>

                        <button type="submit" id="login-submit"
                            class="btn-primary mt-2 w-full justify-center py-3.5 text-base font-semibold shadow-[0_18px_30px_-20px_rgba(16,111,177,0.8)]">
                            Sign in
                        </button>

                        <p class="auth-security-indicator">Protected access • Authorized healthcare personnel</p>
                    </form>

                    <p class="auth-solo-foot mt-6">
                        New to BADBAADO?
                        <a href="{{ route('register') }}" class="auth-utility-link">Create a provider account</a>
                    </p>
                </div>
            </div>
        </main>
    </div>
@endsection

@push('head')
    <script>
        window.BADBAADO = { loginOnly: true };
    </script>
@endpush