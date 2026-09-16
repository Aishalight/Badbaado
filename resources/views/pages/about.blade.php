@extends('layouts.public')

@section('title', 'About — BADBAADO')

@section('public')
<main>
    <section class="mx-auto max-w-6xl px-5 pb-10 pt-16 sm:px-6 lg:pt-20">
        <div class="max-w-3xl">
            <div class="reveal section-eyebrow">About</div>
            <h1 class="reveal reveal-delay-1 mt-4 text-4xl font-extrabold tracking-tight text-brand-950 sm:text-5xl">
                Why the space between<br>hospitals deserves better.
            </h1>
            <p class="reveal reveal-delay-2 mt-5 max-w-2xl text-lg leading-relaxed text-slate-600">
                A referral is not just a document moving between facilities. It is a promise that
                the next care team will be ready. BADBAADO exists to make that promise reliable.
            </p>
        </div>
    </section>

    {{-- STORY --}}
    <section class="mx-auto max-w-6xl px-5 pb-24 pt-6 sm:px-6">
        <div class="relative overflow-hidden rounded-3xl border border-brand-100 bg-gradient-to-br from-brand-50 via-white to-accent-50 p-8 sm:p-12">
            <div class="brand-glow pointer-events-none absolute -bottom-32 -right-24 h-96 w-96 opacity-70"></div>
            <div class="relative max-w-2xl space-y-6">
                @foreach ([
                    ['The problem', 'Severe cases are still transferred between hospitals over phone calls, faxes, and paper. The clinical picture is repeated, fragmented, or lost — and the receiving team starts working blind.'],
                    ['The idea', 'What if the information travelled ahead of the patient? What if every referral was structured, every urgency was explicit, and every hospital could see the same live picture?'],
                    ['The build', 'That is what the BADBAADO team built: a coordinated referral and emergency pre-alert gateway — "Information arrives before the patient."'],
                ] as $i => $p)
                <div class="flex gap-4 sm:gap-6">
                    <span class="mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-xs font-extrabold text-brand-600 shadow-card ring-1 ring-brand-100">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <div>
                        <h2 class="text-[17px] font-bold text-brand-950">{{ $p[0] }}</h2>
                        <p class="mt-1.5 text-[15px] leading-relaxed text-slate-600">{{ $p[1] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- PRINCIPLE --}}
    <section id="principle" class="brand-gradient relative overflow-hidden">
        <div class="brand-glow pointer-events-none absolute -left-24 top-1/2 h-[420px] w-[420px] -translate-y-1/2" style="opacity:0.5"></div>
        <div class="relative mx-auto max-w-6xl px-5 py-20 sm:px-6 lg:py-28">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <div class="reveal text-xs font-bold uppercase tracking-[0.2em] text-accent-300">Our human principle</div>
                    <h2 class="reveal reveal-delay-1 mt-4 text-3xl font-extrabold leading-tight tracking-tight text-white sm:text-4xl">
                        The patient should not be the messenger between hospitals.
                    </h2>
                </div>
                <p class="reveal reveal-delay-2 text-base leading-relaxed text-brand-100">
                    Clinicians already carry enough. They should not have to carry the patient’s
                    story by hand, second-hand, from one building to the next. When the information
                    moves officially and instantly instead, the patient arrives to a team that is
                    already ready, already briefed, already waiting for them.
                </p>
            </div>
        </div>
    </section>

    {{-- TEAM --}}
    <section id="team" class="mx-auto max-w-6xl px-5 py-20 sm:px-6 lg:py-24">
        <div class="mx-auto max-w-2xl text-center">
            <div class="reveal section-eyebrow">Team</div>
            <h2 class="reveal reveal-delay-1 mt-4 text-3xl font-extrabold tracking-tight text-brand-950 sm:text-4xl">
                A small team building big infrastructure.
            </h2>
            <p class="reveal reveal-delay-2 mt-4 text-slate-600">
                A hackathon-born project built with full-stack precision: Laravel behind the API,
                a modern JavaScript console in front, and a design language built around one idea —
                connection.
            </p>
        </div>
        <div class="reveal reveal-delay-1 mx-auto mt-12 max-w-sm">
            <div class="card card-hover p-8 text-center">
                <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="BADBAADO logo" class="mx-auto h-16 w-16 rounded-2xl shadow-float ring-1 ring-slate-200/70">
                <h3 class="mt-5 text-lg font-extrabold text-brand-950">BADBAADO Team</h3>
                <p class="mt-1 text-sm text-slate-500">Platform engineers &amp; designers</p>
                <p class="mt-4 text-sm leading-relaxed text-slate-600">
                    Architecture and product driven by the people who will actually use it —
                    clinicians coordinating care across the country’s hospitals.
                </p>
                <div class="mt-5 flex items-center justify-center gap-2">
                    <span class="status-pill bg-brand-50 text-brand-700">Hackathon prototype</span>
                    <span class="status-pill bg-slate-100 text-slate-600">Built with Laravel</span>
                </div>
            </div>
        </div>
        <div class="reveal mt-12 text-center">
            <a href="{{ route('home') }}" class="btn-secondary px-6 py-3">← Back to home</a>
        </div>
    </section>
</main>
@endsection