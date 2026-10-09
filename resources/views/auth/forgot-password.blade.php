@extends('layouts.app')

@section('title', 'Reset your password: BADBAADO')

@section('body')
    <div class="auth-shell auth-shell--solo">
        <span class="auth-wash" aria-hidden="true"></span>

        <main class="auth-form-panel relative">
            <a href="{{ route('home') }}" class="auth-solo-mark">
                <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="" aria-hidden="true"
                    class="auth-brand-logo">
                <span class="auth-brand-word">BADBAADO</span>
            </a>

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
                    <div class="auth-form-header">
                        <p class="auth-form-kicker">Account recovery</p>
                        <h1 class="text-[color:var(--m-ink)]">Reset your password</h1>
                        <p>Enter the work email on your BADBAADO account and we will send a secure reset
                            link to it.</p>
                    </div>

                    @if (session('status'))
                        <div class="auth-notice" role="status">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" class="space-y-5" novalidate>
                        @csrf

                        <div class="auth-field">
                            <label for="email" class="label">Work email</label>
                            <input type="email" name="email" id="email" required autocomplete="email" autofocus
                                value="{{ old('email') }}" placeholder="name@hospital.org" class="input mt-2">
                            @error('email')
                                <p class="auth-field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="btn-primary mt-2 w-full justify-center py-3.5 text-base font-semibold">
                            Email reset link
                        </button>

                        <p class="auth-security-indicator">The link expires in {{ config('auth.passwords.users.expire') }} minutes</p>
                    </form>

                    <p class="auth-solo-foot">
                        <a href="{{ route('login') }}" class="auth-utility-link">Back to sign in</a>
                    </p>
                </div>
            </div>
        </main>
    </div>
@endsection
