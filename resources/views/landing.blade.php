@extends('layouts.public')

@section('title', 'BADBAADO — Connecting Hospitals, Connecting Care')

@section('public')
<main>
    {{-- HERO --}}
    <section class="relative overflow-hidden">
        <div class="brand-glow pointer-events-none absolute -top-40 left-1/2 h-[520px] w-[820px] -translate-x-1/2"></div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.35]" style="background-image:linear-gradient(#dbe5f0 1px, transparent 1px), linear-gradient(90deg, #dbe5f0 1px, transparent 1px); background-size:44px 44px; mask-image:radial-gradient(ellipse 70% 60% at 50% 0%, black 30%, transparent 75%); -webkit-mask-image:radial-gradient(ellipse 70% 60% at 50% 0%, black 30%, transparent 75%);"></div>

        <div class="relative mx-auto grid max-w-6xl items-center gap-14 px-5 pb-20 pt-16 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:pb-28 lg:pt-24">
            <div>
                <div class="reveal inline-flex items-center gap-2 rounded-full border border-brand-100 bg-white px-3.5 py-1.5 text-xs font-semibold text-brand-700 shadow-sm">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent-500 data-dot"></span>
                    Now connecting hospitals &amp; care teams
                </div>

                <h1 class="reveal reveal-delay-1 mt-6 text-[40px] font-extrabold leading-[1.05] tracking-tight text-brand-950 sm:text-6xl lg:text-[64px]">
                    Connecting<br>
                    Hospitals,<br>
                    <span class="brand-text-gradient">Connecting Care</span>
                </h1>

                <p class="reveal reveal-delay-2 mt-6 max-w-xl text-lg leading-relaxed text-slate-600">
                    BADBAADO is a coordinated referral and emergency pre-alert gateway.
                    <strong class="font-semibold text-brand-800">Information arrives before the patient</strong>
                    — so the receiving team is prepared from the moment the decision to transfer is made.
                </p>

                <div class="reveal reveal-delay-3 mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('login') }}" class="btn-primary px-6 py-3">Open the console</a>
                    <a href="{{ route('how-it-works') }}" class="btn-secondary px-6 py-3">See how it works</a>
                </div>

                <div class="reveal reveal-delay-4 mt-10 flex flex-wrap items-center gap-x-8 gap-y-3 text-[13px] text-slate-500">
                    <span class="flex items-center gap-2"><span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-brand-50 text-brand-600 ring-1 ring-brand-100">✓</span> Role-aware access</span>
                    <span class="flex items-center gap-2"><span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-brand-50 text-brand-600 ring-1 ring-brand-100">✓</span> Live referral lifecycle</span>
                    <span class="flex items-center gap-2"><span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-brand-50 text-brand-600 ring-1 ring-brand-100">✓</span> Audited by default</span>
                </div>
            </div>

            {{-- HERO CONNECTION SCENE --}}
            <div class="reveal reveal-delay-2 relative mx-auto w-full max-w-[520px]">
                <div class="card relative overflow-hidden p-6 shadow-float">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                        <span class="h-2 w-2 rounded-full bg-slate-200"></span>
                        <span class="h-2 w-2 rounded-full bg-slate-200"></span>
                        <span class="h-2 w-2 rounded-full bg-slate-200"></span>
                        <span class="ml-2">Referral corridor — live</span>
                    </div>

                    <svg viewBox="0 0 520 170" fill="none" class="mt-4 w-full">
                        <defs>
                            <linearGradient id="corridor" x1="0" y1="0" x2="520" y2="0" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#185A9D"/>
                                <stop offset="1" stop-color="#28B8CC"/>
                            </linearGradient>
                        </defs>

                        <line x1="118" y1="78" x2="296" y2="78" stroke="url(#corridor)" stroke-width="3" stroke-linecap="round" class="svg-flow"/>
                        <line x1="318" y1="78" x2="400" y2="78" stroke="url(#corridor)" stroke-width="3" stroke-linecap="round" class="svg-flow"/>
                        <line x1="128" y1="78" x2="128" y2="118" stroke="#28B8CC" stroke-width="2" stroke-dasharray="4 6" opacity="0.6"/>
                        <line x1="386" y1="78" x2="386" y2="118" stroke="#28B8CC" stroke-width="2" stroke-dasharray="4 6" opacity="0.6"/>

                        <circle cx="132" cy="78" r="6" fill="#28B8CC" class="data-dot" style="transform-origin:132px 78px"/>
                        <circle cx="382" cy="78" r="6" fill="#185A9D" class="data-dot" style="transform-origin:382px 78px"/>

                        <g>
                            <rect x="4" y="48" width="118" height="58" rx="14" fill="#fff" stroke="#E2E8F0"/>
                            <circle cx="28" cy="77" r="14" fill="#E9FAFC"/>
                            <path d="M22.5 77h9M27 72.5v9" stroke="#1F9EB1" stroke-width="2.4" stroke-linecap="round"/>
                            <text x="50" y="70" font-size="11" font-weight="700" fill="#172033" font-family="Inter, sans-serif">Al-Hilal</text>
                            <text x="50" y="86" font-size="9.5" fill="#64748B" font-family="Inter, sans-serif">Sending hospital</text>
                            <text x="50" y="99" font-size="8.5" fill="#0A2D50" font-family="Inter, sans-serif" font-weight="700">● EMR READY</text>
                        </g>

                        <g>
                            <rect x="298" y="46" width="122" height="62" rx="16" fill="#0A2D50"/>
                            <rect x="298" y="46" width="122" height="62" rx="16" fill="url(#corridor)" opacity="0.16"/>
                            <pattern id="bph" width="10" height="10" patternUnits="userSpaceOnUse">
                                <path d="M0 5h10M5 0v10" stroke="#28B8CC" stroke-width="0.7" opacity="0.35"/>
                            </pattern>
                            <rect x="330" y="50" width="8" height="8" rx="2" fill="#28B8CC"/>
                            <text x="346" y="60" font-size="12" font-weight="800" letter-spacing="1" fill="#fff" font-family="Inter, sans-serif">BADBAADO</text>
                            <text x="330" y="80" font-size="8" fill="#9FC7E8" font-family="Inter, sans-serif" letter-spacing="1">REFERRAL GATEWAY</text>
                            <circle cx="354" cy="95" r="4" fill="#28B8CC" class="data-dot"/>
                        </g>

                        <g>
                            <rect x="404" y="48" width="112" height="58" rx="14" fill="#fff" stroke="#E2E8F0"/>
                            <circle cx="428" cy="77" r="14" fill="#E9FAFC"/>
                            <path d="M421 77h14M428 70v14" stroke="#1F9EB1" stroke-width="2.4" stroke-linecap="round"/>
                            <text x="450" y="70" font-size="11" font-weight="700" fill="#172033" font-family="Inter, sans-serif">Shifa</text>
                            <text x="450" y="86" font-size="9.5" fill="#64748B" font-family="Inter, sans-serif">Receiving hospital</text>
                            <text x="450" y="99" font-size="8.5" fill="#0A2D50" font-family="Inter, sans-serif" font-weight="700">● PRE-ALERTED</text>
                        </g>
                    </svg>

                    <div class="mt-5 rounded-[12px] border border-red-100 bg-red-50/70 p-3.5">
                        <div class="flex items-center justify-between">
                            <span class="urgency-pill -ml-1 text-red-600">Critical</span>
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Pre-alert · just now</span>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <span class="rounded-md bg-white px-2 py-1 text-[11px] font-semibold text-slate-600 ring-1 ring-slate-200">BP 160/95</span>
                            <span class="rounded-md bg-white px-2 py-1 text-[11px] font-semibold text-slate-600 ring-1 ring-slate-200">HR 118</span>
                            <span class="rounded-md bg-white px-2 py-1 text-[11px] font-semibold text-slate-600 ring-1 ring-slate-200">SpO₂ 94%</span>
                        </div>
                        <div class="mt-2 text-[11px] text-slate-500">Team briefed · cath-lab booked · bed confirmed</div>
                    </div>
                </div>

                <div class="card absolute -bottom-6 -left-4 hidden items-center gap-3 px-4 py-3 shadow-float sm:flex">
                    <span class="status-pill bg-emerald-50 text-emerald-600">Received</span>
                    <span class="text-xs font-medium text-slate-600">Acknowledged in <span class="font-bold text-brand-800">90s</span></span>
                </div>
            </div>
        </div>
    </section>

    {{-- STATS --}}
    <section class="border-y border-slate-200/70 bg-white">
        <div class="mx-auto grid max-w-6xl grid-cols-2 gap-y-10 px-5 py-12 sm:px-6 lg:grid-cols-4">
            @foreach ($stats as $stat)
            <div class="reveal px-2">
                <div class="text-4xl font-extrabold tracking-tight text-brand-950">
                    {{ $stat['value'] }}<span class="text-2xl text-accent-500">{{ $stat['unit'] }}</span>
                </div>
                <div class="mt-2 text-sm font-medium text-slate-500">{{ $stat['label'] }}</div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- INFORMATION ARRIVES BEFORE THE PATIENT --}}
    <section class="mx-auto max-w-6xl px-5 py-20 sm:px-6 lg:py-28">
        <div class="grid items-center gap-14 lg:grid-cols-2">
            <div>
                <div class="section-eyebrow reveal">The core promise</div>
                <h2 class="reveal reveal-delay-1 mt-4 text-3xl font-extrabold tracking-tight text-brand-950 sm:text-[40px] sm:leading-[1.1]">
                    Information arrives<br>before the patient.
                </h2>
                <p class="reveal reveal-delay-2 mt-5 max-w-lg text-base leading-relaxed text-slate-600">
                    Today, the patient often carries their own story between hospitals — verbally,
                    on paper, repeated at every handover. BADBAADO turns that fragile transfer into
                    a structured digital snapshot that moves ahead of the ambulance.
                </p>
                <ul class="reveal reveal-delay-3 mt-8 space-y-4">
                    @foreach ([
                        ['01', 'Structured by design', 'Patient, clinical picture, vitals, interventions — captured once, shared instantly.'],
                        ['02', 'Teamed in seconds', 'The receiving hospital acknowledges, reviews and accepts with context in hand.'],
                        ['03', 'Visible end to end', 'Everyone on both sides follows the same live status until handover is done.'],
                    ] as $item)
                    <li class="flex gap-4">
                        <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-xs font-extrabold text-brand-600 ring-1 ring-brand-100">{{ $item[0] }}</span>
                        <div>
                            <div class="text-[15px] font-semibold text-brand-950">{{ $item[1] }}</div>
                            <p class="mt-0.5 text-sm leading-relaxed text-slate-500">{{ $item[2] }}</p>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>

            <div class="reveal reveal-delay-2 relative">
                <div class="brand-glow absolute -right-10 -top-10 h-72 w-72"></div>
                <div class="card mx-auto max-w-md p-6 shadow-float">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="BADBAADO logo" class="h-6 w-6 rounded-md ring-1 ring-slate-200">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Referral #REF-2026-EXAMPLE</span>
                        </div>
                        <span class="status-pill bg-brand-50 text-brand-600">Under review</span>
                    </div>
                    <div class="mt-4 rounded-lg bg-slate-50 p-3 text-[13px] text-slate-600">
                        <span class="font-semibold text-brand-800">Cardiology</span> · ST-elevation myocardial infarction, ongoing chest pain
                    </div>
                    <div class="mt-3 flex items-center justify-between rounded-lg border border-slate-100 p-3 text-xs text-slate-500">
                        <span><span class="font-semibold text-slate-700">Al-Hilal</span> → <span class="font-semibold text-slate-700">Shifa</span></span>
                        <span class="flex items-center gap-1.5"><span class="flow-line mt-0.5 inline-block h-0.5 w-10 rounded-full bg-slate-200"></span></span>
                    </div>
                    <div class="mt-4 space-y-2 text-xs">
                        <div class="flex justify-between"><span class="text-slate-500">Receiving clinician</span><span class="font-medium text-slate-700">Assigned</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Cath-lab readiness</span><span class="font-medium text-emerald-600">Confirmed</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Ambulance ETA</span><span class="font-medium text-slate-700">18 min</span></div>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <span class="btn-primary btn-sm flex-1 justify-center">Accept referral</span>
                        <span class="btn-secondary btn-sm">Discuss</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section id="how-it-works" class="border-y border-slate-200/70 bg-white">
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

            <div class="mt-14 grid gap-10 md:grid-cols-4">
                @foreach ([
                    ['01', 'Pre-alert', 'Urgency is flagged the moment the decision to transfer is made, so teams begin preparing immediately.'],
                    ['02', 'Structured snapshot', 'Vitals, clinical picture and current interventions are captured once, in a fixed format.'],
                    ['03', 'Review & respond', 'The receiving team acknowledges, reviews, accepts or responds — context already in hand.'],
                    ['04', 'Track to arrival', 'Transfer, arrival and handover stay on one visible thread until the referral closes.'],
                ] as $i => $step)
                <div class="reveal reveal-delay-{{ $i + 1 }} relative">
                    @if (! $loop->last)
                    <div class="absolute right-0 top-7 hidden h-px w-full border-t border-dashed border-brand-200 md:left-full md:block"></div>
                    @endif
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 ring-1 ring-brand-100">
                        <span class="text-sm font-extrabold tracking-wide text-brand-600">{{ $step[0] }}</span>
                    </div>
                    <h3 class="mt-5 text-[17px] font-bold text-brand-950">{{ $step[1] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $step[2] }}</p>
                </div>
                @endforeach
            </div>

            <div class="reveal mt-14 text-center">
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

        <div class="reveal reveal-delay-2 mt-12">
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card-hover">
                <div class="flex items-center gap-2 border-b border-slate-100 bg-slate-50 px-5 py-3">
                    <span class="h-2.5 w-2.5 rounded-full bg-slate-200"></span>
                    <span class="h-2.5 w-2.5 rounded-full bg-slate-200"></span>
                    <span class="h-2.5 w-2.5 rounded-full bg-slate-200"></span>
                    <span class="ml-3 text-xs font-medium text-slate-400">console.badbaado.bd/referrals</span>
                </div>
                <div class="flex">
                    <div class="hidden w-52 shrink-0 border-r border-slate-100 bg-[#F6F8FB] p-4 sm:block">
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="BADBAADO logo" class="h-7 w-7 rounded-md ring-1 ring-slate-200">
                            <span class="text-xs font-extrabold text-brand-950">BADBAADO</span>
                        </div>
                        <div class="mt-5 space-y-1.5">
                            <div class="rounded-lg bg-white px-3 py-2 text-xs font-semibold text-brand-700 ring-1 ring-brand-100">Dashboard</div>
                            <div class="rounded-lg px-3 py-2 text-xs font-medium text-slate-500">Referrals</div>
                            <div class="rounded-lg px-3 py-2 text-xs font-medium text-slate-500">New referral</div>
                            <div class="rounded-lg px-3 py-2 text-xs font-medium text-slate-500">Notifications</div>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1 p-4 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-bold text-brand-950">Referral overview</div>
                                <div class="text-xs text-slate-400">Everything moving across your hospitals</div>
                            </div>
                            <span class="btn-primary btn-sm hidden sm:inline-flex">+ New referral</span>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-3">
                            <div class="rounded-lg border border-slate-100 bg-white p-3">
                                <div class="text-lg font-extrabold text-brand-950">12</div>
                                <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Active</div>
                            </div>
                            <div class="rounded-lg border border-slate-100 bg-white p-3">
                                <div class="text-lg font-extrabold text-red-600">2</div>
                                <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Pre-alerts</div>
                            </div>
                            <div class="rounded-lg border border-slate-100 bg-white p-3">
                                <div class="text-lg font-extrabold text-accent-600">3</div>
                                <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Transfers</div>
                            </div>
                        </div>
                        <div class="mt-4 overflow-hidden rounded-lg border border-slate-100">
                            @foreach ([
                                ['REF-2026-A1B2C3', 'Cardiology', 'Al-Hilal', 'Shifa', 'critical', 'Received', 'red'],
                                ['REF-2026-D4E5F6', 'Neurology', 'Mahabub', 'Al-Hilal', 'emergent', 'Under review', 'orange'],
                                ['REF-2026-G7H8I9', 'Pediatrics', 'Shifa', 'South Delta', 'routine', 'Arrived', 'purple'],
                                ['REF-2026-J0K1L2', 'Orthopedics', 'Rudra', 'Gazipur', 'urgent', 'Transfer', 'blue'],
                            ] as $row)
                            <div class="grid grid-cols-2 items-center gap-2 border-b border-slate-100 px-3 py-2.5 text-xs last:border-0 sm:grid-cols-12">
                                <div class="font-mono font-semibold text-slate-600 sm:col-span-3">{{ $row[0] }}</div>
                                <div class="text-slate-500 sm:col-span-2">{{ $row[1] }}</div>
                                <div class="hidden text-slate-500 sm:col-span-3 sm:block"><span class="font-medium text-slate-700">{{ $row[2] }}</span> → <span class="font-medium text-slate-700">{{ $row[3] }}</span></div>
                                <div class="sm:col-span-2"><span class="urgency-pill text-slate-600">{{ $row[4] }}</span></div>
                                <div class="flex items-center justify-end sm:col-span-2"><span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-600">{{ $row[5] }}</span></div>
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
        <div class="brand-glow pointer-events-none absolute -right-32 top-1/2 h-[480px] w-[480px] -translate-y-1/2" style="opacity:0.5"></div>
        <div class="relative mx-auto max-w-6xl px-5 py-20 sm:px-6 lg:py-28">
            <div class="max-w-3xl">
                <div class="reveal text-xs font-bold uppercase tracking-[0.2em] text-accent-300">Our human principle</div>
                <h2 class="reveal reveal-delay-1 mt-4 text-3xl font-extrabold leading-tight tracking-tight text-white sm:text-[44px]">
                    The patient should never be the messenger between hospitals.
                </h2>
                <p class="reveal reveal-delay-2 mt-5 max-w-xl text-base leading-relaxed text-brand-100">
                    Coordination is the infrastructure. BADBAADO moves the right information to the
                    right team at the right moment — so clinicians spend their attention on the
                    patient, not on chasing a fax or a phone call.
                </p>
                <div class="reveal reveal-delay-3 mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('features') }}" class="inline-flex items-center justify-center rounded-[10px] bg-white px-6 py-3 text-sm font-semibold text-brand-800 shadow-sm transition hover:bg-brand-50">Explore the capabilities</a>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-[10px] border border-white/25 px-6 py-3 text-sm font-semibold text-white transition hover:border-white/40 hover:bg-white/5">Try the demo console</a>
                </div>
            </div>
        </div>
    </section>

    {{-- CAPABILITIES --}}
    <section id="capabilities" class="mx-auto max-w-6xl px-5 py-20 sm:px-6 lg:py-28">
        <div class="grid items-start gap-4 md:grid-cols-2">
            @foreach ([
                ['Structured handover', 'Vitals, clinical picture, conditions and current interventions are captured in one fixed format — nothing important gets lost in transit.'],
                ['Emergency pre-alerts', 'Critical cases trigger an immediate pre-alert to the receiving team, so preparation begins before wheels turn.'],
                ['Role-aware access', 'Health care workers, coordinators and hospital admins each see exactly what their job requires — and nothing more.'],
                ['Audit-ready by default', 'Every creation, transition and decision is recorded for accountability across the entire referral lifecycle.'],
                ['Referral-scoped messaging', 'Clinicians discuss a case in one thread attached to that referral — context stays with the patient.'],
                ['AI-assisted urgency', 'An AI-assisted suggestion helps calibrate urgency. The clinical decision always remains human.'],
            ] as $cap)
            <div class="reveal card card-hover p-6">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600 ring-1 ring-brand-100">
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

    {{-- FINAL CTA --}}
    <section class="mx-auto max-w-6xl px-5 pb-24 sm:px-6">
        <div class="reveal mx-auto max-w-2xl text-center">
            <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="BADBAADO logo" class="mx-auto h-16 w-16 rounded-2xl shadow-float ring-1 ring-slate-200/70">
            <h2 class="mt-6 text-3xl font-extrabold tracking-tight text-brand-950 sm:text-[40px]">
                Connecting Hospitals, Connecting Care
            </h2>
            <p class="mt-4 text-lg text-slate-600">
                Explore the live console with demo accounts, or see how the full referral journey works.
            </p>
            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('login') }}" class="btn-primary px-6 py-3">Open the console</a>
                <a href="{{ route('about') }}" class="btn-secondary px-6 py-3">About BADBAADO</a>
            </div>
        </div>
    </section>
</main>
@endsection