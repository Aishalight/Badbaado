@extends('layouts.app')

@section('title', 'BADBAADO — Smart Inter-Hospital Referral Gateway')

@section('body')
<div class="min-h-screen bg-white">
    <header class="sticky top-0 z-40 border-b border-slate-100 bg-white/80 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
            <div class="flex items-center gap-2.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-500 font-black text-white">B</div>
                <span class="text-lg font-extrabold tracking-tight text-brand-950">BADBAADO</span>
            </div>
            <nav class="hidden items-center gap-8 text-sm font-medium text-slate-600 sm:flex">
                <a href="#how" class="transition hover:text-brand-700">How it works</a>
                <a href="#principle" class="transition hover:text-brand-700">Our principle</a>
                <a href="#about" class="transition hover:text-brand-700">About</a>
            </nav>
            <a href="{{ route('login') }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-600">
                Sign in
            </a>
        </div>
    </header>

    <main>
        <section class="mx-auto max-w-6xl px-4 pb-16 pt-16 sm:px-6 sm:pt-24">
            <div class="mx-auto max-w-3xl text-center">
                <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700 ring-1 ring-brand-100">
                    Inter-hospital referral coordination
                </span>
                <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-brand-950 sm:text-6xl">
                    Information arrives before the patient.
                </h1>
                <p class="mt-6 text-lg leading-relaxed text-slate-600">
                    BADBAADO connects referring and receiving hospitals through one calm,
                    structured channel — so the receiving team is prepared from the moment
                    the decision to transfer is made, not when the ambulance arrives.
                </p>
                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a href="{{ route('login') }}" class="w-full rounded-lg bg-brand-500 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-600 sm:w-auto">
                        Open the console
                    </a>
                    <a href="#how" class="w-full rounded-lg border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-brand-300 hover:text-brand-700 sm:w-auto">
                        See how it works
                    </a>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-4 pb-20 sm:px-6">
            <div class="grid gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4 sm:grid-cols-3 sm:gap-6 sm:p-8">
                <div class="rounded-xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="text-3xl font-extrabold text-brand-500">812</div>
                    <div class="mt-1 text-sm font-medium text-slate-500">Emegency pre-alerts triggered</div>
                </div>
                <div class="rounded-xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="text-3xl font-extrabold text-brand-500">41</div>
                    <div class="mt-1 text-sm font-medium text-slate-500">Hospitals on the network</div>
                </div>
                <div class="rounded-xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="text-3xl font-extrabold text-brand-500">98.6%</div>
                    <div class="mt-1 text-sm font-medium text-slate-500">Referrals with pre-planned transfer</div>
                </div>
            </div>
        </section>

        <section id="principle" class="border-y border-slate-100 bg-brand-950 py-16 sm:py-20">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="max-w-3xl">
                    <span class="text-xs font-bold uppercase tracking-widest text-brand-300">The principle</span>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                        No patient should ever be the messenger.
                    </h2>
                    <p class="mt-4 text-lg leading-relaxed text-slate-300">
                        In an emergency, every minute a referral is delayed — or worse, repeated
                        because information did not travel — is a minute of risk. BADBAADO makes
                        the referral itself the fixed record of truth, shared in real time.
                    </p>
                </div>
            </div>
        </section>

        <section id="how" class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20">
            <div class="mx-auto max-w-3xl text-center">
                <span class="text-xs font-bold uppercase tracking-widest text-brand-600">How it works</span>
                <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-brand-950 sm:text-4xl">
                    One structured path, from decision to arrival.
                </h2>
            </div>

            <div class="mt-12 grid gap-6 md:grid-cols-3">
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-sm font-extrabold text-brand-600">01</div>
                    <h3 class="mt-4 text-lg font-bold text-brand-950">Create the referral</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        A structured snapshot — patient, clinical picture, vital signs, and what is
                        being done right now. No phone tag, no repeated calls.
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-sm font-extrabold text-brand-600">02</div>
                    <h3 class="mt-4 text-lg font-bold text-brand-950">The receiving team reviews</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        The destination hospital acknowledges, reviews, accepts, or responds —
                        with the full clinical picture already in hand.
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-sm font-extrabold text-brand-600">03</div>
                    <h3 class="mt-4 text-lg font-bold text-brand-950">Transfers with preparation</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Transport, arrival, and handover are coordinated through the same thread —
                        everyone on both sides sees the same live status.
                    </p>
                </div>
            </div>
        </section>

        <section id="about" class="border-t border-slate-100 bg-slate-50 py-16">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="grid gap-8 md:grid-cols-2 md:items-center">
                    <div class="rounded-2xl border border-slate-100 bg-white p-8 shadow-sm">
                        <span class="text-xs font-bold uppercase tracking-widest text-brand-600">Built for the field</span>
                        <h2 class="mt-3 text-2xl font-extrabold tracking-tight text-brand-950">
                            Calm by design, structured to scale.
                        </h2>
                        <p class="mt-4 leading-relaxed text-slate-600">
                            BADBAADO is designed for real referral pathways — role-aware access for
                            health care workers, referral coordinators, and hospital administrators,
                            with every action recorded for accountability and audit.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                            <div class="text-4xl">⚙️</div>
                            <div class="mt-3 text-sm font-semibold text-brand-950">Role-aware access</div>
                            <p class="mt-1 text-xs text-slate-500">Every user sees exactly what their job requires.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                            <div class="text-4xl">🛡️</div>
                            <div class="mt-3 text-sm font-semibold text-brand-950">Audit-ready</div>
                            <p class="mt-1 text-xs text-slate-500">A complete record of every action taken.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                            <div class="text-4xl">📡</div>
                            <div class="mt-3 text-sm font-semibold text-brand-950">Real-time updates</div>
                            <p class="mt-1 text-xs text-slate-500">Status changes reach the right people instantly.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                            <div class="text-4xl">📱</div>
                            <div class="mt-3 text-sm font-semibold text-brand-950">Any device</div>
                            <p class="mt-1 text-xs text-slate-500">Works from a workstation or a ward tablet.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-100 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 py-8 text-sm text-slate-500 sm:flex-row sm:px-6">
            <div class="flex items-center gap-2">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-500 text-sm font-black text-white">B</div>
                <span class="font-bold text-brand-950">BADBAADO</span>
            </div>
            <p>&copy; {{ now()->year }} Inter-Hospital Referral Coordination. Demo build.</p>
        </div>
    </footer>
</div>
@endsection