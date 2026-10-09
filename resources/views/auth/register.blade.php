@extends('layouts.app')

@section('title', 'Register: BADBAADO')

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
                        <p class="auth-form-kicker">Provider onboarding</p>
                        <h1 class="text-[color:var(--m-ink)]">Create your BADBAADO account</h1>
                        <p>Every account is reviewed by a system administrator before it can
                            reach the console.</p>
                    </div>

                    <form method="POST" action="{{ route('register.store') }}" class="space-y-5" data-registration-form novalidate>
                        @csrf

                        <fieldset class="space-y-3">
                            <legend class="label">I am registering as</legend>
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach ($types as $type)
                                    <label class="auth-choice">
                                        <input type="radio" name="type" value="{{ $type->value }}"
                                            @checked(old('type', $type === $types[0]))>
                                        <span>
                                            <span class="block text-sm font-semibold text-[color:var(--m-ink)]">
                                                {{ $type->label() }}
                                            </span>
                                            <span class="block text-xs text-[color:var(--m-muted)]">
                                                @if ($type === \App\Enums\ProviderApplicationType::HOSPITAL)
                                                    The first approved user becomes the hospital administrator.
                                                @else
                                                    A verified independent practitioner with a private practice.
                                                @endif
                                            </span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('type')
                                <p class="auth-field-error">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <div class="auth-field">
                            <label for="name" class="label">Full name</label>
                            <input type="text" name="name" id="name" required autocomplete="name" autofocus
                                value="{{ old('name') }}" class="input mt-2">
                            @error('name')
                                <p class="auth-field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="auth-field">
                            <label for="email" class="label">Work email</label>
                            <input type="email" name="email" id="email" required autocomplete="email"
                                value="{{ old('email') }}" class="input mt-2">
                            @error('email')
                                <p class="auth-field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="auth-field">
                                <label for="phone" class="label">Phone <span
                                        class="text-[color:var(--m-muted)]">(optional)</span></label>
                                <input type="text" name="phone" id="phone" autocomplete="tel"
                                    value="{{ old('phone') }}" class="input mt-2">
                                @error('phone')
                                    <p class="auth-field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="auth-field">
                                <label for="title" class="label">Professional title <span
                                        class="text-[color:var(--m-muted)]">(optional)</span></label>
                                <input type="text" name="title" id="title" placeholder="Senior Registrar"
                                    value="{{ old('title') }}" class="input mt-2">
                                @error('title')
                                    <p class="auth-field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2" data-registration-doctor>
                            <div class="auth-field">
                                <label for="specialty_id" class="label">Specialty</label>
                                <select name="specialty_id" id="specialty_id" class="input mt-2">
                                    <option value="">Choose a specialty</option>
                                    @foreach ($specialties as $specialty)
                                        <option value="{{ $specialty->id }}"
                                            @selected((int) old('specialty_id') === $specialty->id)>
                                            {{ $specialty->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('specialty_id')
                                    <p class="auth-field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="auth-field">
                                <label for="license_number" class="label">Medical registration number</label>
                                <input type="text" name="license_number" id="license_number"
                                    value="{{ old('license_number') }}" class="input mt-2">
                                @error('license_number')
                                    <p class="auth-field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2" data-registration-hospital>
                            <div class="auth-field">
                                <label for="facility_name" class="label">Hospital name</label>
                                <input type="text" name="facility_name" id="facility_name"
                                    value="{{ old('facility_name') }}" class="input mt-2">
                                @error('facility_name')
                                    <p class="auth-field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="auth-field">
                                <label for="level" class="label">Facility level</label>
                                <select name="level" id="level" class="input mt-2">
                                    @foreach (['primary', 'secondary', 'tertiary'] as $level)
                                        <option value="{{ $level }}" @selected(old('level') === $level)>
                                            {{ ucfirst($level) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('level')
                                    <p class="auth-field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="auth-field">
                            <label for="location" class="label">Location <span
                                    class="text-[color:var(--m-muted)]">(optional)</span></label>
                            <input type="text" name="location" id="location" autocomplete="address-level2"
                                value="{{ old('location') }}" class="input mt-2">
                            @error('location')
                                <p class="auth-field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="auth-field auth-field--password">
                                <label for="password" class="label">Password</label>
                                <div class="auth-password-wrap">
                                    <input type="password" name="password" id="password" required
                                        autocomplete="new-password" placeholder="Enter a password" class="input mt-2"
                                        aria-label="Password">
                                    <button type="button" class="auth-password-toggle" aria-label="Show password"
                                        aria-pressed="false" title="Show password">
                                        <svg data-password-icon="show" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M2.5 12s3.2-5 9.5-5 9.5 5 9.5 5-3.2 5-9.5 5-9.5-5-9.5-5Z"
                                                stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                                            <circle cx="12" cy="12" r="2.25" stroke="currentColor" stroke-width="1.7" />
                                        </svg>
                                        <svg data-password-icon="hide" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path
                                                d="m3 3 18 18M10.6 6.25A10.8 10.8 0 0 1 12 6c6.3 0 9.5 6 9.5 6a16.7 16.7 0 0 1-3.4 3.75M6.2 6.9C3.8 8.35 2.5 12 2.5 12s3.2 6 9.5 6c1.4 0 2.65-.3 3.75-.75"
                                                stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="auth-field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="auth-field auth-field--password">
                                <label for="password_confirmation" class="label">Confirm password</label>
                                <div class="auth-password-wrap">
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        required autocomplete="new-password" placeholder="Repeat the password"
                                        class="input mt-2" aria-label="Confirm password">
                                    <button type="button" class="auth-password-toggle" aria-label="Show password"
                                        aria-pressed="false" title="Show password">
                                        <svg data-password-icon="show" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M2.5 12s3.2-5 9.5-5 9.5 5 9.5 5-3.2 5-9.5 5-9.5-5-9.5-5Z"
                                                stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                                            <circle cx="12" cy="12" r="2.25" stroke="currentColor" stroke-width="1.7" />
                                        </svg>
                                        <svg data-password-icon="hide" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path
                                                d="m3 3 18 18M10.6 6.25A10.8 10.8 0 0 1 12 6c6.3 0 9.5 6 9.5 6a16.7 16.7 0 0 1-3.4 3.75M6.2 6.9C3.8 8.35 2.5 12 2.5 12s3.2 6 9.5 6c1.4 0 2.65-.3 3.75-.75"
                                                stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary mt-2 w-full justify-center py-3.5 text-base font-semibold">
                            Submit for verification
                        </button>

                        <p class="auth-security-indicator">Your account stays locked until a system administrator
                            approves it.</p>
                    </form>

                    <p class="auth-solo-foot">
                        <a href="{{ route('login') }}" class="auth-utility-link">Already registered? Sign in</a>
                    </p>
                </div>
            </div>
        </main>
    </div>
@endsection