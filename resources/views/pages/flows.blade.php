@extends('layouts.public')

@section('title', 'Example flows · BADBAADO')

@section('public')
<main>
    <!-- HERO -->
    <section class="border-y border-slate-200/70">
        <div class="mx-auto max-w-6xl px-5 py-20 sm:px-6 lg:py-28">
            <div class="mx-auto max-w-2xl text-center">
                <div class="reveal section-eyebrow">Example flows</div>
                <h1 class="reveal reveal-delay-1 mt-4 text-3xl font-extrabold tracking-tight text-brand-950 sm:text-5xl">
                    See the referral journey
                </h1>
                <p class="reveal reveal-delay-2 mt-5 max-w-xl text-lg leading-relaxed text-slate-600">
                    Follow a real patient referral from decision to completion. Every step is
                    visible to both hospitals, so care never stalls at a handover.
                </p>
            </div>
        </div>
    </section>

    <!-- LIFECYCLE OVERVIEW -->
    <section class="mx-auto max-w-6xl px-5 py-20 sm:px-6 lg:py-28">
        <div class="grid items-start gap-8 md:grid-cols-2 lg:grid-cols-3">
            @php
                $stages = [
                    ['Draft', 'The referring clinician creates a structured referral.', 'bg-slate-50 text-slate-600 border-slate-200'],
                    ['Sent', 'Referral transmitted with urgency level.', 'bg-brand-50 text-brand-700 border-brand-100'],
                    ['Received', 'Receiving team acknowledges the referral.', 'bg-accent-50 text-accent-700 border-accent-100'],
                    ['Under review', 'Clinician reviews clinical picture and AI suggestion.', 'bg-amber-50 text-amber-700 border-amber-100'],
                    ['Accepted', 'Referral accepted for admission.', 'bg-emerald-50 text-emerald-700 border-emerald-100'],
                    ['Transfer in progress', 'Patient is en route.', 'bg-orange-50 text-orange-600 border-orange-100'],
                    ['Arrived', 'Patient received by accepting team.', 'bg-purple-50 text-purple-700 border-purple-100'],
                    ['Completed', 'Handover closed and audited.', 'bg-emerald-100 text-emerald-700 border-emerald-200'],
                ];
            @endphp
            @foreach ($stages as $i => $stage)
                <div class="reveal reveal-delay-{{ min($i + 1, 6) }} flex items-center gap-3 p-4 rounded-xl border bg-white shadow-sm group hover:border-brand-200/60 transition">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $stage[2] }} text-sm font-bold">
                        {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">{{ $stage[0] }}</p>
                        <p class="text-xs text-slate-500 leading-tight">{{ $stage[1] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- DEMO CASE SCENE -->
    <section class="border-y border-slate-200/70 bg-slate-25/50">
        <div class="mx-auto max-w-7xl px-5 py-20 sm:px-6 lg:py-32">
            <div class="grid items-start gap-12 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <div class="reveal section-eyebrow">Demo case</div>
                    <h2 class="reveal reveal-delay-1 mt-4 text-3xl font-extrabold tracking-tight text-brand-950">
                        From Al-Hilal to Shifa General
                    </h2>
                    <p class="reveal reveal-delay-2 mt-5 max-w-md text-lg leading-relaxed text-slate-600">
                        A 62-year-old patient with ST-elevation myocardial infarction needs
                        urgent cardiac care. Al-Hilal refers to Shifa General's cath lab.
                        Watch the information move before the patient does.
                    </p>
                    <div class="mt-8 grid gap-3">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-brand-50 border border-brand-100">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-brand-600">
                                <i data-lucide="user" class="h-4 w-4"></i>
                            </span>
                            <div>
                                <p class="text-sm font-bold text-slate-800">Mr. Hassan Ali</p>
                                <p class="text-xs text-slate-500">62 · Male · Patient ID: HA-8842</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-red-50 border border-red-100">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-red-600">
                                <i data-lucide="triangle-alert" class="h-4 w-4"></i>
                            </span>
                            <div>
                                <p class="text-sm font-bold text-red-800">Emergency pre-alert</p>
                                <p class="text-xs text-slate-500">Cath lab ready · ETA 18 min</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="reveal reveal-delay-1">
                        <!-- Connection visual -->
                        <div class="relative h-[300px] flex items-center justify-center">
                            <!-- Connection line -->
                            <div class="absolute top-1/2 left-1/2 w-[80%] h-1 bg-slate-200 -translate-x-1/2 -translate-y-1/2"></div>
                            <div class="absolute top-1/2 left-1/2 w-[80%] h-[3px] bg-gradient-to-r from-transparent via-accent-500 to-transparent -translate-x-1/2 -translate-y-1/2 animate-[flow-travel_2.4s_ease-out-quart_infinite]"></div>

                            <!-- From hospital node -->
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 flex flex-col items-center gap-2 z-10">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand-50 border-2 border-brand-200 shadow-card">
                                    <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="Al-Hilal" class="h-8 w-8 rounded-lg object-contain">
                                </div>
                                <p class="text-xs font-bold text-slate-700 text-center max-w-[140px]">Al-Hilal<br>Sending hospital</p>
                            </div>

                            <!-- To hospital node -->
                            <div class="absolute right-0 top-1/2 -translate-y-1/2 flex flex-col items-center gap-2 z-10">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-accent-50 border-2 border-accent-200 shadow-card">
                                    <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="Shifa General" class="h-8 w-8 rounded-lg object-contain">
                                </div>
                                <p class="text-xs font-bold text-slate-700 text-center max-w-[140px]">Shifa General<br>Receiving hospital</p>
                            </div>

                            <!-- Pulse dot -->
                            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-3 w-3 rounded-full bg-brand-500 shadow-[0_0_12px_rgba(24,90,157,0.6)] animate-[data-pulse_2s_ease-in-out_infinite]"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- STEP-BY-STEP FLOW -->
    <section class="mx-auto max-w-6xl px-5 py-20 sm:px-6 lg:py-28">
        <div class="mx-auto max-w-2xl text-center mb-12">
            <div class="reveal section-eyebrow">The journey, step by step</div>
            <h2 class="reveal reveal-delay-1 mt-4 text-3xl font-extrabold tracking-tight text-brand-950">
                One referral, eight states
            </h2>
        </div>

        <div class="reveal relative">
            <div class="absolute left-[28px] top-8 bottom-8 w-px border-l border-dashed border-brand-200 sm:left-[34px]"></div>

            @php
                $steps = [
                    [
                        'title' => 'Referral created (Draft)',
                        'time' => '10:04 AM',
                        'actor' => 'Dr. Sarah Chen · Al-Hilal',
                        'status' => 'draft',
                        'meta' => 'Structured referral opened: patient details, clinical picture, vitals captured.',
                    ],
                    [
                        'title' => 'Referral sent',
                        'time' => '10:06 AM',
                        'actor' => 'Dr. Sarah Chen · Al-Hilal',
                        'status' => 'sent',
                        'meta' => 'Transmitted to Shifa General. Emergency pre-alert fired to cath lab team.',
                    ],
                    [
                        'title' => 'Referral received',
                        'time' => '10:07 AM',
                        'actor' => 'Shifa General',
                        'status' => 'received',
                        'meta' => 'Receiving team acknowledged. Pre-alert acknowledged in 90 seconds.',
                    ],
                    [
                        'title' => 'Under review',
                        'time' => '10:12 AM',
                        'actor' => 'Dr. Marcus Reed · Shifa General',
                        'status' => 'under_review',
                        'meta' => 'AI-assisted urgency suggestion: HIGH (87% confidence). Clinician reviewing.',
                    ],
                    [
                        'title' => 'Referral accepted',
                        'time' => '10:18 AM',
                        'actor' => 'Dr. Marcus Reed · Shifa General',
                        'status' => 'accepted',
                        'meta' => 'Accepted for admission. Cath lab and cardiac team assigned.',
                    ],
                    [
                        'title' => 'Transfer in progress',
                        'time' => '10:30 AM',
                        'actor' => 'Ambulance crew',
                        'status' => 'transfer_in_progress',
                        'meta' => 'Patient en route. Ambulance ETA 18 minutes. Vitals shared live.',
                    ],
                    [
                        'title' => 'Patient arrived',
                        'time' => '10:48 AM',
                        'actor' => 'Shifa General',
                        'status' => 'arrived',
                        'meta' => 'Patient received by accepting team. Handover completed using structured reference.',
                    ],
                    [
                        'title' => 'Referral completed',
                        'time' => '11:15 AM',
                        'actor' => 'Dr. Marcus Reed · Shifa General',
                        'status' => 'completed',
                        'meta' => 'Handover closed. Every action recorded for audit.',
                    ],
                ];
            @endphp

            @foreach ($steps as $i => $step)
                <div class="relative flex gap-6 py-8 sm:py-10">
                    <!-- Connector line -->
                    @if ($i < count($steps) - 1)
                        <div class="absolute left-[28px] top-[64px] bottom-[40px] w-px border-l border-dashed border-slate-200 sm:left-[34px]"></div>
                    @endif

                    <!-- Node -->
                    <div class="relative z-10 flex-shrink-0">
                        <span class="absolute -inset-0.5 rounded-full ring-2 ring-brand-950/10"></span>
                        <span class="relative z-10 inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full border-2
                            {{ $step['status'] === 'completed' ? 'border-emerald-600 bg-emerald-600' : 'bg-white border-slate-300' }}
                            {{ $step['status'] === 'accepted' ? 'border-brand-600 bg-brand-500' : '' }}
                        ">
                            @if ($step['status'] === 'completed')
                                <i data-lucide="check" class="h-5 w-5 text-white"></i>
                            @elseif ($step['status'] === 'accepted')
                                <i data-lucide="check" class="h-5 w-5 text-white"></i>
                            @else
                                <span class="h-4 w-4 rounded-full bg-current"></span>
                            @endif
                        </span>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0 bg-white p-5 rounded-xl border border-slate-200/80 shadow-sm relative">
                        <div class="absolute -top-3 left-[28px] sm:left-[34px]">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white border border-slate-200 text-[10px] font-bold text-brand-600">
                                {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <h4 class="text-sm font-bold text-slate-800">{{ $step['title'] }}</h4>
                            <span class="text-xs font-medium text-slate-500">{{ $step['time'] }}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500 font-medium">{{ $step['actor'] }}</p>
                        <p class="mt-2.5 text-sm text-slate-700 leading-relaxed">{{ $step['meta'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- WHY THIS MATTERS -->
    <section class="brand-gradient relative overflow-hidden py-24">
        <div class="brand-glow pointer-events-none absolute -right-32 top-1/2 h-[400px] w-[400px] -translate-y-1/2"></div>
        <div class="relative mx-auto max-w-5xl px-5 py-12 sm:px-6 lg:py-20">
            <div class="max-w-2xl">
                <div class="reveal text-xs font-bold uppercase tracking-[0.2em] text-accent-300">The human principle</div>
                <h2 class="reveal reveal-delay-1 mt-4 text-3xl font-extrabold leading-tight tracking-tight text-white sm:text-4xl">
                    The patient should never be the messenger between hospitals.
                </h2>
                <p class="reveal reveal-delay-2 mt-5 max-w-xl text-lg leading-relaxed text-brand-100">
                    In this flow, Mr. Hassan's clinical information, vitals, and urgency moved
                    from Al-Hilal to Shifa General before the ambulance even left. The receiving
                    team was ready. No phone tag. No repeated stories. No lost paperwork.
                </p>
                <div class="reveal reveal-delay-3 mt-10 flex flex-col gap-3 sm:flex-row sm:justify-center">
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-[10px] bg-white px-7 py-3.5 text-sm font-bold text-brand-900 shadow-lg transition hover:bg-brand-50">Open the console</a>
                    <a href="{{ route('how-it-works') }}" class="btn-secondary px-7 py-3.5">Learn how it works</a>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
