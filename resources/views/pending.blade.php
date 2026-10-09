@extends('layouts.app')

@section('title', 'Awaiting verification: BADBAADO')

@section('body')
    <div class="auth-shell auth-shell--solo">
        <span class="auth-wash" aria-hidden="true"></span>

        <main class="auth-form-panel relative">
            <a href="{{ route('home') }}" class="auth-solo-mark">
                <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="" aria-hidden="true"
                    class="auth-brand-logo">
                <span class="auth-brand-word">BADBAADO</span>
            </a>

            <div class="auth-form-shell">
                <div class="auth-form-wrap w-full">
                    <div class="auth-form-header">
                        <p class="auth-form-kicker">Account status</p>
                        <h1 class="text-[color:var(--m-ink)]">
                            {{ $application?->status->label() ?? 'Awaiting verification' }}
                        </h1>
                        <p>Hello {{ $user->name }}, here is where your registration stands.</p>
                    </div>

                    @if ($application === null)
                        <div class="auth-notice" role="status">
                            We could not find an application on this account. Contact your administrator if
                            this looks wrong.
                        </div>
                    @else
                        <dl class="space-y-3 text-sm">
                            <div class="flex items-baseline justify-between gap-4 border-b border-[color:var(--m-border)] pb-3">
                                <dt class="text-[color:var(--m-muted)]">Registered as</dt>
                                <dd class="text-right font-semibold text-[color:var(--m-ink)]">
                                    {{ $application->type->label() }}
                                </dd>
                            </div>

                            @if ($application->type === \App\Enums\ProviderApplicationType::DOCTOR)
                                <div class="flex items-baseline justify-between gap-4 border-b border-[color:var(--m-border)] pb-3">
                                    <dt class="text-[color:var(--m-muted)]">Specialty</dt>
                                    <dd class="text-right font-semibold text-[color:var(--m-ink)]">
                                        {{ $application->payload['specialty_id'] ? optional(\App\Models\Specialty::find($application->payload['specialty_id']))->name : '—' }}
                                    </dd>
                                </div>
                                <div class="flex items-baseline justify-between gap-4 border-b border-[color:var(--m-border)] pb-3">
                                    <dt class="text-[color:var(--m-muted)]">Registration number</dt>
                                    <dd class="text-right font-semibold text-[color:var(--m-ink)]">
                                        {{ $application->payload['license_number'] ?? '—' }}
                                    </dd>
                                </div>
                            @else
                                <div class="flex items-baseline justify-between gap-4 border-b border-[color:var(--m-border)] pb-3">
                                    <dt class="text-[color:var(--m-muted)]">Hospital</dt>
                                    <dd class="text-right font-semibold text-[color:var(--m-ink)]">
                                        {{ $application->payload['facility_name'] ?? '—' }}
                                    </dd>
                                </div>
                            @endif

                            <div class="flex items-baseline justify-between gap-4 border-b border-[color:var(--m-border)] pb-3">
                                <dt class="text-[color:var(--m-muted)]">Submitted</dt>
                                <dd class="text-right font-semibold text-[color:var(--m-ink)]">
                                    {{ $application->created_at?->format('d M Y H:i') }}
                                </dd>
                            </div>

                            @if ($application->reviewed_at)
                                <div class="flex items-baseline justify-between gap-4 border-b border-[color:var(--m-border)] pb-3">
                                    <dt class="text-[color:var(--m-muted)]">Reviewed</dt>
                                    <dd class="text-right font-semibold text-[color:var(--m-ink)]">
                                        {{ $application->reviewed_at->format('d M Y H:i') }}
                                    </dd>
                                </div>
                            @endif
                        </dl>

                        @if ($application->rejection_reason)
                            <div class="auth-notice" role="alert">
                                <strong>Reason:</strong> {{ $application->rejection_reason }}
                            </div>
                        @endif

                        @if ($application->status->isPending())
                            <div class="auth-notice" role="status">
                                A system administrator reviews every registration before console access is
                                granted. You will receive an email at {{ $user->email }} once a decision
                                is made.
                            </div>
                        @elseif ($application->status === \App\Enums\ProviderApplicationStatus::APPROVED)
                            <div class="auth-notice" role="status">
                                Your account is approved.
                                @if ($application->hospital)
                                    Sign in again to continue to your
                                    <strong>{{ $application->hospital->name }}</strong> workspace.
                                @endif
                            </div>
                        @endif
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-secondary w-full justify-center py-3">
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>
@endsection