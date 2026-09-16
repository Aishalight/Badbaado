@extends('layouts.public')

@section('title', 'How it works — BADBAADO')

@section('public')
<main>
    <section class="mx-auto max-w-6xl px-5 pb-8 pt-16 sm:px-6 lg:pt-20">
        <div class="max-w-3xl">
            <div class="reveal section-eyebrow">How it works</div>
            <h1 class="reveal reveal-delay-1 mt-4 text-4xl font-extrabold tracking-tight text-brand-950 sm:text-5xl">
                The referral lifecycle,<br>from decision to completion.
            </h1>
            <p class="reveal reveal-delay-2 mt-5 max-w-2xl text-lg leading-relaxed text-slate-600">
                Every BADBAADO referral moves through the same structured pipeline. Each step is
                visible to both hospitals, so everyone always knows where a patient stands — and
                what happens next.
            </p>
        </div>
    </section>

    {{-- LIFECYCLE --}}
    <section class="mx-auto max-w-4xl px-5 pb-20 pt-8 sm:px-6">
        <ol class="relative space-y-0">
            <span class="absolute left-[27px] top-4 bottom-4 w-px border-l border-dashed border-brand-200 sm:left-[33px]"></span>
            @foreach ([
                ['Draft', 'The referring clinician starts a structured referral: patient details, clinical picture, vital signs, and what is being done right now.', 'Created by the referring hospital'],
                ['Sent', 'The referral is transmitted to the destination hospital with the chosen urgency level. If flagged, an emergency pre-alert fires immediately.', 'Referring hospital → Receiving hospital'],
                ['Received', 'The receiving team acknowledges the referral. The countdown begins — teams are now preparing in parallel with transport.', 'Receiving hospital'],
                ['Under review', 'The receiving clinician reviews the full clinical picture and the urgency suggestion before making a decision.', 'Receiving hospital'],
                ['Accepted', 'The referral is accepted for admission. Bed, team and equipment are assigned, and both sides can see the plan.', 'Receiving hospital'],
                ['Transfer in progress', 'The patient is en route. Ambulance status, handover notes and any clinical updates travel on the same thread.', 'Both hospitals'],
                ['Arrived', 'The patient arrives and is received by the accepting team. Handover uses the same structured reference.', 'Receiving hospital'],
                ['Completed', 'Care is officially handed over and the referral is closed. Every action remains recorded for audit.', 'Closed'],
            ] as $i => $step)
            <li class="relative flex gap-5 pb-10 sm:gap-7">
                <span class="reveal relative z-10 flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white shadow-card ring-1 ring-brand-100 sm:h-16 sm:w-16">
                    <span class="text-sm font-extrabold tracking-wide text-brand-600">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                </span>
                <div class="reveal reveal-delay-1 pt-1 sm:pt-2">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-lg font-bold text-brand-950">{{ $step[0] }}</h2>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ $step[2] }}</span>
                    </div>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-slate-600">{{ $step[1] }}</p>
                </div>
            </li>
            @endforeach
        </ol>

        <div class="reveal mt-2 grid gap-4 sm:grid-cols-2">
            <div class="card p-5">
                <div class="flex items-center gap-2">
                    <span class="status-pill bg-red-50 text-red-600">Rejected</span>
                </div>
                <p class="mt-3 text-sm leading-relaxed text-slate-600">
                    If the receiving team cannot accept, the referral is declined with a clear reason
                    — so the referring team immediately knows why and can redirect without delay.
                </p>
            </div>
            <div class="card p-5">
                <div class="flex items-center gap-2">
                    <span class="status-pill bg-slate-100 text-slate-600">Cancelled</span>
                </div>
                <p class="mt-3 text-sm leading-relaxed text-slate-600">
                    A draft or sent referral can be cancelled by the referring hospital when the
                    situation changes before transfer begins.
                </p>
            </div>
        </div>
    </section>

    {{-- URGENCY BAND --}}
    <section class="border-y border-slate-200/70 bg-white">
        <div class="mx-auto max-w-6xl px-5 py-20 sm:px-6">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <div class="reveal section-eyebrow">Calibrating urgency</div>
                    <h2 class="reveal reveal-delay-1 mt-4 text-3xl font-extrabold tracking-tight text-brand-950">
                        Urgency is explicit,<br>never guessed twice.
                    </h2>
                    <p class="reveal reveal-delay-2 mt-4 max-w-lg leading-relaxed text-slate-600">
                        Every referral carries an urgency level that the receiving team sees instantly.
                        A suggestion helps calibrate it from the vitals and clinical picture — but the
                        decision is always the clinician’s, with far more authority than any AI.
                    </p>
                </div>
                <div class="reveal reveal-delay-1 grid grid-cols-2 gap-3">
                    @foreach ([
                        ['Critical', 'Life-threatening, immediate pre-alert', 'bg-red-50 text-red-600'],
                        ['High', 'Time-sensitive, prompt response', 'bg-orange-50 text-orange-600'],
                        ['Medium', 'Needs attention, not red-alert', 'bg-amber-50 text-amber-700'],
                        ['Normal', 'Routine referral pathway', 'bg-emerald-50 text-emerald-600'],
                    ] as $u)
                    <div class="rounded-xl border border-slate-100 p-4">
                        <span class="urgency-pill {{ $u[2] }}">{{ $u[0] }}</span>
                        <p class="mt-2.5 text-xs leading-relaxed text-slate-500">{{ $u[1] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ROLES --}}
    <section class="mx-auto max-w-6xl px-5 py-20 sm:px-6">
        <div class="mx-auto max-w-2xl text-center">
            <div class="reveal section-eyebrow">Who does what</div>
            <h2 class="reveal reveal-delay-1 mt-4 text-3xl font-extrabold tracking-tight text-brand-950 sm:text-4xl">
                Clear roles, clear access.
            </h2>
            <p class="reveal reveal-delay-2 mt-4 text-slate-600">
                BADBAADO is role-aware: every person sees exactly what their job requires, nothing more.
            </p>
        </div>
        <div class="reveal reveal-delay-1 mt-12 grid gap-6 md:grid-cols-3">
            @foreach ([
                ['Health care worker', 'Creates referrals, sends pre-alerts, and communicates with the receiving team from the ward.'],
                ['Referral coordinator', 'Owns the corridor: reviews incoming referrals, accepts or declines, and coordinates transfers.'],
                ['Hospital admin', 'Manages the hospital’s presence on the network and oversees every referral touching the facility.'],
            ] as $role)
            <div class="card card-hover p-6">
                <div class="h-1.5 w-10 rounded-full bg-gradient-to-r from-brand-500 to-accent-500"></div>
                <h3 class="mt-4 text-[17px] font-bold text-brand-950">{{ $role[0] }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $role[1] }}</p>
            </div>
            @endforeach
        </div>
        <div class="reveal mt-12 text-center">
            <a href="{{ route('features') }}" class="btn-primary px-6 py-3">See the full feature set</a>
        </div>
    </section>
</main>
@endsection