@extends('layouts.public')

@section('title', 'BADBAADO: Connecting Hospitals, Connecting Care')

@section('public')
<main>
    {{-- HERO --}}
    <section class="m-hero relative overflow-hidden">
        <div class="relative z-10 mx-auto grid max-w-7xl items-center gap-16 px-5 pb-16 pt-16 sm:px-8 lg:grid-cols-[1.05fr_0.95fr] lg:pb-20 lg:pt-24">
            <div>
                <span class="m-rise inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/5 px-3.5 py-1.5 text-xs font-semibold text-slate-400 backdrop-blur" style="--d:.05s">
                    <span class="m-live"><span class="m-live__dot"></span>Live</span>
                    {{ $heroEyebrow }}
                </span>

                <h1 class="m-display mt-7 max-w-2xl text-[42px] sm:text-6xl lg:text-[74px]">
                    @foreach ($heroLines as $i => $line)
                    <span class="m-line"><span style="--d:{{ 0.08 + $i * 0.12 }}s" @if($loop->last)class="m-gradient-text"@endif>{{ $line }}</span></span>
                    @endforeach
                </h1>

                <p class="m-lead m-blur-in mt-7 max-w-xl text-lg" style="--d:.5s">
                    {{ $heroSubtitle }}
                </p>

                <div class="m-rise mt-9 flex flex-col gap-3 sm:flex-row" style="--d:.62s">
                    <a href="{{ route('login') }}" class="btn-primary px-7 py-3.5" data-magnetic>
                        {{ $ctaPrimary }}
                    </a>
                    <a href="{{ route('how-it-works') }}" class="btn-secondary px-7 py-3.5">
                        {{ $ctaSecondary }}
                    </a>
                </div>

                <div class="m-rise mt-12 flex flex-wrap items-center gap-x-8 gap-y-3 text-[13px] text-slate-500" style="--d:.74s">
                    @foreach (['Role-aware access', 'Live referral lifecycle', 'Audited by default'] as $proof)
                    <span class="flex items-center gap-2">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-accent-500/12 text-accent-300 ring-1 ring-accent-400/25">
                            <svg viewBox="0 0 20 20" fill="none" class="h-3 w-3"><path d="M4 10.5 8 14l8-8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        {{ $proof }}
                    </span>
                    @endforeach
                </div>
            </div>

            {{-- HERO VISUAL --}}
            <div class="m-visual mx-auto w-full max-w-[540px]" data-parallax="0.05">
                <div class="m-blur-in relative" style="--d:.4s">
                    <div class="m-window m-tilt" data-tilt="6">
                        <div class="m-window__bar">
                            <span class="m-dot"></span>
                            <span class="m-dot"></span>
                            <span class="m-dot"></span>
                            <span class="m-window__title">Referral · REF-2026-A1B2C3</span>
                            <span class="m-live"><span class="m-live__dot"></span>Live</span>
                        </div>

                        <div class="m-window__body">
                            <div class="m-route">
                                <div class="m-node">
                                    <span class="m-node__badge">AH</span>
                                    <span><b>Al-Hilal</b><small>Sending</small></span>
                                </div>
                                <span class="m-route__track"><span class="m-route__pulse"></span></span>
                                <div class="m-node m-node--recv">
                                    <span class="m-node__badge">SH</span>
                                    <span><b>Shifa</b><small>Receiving</small></span>
                                </div>
                            </div>

                            <div class="m-vitals">
                                <span class="m-chip">BP <b>160/95</b></span>
                                <span class="m-chip">HR <b>118</b></span>
                                <span class="m-chip">SpO₂ <b>94%</b></span>
                                <span class="m-chip">GCS <b>14</b></span>
                            </div>

                            <svg viewBox="0 0 320 64" class="m-wave" aria-hidden="true">
                                <path d="M0 34 H44 l6 -4 l5 -16 l6 30 l6 -24 l5 14 H148 l7 -6 l6 -16 l6 28 l6 -22 l5 12 H320"/>
                            </svg>

                            <div class="m-alert">
                                <span class="m-alert__ping">
                                    <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4"><path d="M10 5v6m0 3.5v.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M10 2.5 17.5 16h-15L10 2.5Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
                                </span>
                                <span class="min-w-0">
                                    <b>Critical pre-alert · STEMI 62/M</b>
                                    <small>Cath-lab unlocked · team briefed · bed confirmed</small>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative mx-auto hidden max-w-7xl px-8 pb-5 lg:block">
            <span class="m-cue m-fade" style="--d:1.1s">
                <span>Scroll</span>
                <span class="m-cue__rail"></span>
            </span>
        </div>
    </section>

    {{-- MARQUEE --}}
    <section class="border-y border-white/6 py-3">
        <div class="m-marquee">
            @php
                $marquee = [
                    'Information arrives before the patient',
                    'Structured handover',
                    'Emergency pre-alerts',
                    'Role-aware access',
                    'Audit-ready by default',
                    'Referral-scoped messaging',
                    'Track to arrival',
                ];
            @endphp
            <div class="m-marquee__track">
                @foreach ($marquee as $item)
                <span class="m-marquee__item">{{ $item }}</span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- STATS --}}
    <section class="mx-auto max-w-6xl px-5 py-16 sm:px-6">
        <div class="grid grid-cols-2 gap-y-12 lg:grid-cols-4">
            @foreach ($stats as $i => $stat)
            <div class="m-stat reveal reveal-delay-{{ min($i + 1, 6) }}">
                <div class="m-stat__value">
                    <span data-counter="{{ $stat['value'] }}">0</span><span>{{ $stat['unit'] }}</span>
                </div>
                <div class="m-stat__label">{{ $stat['label'] }}</div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- CORE PROMISE: PINNED SCENE --}}
    <section class="mx-auto max-w-6xl px-5 py-20 sm:px-6 lg:py-28">
        <div class="grid gap-14 lg:grid-cols-2 lg:gap-20" data-scene>
            <div>
                <div class="section-eyebrow reveal">The core promise</div>
                <h2 class="reveal reveal-delay-1 mt-4 text-3xl font-extrabold tracking-tight text-brand-950 sm:text-[40px] sm:leading-[1.1]">
                    Information arrives<br>before the patient.
                </h2>
                <p class="reveal reveal-delay-2 mt-5 max-w-lg text-base leading-relaxed text-slate-600">
                    Today the patient often carries their own story between hospitals, verbally, on
                    paper, repeated at every handover. BADBAADO turns that fragile transfer into a
                    structured digital snapshot that moves ahead of the ambulance.
                </p>

                <div class="mt-8">
                    @foreach ([
                        ['01', 'Structured by design', 'Patient, clinical picture, vitals and interventions, captured once, shared instantly.'],
                        ['02', 'Teamed in seconds', 'The receiving hospital acknowledges, reviews and accepts with full context in hand.'],
                        ['03', 'Visible end to end', 'Everyone on both sides follows the same live status until handover is done.'],
                    ] as $item)
                    <div class="m-scene__step" data-scene-step>
                        <span class="m-scene__marker">{{ $item[0] }}</span>
                        <h3 class="text-[17px] font-semibold text-brand-950">{{ $item[1] }}</h3>
                        <p class="mt-1.5 max-w-md text-sm leading-relaxed text-slate-500">{{ $item[2] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="hidden lg:block">
                <div class="m-scene__sticky">
                    <div class="m-scene__frame">
                        {{-- Panel 1 --}}
                        <div class="m-scene__panel" data-scene-panel>
                            <div class="m-card m-glow-ring flex h-full flex-col p-6">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Structured snapshot</span>
                                    <span class="status-pill bg-brand-500/15 text-accent-300">Draft</span>
                                </div>
                                <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                                    <div class="rounded-xl border border-white/8 bg-white/4 p-3">
                                        <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Patient</div>
                                        <div class="mt-1 font-semibold text-ink">62 · Male</div>
                                    </div>
                                    <div class="rounded-xl border border-white/8 bg-white/4 p-3">
                                        <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Specialty</div>
                                        <div class="mt-1 font-semibold text-ink">Cardiology</div>
                                    </div>
                                </div>
                                <div class="m-vitals mt-4">
                                    <span class="m-chip">BP <b>160/95</b></span>
                                    <span class="m-chip">HR <b>118</b></span>
                                    <span class="m-chip">SpO₂ <b>94%</b></span>
                                </div>
                                <div class="mt-auto rounded-xl border border-white/8 bg-white/4 p-4 text-sm leading-relaxed text-slate-400">
                                    <span class="font-semibold text-ink">Clinical picture:</span> ST-elevation
                                    myocardial infarction with ongoing chest pain.
                                </div>
                            </div>
                        </div>

                        {{-- Panel 2 --}}
                        <div class="m-scene__panel" data-scene-panel>
                            <div class="m-card m-glow-ring flex h-full flex-col p-6">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Shared instantly</span>
                                    <span class="status-pill bg-emerald-500/15 text-emerald-300">Received</span>
                                </div>

                                <div class="mt-8 flex items-center gap-4">
                                    <div class="m-node">
                                        <span class="m-node__badge">AH</span>
                                        <span><b>Al-Hilal</b><small>Sending</small></span>
                                    </div>
                                    <span class="m-route__track"><span class="m-route__pulse"></span></span>
                                    <div class="m-node m-node--recv">
                                        <span class="m-node__badge">SH</span>
                                        <span><b>Shifa</b><small>Receiving</small></span>
                                    </div>
                                </div>

                                <div class="mt-8 space-y-3 text-sm">
                                    <div class="flex items-center justify-between border-b border-white/6 pb-3">
                                        <span class="text-slate-500">Receiving clinician</span>
                                        <span class="font-semibold text-ink">Assigned</span>
                                    </div>
                                    <div class="flex items-center justify-between border-b border-white/6 pb-3">
                                        <span class="text-slate-500">Acknowledged</span>
                                        <span class="font-semibold text-emerald-300">90 seconds</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">Cath-lab</span>
                                        <span class="font-semibold text-accent-300">Confirmed</span>
                                    </div>
                                </div>

                                <div class="mt-auto flex gap-2">
                                    <span class="btn-primary btn-sm flex-1 justify-center">Accept referral</span>
                                    <span class="btn-secondary btn-sm">Discuss</span>
                                </div>
                            </div>
                        </div>

                        {{-- Panel 3 --}}
                        <div class="m-scene__panel" data-scene-panel>
                            <div class="m-card m-glow-ring flex h-full flex-col p-6">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Tracked to arrival</span>
                                    <span class="status-pill bg-orange-500/15 text-orange-300">In transit</span>
                                </div>

                                <div class="mt-8 space-y-5">
                                    @foreach ([
                                        ['Sent', 'Referral transmitted', true],
                                        ['Received', 'Team acknowledged', true],
                                        ['In transit', 'Ambulance en route · ETA 18 min', false],
                                    ] as $step)
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $step[2] ? 'bg-emerald-500/15 text-emerald-300 ring-1 ring-emerald-400/30' : 'bg-orange-500/15 text-orange-300 ring-1 ring-orange-400/30' }}">
                                            @if ($step[2])
                                            <svg viewBox="0 0 20 20" fill="none" class="h-3.5 w-3.5"><path d="M4 10.5 8 14l8-8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            @else
                                            <span class="h-2 w-2 rounded-full bg-current"></span>
                                            @endif
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block text-sm font-semibold text-ink">{{ $step[0] }}</span>
                                            <span class="block text-xs text-slate-500">{{ $step[1] }}</span>
                                        </span>
                                    </div>
                                    @endforeach
                                </div>

                                <div class="mt-auto flex items-center justify-between rounded-xl border border-white/8 bg-white/4 p-4">
                                    <span class="text-sm text-slate-500">Ambulance ETA</span>
                                    <span class="text-lg font-extrabold tracking-tight text-accent-300">18 min</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section id="how-it-works" class="border-y border-white/6">
        <div class="mx-auto max-w-6xl px-5 py-20 sm:px-6 lg:py-24">
            <div class="mx-auto max-w-2xl text-center">
                <div class="section-eyebrow reveal">How it works</div>
                <h2 class="reveal reveal-delay-1 mt-4 text-3xl font-extrabold tracking-tight text-brand-950 sm:text-4xl">
                    One calm path from decision to arrival.
                </h2>
                <p class="reveal reveal-delay-2 mt-4 text-slate-600">
                    Every referral follows the same structured pipeline. No phone tag. No repeated
                    stories. Just coordinated movement.
                </p>
            </div>

            <div class="mt-14 grid gap-6 md:grid-cols-4">
                @foreach ([
                    ['01', 'Pre-alert', 'Urgency is flagged the moment the decision to transfer is made, so teams begin preparing immediately.'],
                    ['02', 'Structured snapshot', 'Vitals, clinical picture and current interventions are captured once, in a fixed format.'],
                    ['03', 'Review & respond', 'The receiving team acknowledges, reviews, accepts or responds, with context already in hand.'],
                    ['04', 'Track to arrival', 'Transfer, arrival and handover stay on one visible thread until the referral closes.'],
                ] as $i => $step)
                <div class="m-card m-card-hover m-spotlight reveal reveal-delay-{{ $i + 1 }} relative p-6" data-spotlight>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-accent-500/12 ring-1 ring-accent-400/25">
                        <span class="text-sm font-extrabold tracking-wide text-accent-300">{{ $step[0] }}</span>
                    </div>
                    <h3 class="mt-5 text-[17px] font-bold text-brand-950">{{ $step[1] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $step[2] }}</p>
                </div>
                @endforeach
            </div>

            <div class="reveal mt-12 text-center">
                <a href="{{ route('how-it-works') }}" class="btn-secondary px-6 py-3">Explore the full referral journey →</a>
            </div>
        </div>
    </section>

    {{-- PRODUCT PREVIEW --}}
    <section class="mx-auto max-w-6xl px-5 py-20 sm:px-6 lg:py-28">
        <div class="mx-auto max-w-2xl text-center">
            <div class="section-eyebrow reveal">The system</div>
            <h2 class="reveal reveal-delay-1 mt-4 text-3xl font-extrabold tracking-tight text-brand-950 sm:text-4xl">
                This is the referral console.
            </h2>
            <p class="reveal reveal-delay-2 mt-4 text-slate-600">
                A calm, operational workspace where every referral, pre-alert and transfer is
                visible at a glance.
            </p>
        </div>

        <div class="m-blur-in mt-12" style="--d:.15s">
            <div class="m-window m-sheen reveal reveal-scale">
                <div class="m-window__bar">
                    <span class="m-dot"></span>
                    <span class="m-dot"></span>
                    <span class="m-dot"></span>
                    <span class="m-window__title">console.badbaado.bd/referrals</span>
                </div>
                <div class="flex">
                    <div class="hidden w-52 shrink-0 border-r border-white/8 p-4 sm:block">
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="BADBAADO logo" class="h-7 w-7 rounded-md ring-1 ring-white/15">
                            <span class="text-xs font-extrabold text-brand-950">BADBAADO</span>
                        </div>
                        <div class="mt-5 space-y-1.5">
                            <div class="rounded-lg bg-white/8 px-3 py-2 text-xs font-semibold text-accent-300">Dashboard</div>
                            <div class="rounded-lg px-3 py-2 text-xs font-medium text-slate-500">Referrals</div>
                            <div class="rounded-lg px-3 py-2 text-xs font-medium text-slate-500">New referral</div>
                            <div class="rounded-lg px-3 py-2 text-xs font-medium text-slate-500">Notifications</div>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1 p-4 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-bold text-brand-950">Referral overview</div>
                                <div class="text-xs text-slate-500">Everything moving across your hospitals</div>
                            </div>
                            <span class="btn-primary btn-sm hidden sm:inline-flex">+ New referral</span>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-3">
                            <div class="rounded-lg border border-white/8 bg-white/4 p-3">
                                <div class="text-lg font-extrabold text-brand-950">12</div>
                                <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Active</div>
                            </div>
                            <div class="rounded-lg border border-white/8 bg-white/4 p-3">
                                <div class="text-lg font-extrabold text-red-400">2</div>
                                <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Pre-alerts</div>
                            </div>
                            <div class="rounded-lg border border-white/8 bg-white/4 p-3">
                                <div class="text-lg font-extrabold text-accent-300">3</div>
                                <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Transfers</div>
                            </div>
                        </div>
                        <div class="mt-4 overflow-hidden rounded-lg border border-white/8">
                            @foreach ([
                                ['REF-2026-A1B2C3', 'Cardiology', 'Al-Hilal', 'Shifa', 'critical', 'Received'],
                                ['REF-2026-D4E5F6', 'Neurology', 'Mahabub', 'Al-Hilal', 'emergent', 'Under review'],
                                ['REF-2026-G7H8I9', 'Pediatrics', 'Shifa', 'South Delta', 'routine', 'Arrived'],
                                ['REF-2026-J0K1L2', 'Orthopedics', 'Rudra', 'Gazipur', 'urgent', 'Transfer'],
                            ] as $row)
                            <div class="grid grid-cols-2 items-center gap-2 border-b border-white/6 px-3 py-2.5 text-xs last:border-0 sm:grid-cols-12">
                                <div class="font-mono font-semibold text-slate-400 sm:col-span-3">{{ $row[0] }}</div>
                                <div class="text-slate-500 sm:col-span-2">{{ $row[1] }}</div>
                                <div class="hidden text-slate-500 sm:col-span-3 sm:block"><span class="font-medium text-slate-400">{{ $row[2] }}</span> → <span class="font-medium text-slate-400">{{ $row[3] }}</span></div>
                                <div class="sm:col-span-2"><span class="urgency-pill text-slate-400">{{ $row[4] }}</span></div>
                                <div class="flex items-center justify-end sm:col-span-2"><span class="rounded-full bg-white/8 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ $row[5] }}</span></div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- PRINCIPLE BAND --}}
    <section class="brand-gradient relative overflow-hidden">
        <div class="brand-glow pointer-events-none absolute -right-32 top-1/2 h-[480px] w-[480px] -translate-y-1/2"></div>
        <div class="brand-glow pointer-events-none absolute -left-40 -top-40 h-[420px] w-[420px]"></div>
        <div class="relative mx-auto max-w-6xl px-5 py-20 sm:px-6 lg:py-28">
            <div class="max-w-3xl">
                <div class="reveal text-xs font-bold uppercase tracking-[0.2em] text-accent-300">Our human principle</div>
                <h2 class="reveal reveal-delay-1 mt-4 text-3xl font-extrabold leading-tight tracking-tight text-white sm:text-[44px]">
                    The patient should never be the messenger between hospitals.
                </h2>
                <p class="reveal reveal-delay-2 mt-5 max-w-xl text-base leading-relaxed text-brand-100">
                    Coordination is the infrastructure. BADBAADO moves the right information to the
                    right team at the right moment, so clinicians spend their attention on the
                    patient, not on chasing a fax or a phone call.
                </p>
                <div class="reveal reveal-delay-3 mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('features') }}" class="inline-flex items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-bold text-brand-900 shadow-lg transition hover:-translate-y-0.5 hover:bg-brand-50">Explore the capabilities</a>
                    <a href="{{ route('login') }}" class="btn-secondary px-6 py-3">Try the demo console</a>
                </div>
            </div>
        </div>
    </section>

    {{-- CAPABILITIES --}}
    <section id="capabilities" class="mx-auto max-w-6xl px-5 py-20 sm:px-6 lg:py-28">
        <div class="grid items-start gap-4 md:grid-cols-2">
            @foreach ([
                ['Structured handover', 'Vitals, clinical picture, conditions and current interventions are captured in one fixed format, so nothing important gets lost in transit.'],
                ['Emergency pre-alerts', 'Critical cases trigger an immediate pre-alert to the receiving team, so preparation begins before wheels turn.'],
                ['Role-aware access', 'Health care workers, coordinators and hospital admins each see exactly what their job requires, and nothing more.'],
                ['Audit-ready by default', 'Every creation, transition and decision is recorded for accountability across the entire referral lifecycle.'],
                ['Referral-scoped messaging', 'Clinicians discuss a case in one thread attached to that referral, so context stays with the patient.'],
                ['AI-assisted urgency', 'An AI-assisted suggestion helps calibrate urgency. The clinical decision always remains human.'],
            ] as $cap)
            <div class="m-card m-card-hover m-spotlight reveal p-6" data-spotlight>
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-accent-500/12 text-accent-300 ring-1 ring-accent-400/25">
                        <svg viewBox="0 0 20 20" fill="none" class="h-4.5 w-4.5">
                            <circle cx="10" cy="10" r="3" fill="currentColor"/>
                            <path d="M3 5h8M3 10h4M3 15h9M14 15h3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" opacity="0.55"/>
                            <circle cx="14.5" cy="5.5" r="2" stroke="currentColor" stroke-width="1.4"/>
                        </svg>
                    </span>
                    <h3 class="text-[16px] font-bold text-brand-950">{{ $cap[0] }}</h3>
                </div>
                <p class="mt-3 text-sm leading-relaxed text-slate-500">{{ $cap[1] }}</p>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ANNOUNCEMENTS (CMS) --}}
    @if ($announcements->isNotEmpty())
    <section class="border-t border-white/6">
        <div class="mx-auto max-w-6xl px-5 py-20 sm:px-6 lg:py-24">
            <div class="section-eyebrow reveal">{{ $announcementsEyebrow }}</div>
            <h2 class="reveal reveal-delay-1 mt-4 text-3xl font-extrabold tracking-tight text-brand-950 sm:text-4xl">What's new on the network.</h2>
            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ($announcements as $announcement)
                <div class="m-card m-card-hover reveal p-6">
                    <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-accent-300">{{ $announcement->published_at?->format('d M Y') }}</p>
                    <h3 class="mt-3 text-[17px] font-bold text-brand-950">{{ $announcement->title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $announcement->body }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- FAQ (CMS) --}}
    @if ($faqs->isNotEmpty())
    <section class="border-t border-white/6 bg-white/20">
        <div class="mx-auto max-w-4xl px-5 py-20 sm:px-6 lg:py-24">
            <div class="text-center">
                <div class="section-eyebrow reveal">{{ $faqEyebrow }}</div>
                <h2 class="reveal reveal-delay-1 mt-4 text-3xl font-extrabold tracking-tight text-brand-950 sm:text-4xl">Questions, answered.</h2>
            </div>
            <div class="mt-12 space-y-4">
                @foreach ($faqs as $faq)
                <details class="m-card m-card-hover reveal group open:ring-1 open:ring-accent-400/25">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-6 text-[16px] font-bold text-brand-950">
                        {{ $faq->question }}
                        <span class="text-accent-300 transition group-open:rotate-45"><svg viewBox="0 0 20 20" fill="none" class="h-5 w-5"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span>
                    </summary>
                    <p class="px-6 pb-6 text-sm leading-relaxed text-slate-500">{{ $faq->answer }}</p>
                </details>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- CONTACT (CMS) --}}
    <section class="border-t border-white/6">
        <div class="mx-auto max-w-6xl px-5 py-20 sm:px-6 lg:py-24">
            <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
                <div>
                    <div class="section-eyebrow reveal">{{ $contactEyebrow }}</div>
                    <h2 class="reveal reveal-delay-1 mt-4 text-3xl font-extrabold tracking-tight text-brand-950 sm:text-4xl">Let's talk.</h2>
                    <p class="reveal reveal-delay-2 mt-4 max-w-md text-slate-600">Questions about the platform, onboarding a hospital, or running a pilot on your network — reach the operations team directly.</p>
                </div>
                <div>
                    <div class="m-card m-card-hover reveal grid gap-5 p-8 sm:grid-cols-2">
                        @if ($contact['email'])<div><p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Email</p><p class="mt-1 font-semibold text-brand-950">{{ $contact['email'] }}</p></div>@endif
                        @if ($contact['phone'])<div><p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Phone</p><p class="mt-1 font-semibold text-brand-950">{{ $contact['phone'] }}</p></div>@endif
                        @if ($contact['address'])<div class="sm:col-span-2"><p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Operations</p><p class="mt-1 font-semibold text-brand-950">{{ $contact['address'] }}</p></div>@endif
                        @if (empty(array_filter($contact)))
                        <p class="text-sm text-slate-500 sm:col-span-2">Contact details are configured by the platform team.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FINAL CTA --}}
    <section class="mx-auto max-w-6xl px-5 pb-28 sm:px-6">
        <div class="m-card reveal relative overflow-hidden px-8 py-16 text-center sm:px-16">
            <div class="brand-glow pointer-events-none absolute -top-32 left-1/2 h-96 w-96 -translate-x-1/2"></div>
            <div class="relative">
                <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="BADBAADO logo" class="mx-auto h-16 w-16 rounded-2xl shadow-float ring-1 ring-white/15">
                <h2 class="mt-6 text-3xl font-extrabold tracking-tight text-brand-950 sm:text-[40px]">
                    Connecting Hospitals, Connecting Care
                </h2>
                <p class="mx-auto mt-4 max-w-xl text-lg text-slate-600">
                    Explore the live console with demo accounts, or see how the full referral journey works.
                </p>
                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a href="{{ route('login') }}" class="btn-primary px-7 py-3.5" data-magnetic>Open the console</a>
                    <a href="{{ route('about') }}" class="btn-secondary px-7 py-3.5">About BADBAADO</a>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
