@extends('layouts.public')

@section('title', 'Features — BADBAADO')

@section('public')
<main>
    <section class="mx-auto max-w-6xl px-5 pb-10 pt-16 sm:px-6 lg:pt-20">
        <div class="max-w-3xl">
            <div class="reveal section-eyebrow">Features</div>
            <h1 class="reveal reveal-delay-1 mt-4 text-4xl font-extrabold tracking-tight text-brand-950 sm:text-5xl">
                Built for the space<br>between hospitals.
            </h1>
            <p class="reveal reveal-delay-2 mt-5 max-w-2xl text-lg leading-relaxed text-slate-600">
                BADBAADO bundles the coordination pieces that usually live in phone calls, fax
                machines, and paper notes into one structured, connected channel.
            </p>
        </div>
    </section>

    {{-- FEATURE ROWS --}}
    <section class="mx-auto max-w-6xl space-y-6 px-5 pb-24 pt-6 sm:px-6">
        @foreach ([
            ['Emergency pre-alert', 'When a clinician flags a referral as an emergency, a pre-alert fires to the receiving team immediately — before transport is even booked. The team briefs, books capacity, and prepares to receive.', null],
            ['Structured handover', 'Vitals, consciousness, trauma indicators, existing conditions, current interventions, and symptoms arrive in one fixed format. Nothing important is left to memory or repeated phone calls.', 'shared'],
            ['Referral-scoped messaging', 'Every referral carries its own discussion thread. Both hospitals ask, clarify, and update on the same record — context stays with the patient the whole way.', null],
            ['AI-assisted urgency suggestion', 'A suggestion calibrates urgency from the captured observations. It informs; it never decides. The human clinical decision is always shown with greater authority.', 'ai'],
            ['Role-specific access', 'Health care workers, referral coordinators, and hospital admins each get an interface matched to their responsibility — enforced on every request.', 'shared'],
            ['Audit-ready by design', 'Every creation, transition, and decision is written to an append-only audit log, giving administrators an exact record of what happened and when.', null],
        ] as $feat)
        <div class="card reveal p-8 sm:p-10">
            <div class="grid items-center gap-8 lg:grid-cols-[1fr_1fr]">
                <div>
                    <div class="section-eyebrow">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight text-brand-950 sm:text-3xl">{{ $feat[0] }}</h2>
                    <p class="mt-4 max-w-lg text-sm leading-relaxed text-slate-600">{{ $feat[1] }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-100" style="min-height:150px">
                    @if ($feat[0] === 'Emergency pre-alert')
                    <div class="rounded-xl border border-red-100 bg-white p-4">
                        <div class="flex items-center justify-between">
                            <span class="urgency-pill text-red-600">Critical pre-alert</span>
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">T-minus 14 min to arrival</span>
                        </div>
                        <div class="mt-3 text-sm text-slate-600">
                            <span class="font-semibold text-slate-800">STEMI · 62/M</span> — cath-lab unlocked for Al-Hilal
                        </div>
                        <div class="mt-3 flex gap-2">
                            <span class="rounded-md bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-700">Team briefed</span>
                            <span class="rounded-md bg-brand-50 px-2 py-1 text-[11px] font-semibold text-brand-700">Bed confirmed</span>
                            <span class="rounded-md bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-600">Intervention team ready</span>
                        </div>
                    </div>
                    @elseif ($feat[0] === 'AI-assisted urgency suggestion')
                    <div class="rounded-xl border border-slate-200 bg-white p-4">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">AI-assisted urgency suggestion</div>
                        <div class="mt-2 flex items-center gap-3">
                            <span class="urgency-pill text-orange-600">High</span>
                            <span class="text-xs text-slate-500">Confidence 87%</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Structured observations indicate elevated urgency.</p>
                        <div class="mt-3 border-t border-slate-100 pt-3">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Human clinical decision</div>
                            <div class="mt-2"><span class="urgency-pill bg-brand-50 text-brand-700">Critical</span> <span class="ml-1 text-xs font-medium text-slate-600">— overrides suggestion</span></div>
                        </div>
                    </div>
                    @else
                    <div class="flex h-full items-center justify-center text-center">
                        <div class="max-w-[220px]">
                            <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-brand-600 shadow-card ring-1 ring-slate-100">
                                <svg viewBox="0 0 20 20" fill="none" class="h-5 w-5">
                                    <circle cx="10" cy="10" r="3" fill="currentColor"/>
                                    <path d="M3 5h8M3 10h4M3 15h9M14 15h3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" opacity="0.55"/>
                                </svg>
                            </div>
                            <div class="mt-3 text-xs font-semibold text-slate-500">Connected, always</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </section>

    {{-- SECURITY --}}
    <section id="security" class="border-y border-slate-200/70 bg-white">
        <div class="mx-auto max-w-6xl px-5 py-20 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <div class="reveal section-eyebrow">Security &amp; trust</div>
                <h2 class="reveal reveal-delay-1 mt-4 text-3xl font-extrabold tracking-tight text-brand-950 sm:text-4xl">
                    Trust is infrastructure too.
                </h2>
                <p class="reveal reveal-delay-2 mt-4 text-slate-600">
                    A hospital will not put a patient on a network it cannot trust. BADBAADO treats
                    security as a first-class feature.
                </p>
            </div>
            <div class="reveal reveal-delay-1 mt-12 grid gap-6 md:grid-cols-3">
                @foreach ([
                    ['Authentication', 'Token-based sessions for every user; hospital staff can only reach data their own role and facility allow.'],
                    ['Hospital isolation', 'Referrals are visible only to the referring and receiving hospitals — plus system administration. No cross-tenant leaks.'],
                    ['Accountability', 'Every action is logged with actor, timestamp and metadata in an append-only audit trail.'],
                ] as $s)
                <div class="card card-hover p-6">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 ring-1 ring-brand-100">
                        <svg viewBox="0 0 20 20" fill="none" class="h-5 w-5">
                            <rect x="4" y="8" width="12" height="8" rx="2" stroke="currentColor" stroke-width="1.4"/>
                            <path d="M7 8V6a3 3 0 0 1 6 0v2" stroke="currentColor" stroke-width="1.4"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-[16px] font-bold text-brand-950">{{ $s[0] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $s[1] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="mx-auto max-w-6xl px-5 py-20 sm:px-6">
        <div class="reveal mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-extrabold tracking-tight text-brand-950 sm:text-[40px]">Experience it yourself</h2>
            <p class="mt-4 text-lg text-slate-600">Sign in to the console with a demo hospital account and move a referral end to end.</p>
            <a href="{{ route('login') }}" class="btn-primary mt-8 px-6 py-3">Open the console</a>
        </div>
    </section>
</main>
@endsection