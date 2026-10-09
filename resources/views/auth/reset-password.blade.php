@extends('layouts.app')

@section('title', 'Choose a new password: BADBAADO')

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
                        <h1 class="text-[color:var(--m-ink)]">Choose a new password</h1>
                        <p>Setting a new password signs out every other session on this account.</p>
                    </div>

                    @if (session('status'))
                        <div class="auth-notice" role="status">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}" class="space-y-5" novalidate>
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="auth-field">
                            <label for="email" class="label">Work email</label>
                            <input type="email" name="email" id="email" required autocomplete="email"
                                value="{{ old('email', $email) }}" class="input mt-2">
                            @error('email')
                                <p class="auth-field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="auth-field auth-field--password">
                            <label for="password" class="label">New password</label>
                            <div class="auth-password-wrap">
                                <input type="password" name="password" id="password" required
                                    autocomplete="new-password" placeholder="Enter a new password"
                                    class="input mt-2" aria-label="New password">
                                <button type="button" class="auth-password-toggle" aria-label="Show password"
                                    aria-pressed="false" title="Show password">
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
                            @error('password')
                                <p class="auth-field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="auth-field auth-field--password">
                            <label for="password_confirmation" class="label">Confirm new password</label>
                            <div class="auth-password-wrap">
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    required autocomplete="new-password" placeholder="Repeat the new password"
                                    class="input mt-2" aria-label="Confirm new password">
                            </div>
                        </div>

                        <button type="submit" class="btn-primary mt-2 w-full justify-center py-3.5 text-base font-semibold">
                            Update password
                        </button>
                    </form>

                    <p class="auth-solo-foot">
                        <a href="{{ route('login') }}" class="auth-utility-link">Back to sign in</a>
                    </p>
                </div>
            </div>
        </main>
    </div>
@endsection
