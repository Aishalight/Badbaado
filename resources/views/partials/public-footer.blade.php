<footer class="border-t border-slate-200/70 bg-white">
    <div class="mx-auto max-w-6xl px-5 py-14 sm:px-6">
        <div class="grid gap-10 md:grid-cols-12">
            <div class="md:col-span-5">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="BADBAADO logo" class="h-11 w-11 rounded-xl object-cover ring-1 ring-slate-200/70">
                    <span class="flex flex-col leading-none">
                        <span class="text-base font-extrabold tracking-tight text-brand-950">BADBAADO</span>
                        <span class="mt-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-brand-500">Connecting Hospitals</span>
                    </span>
                </div>
                <p class="mt-4 max-w-sm text-sm font-medium text-brand-800">
                    Information arrives before the patient.
                </p>
                <p class="mt-2 max-w-sm text-sm leading-relaxed text-slate-500">
                    A coordinated channel for inter-hospital referral, emergency pre-alert and safe
                    transfer — so care continues across hospitals, without interruption.
                </p>
            </div>

            <div class="md:col-span-2">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Product</div>
                <ul class="mt-4 space-y-2.5 text-sm text-slate-600">
                    <li><a href="{{ route('how-it-works') }}" class="transition hover:text-brand-700">How it works</a></li>
                    <li><a href="{{ route('features') }}" class="transition hover:text-brand-700">Features</a></li>
                    <li><a href="{{ route('features') }}#pre-alert" class="transition hover:text-brand-700">Pre-alerts</a></li>
                    <li><a href="{{ route('features') }}#security" class="transition hover:text-brand-700">Security &amp; trust</a></li>
                </ul>
            </div>

            <div class="md:col-span-2">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Company</div>
                <ul class="mt-4 space-y-2.5 text-sm text-slate-600">
                    <li><a href="{{ route('about') }}" class="transition hover:text-brand-700">About</a></li>
                    <li><a href="{{ route('about') }}#principle" class="transition hover:text-brand-700">Our principle</a></li>
                    <li><a href="{{ route('login') }}" class="transition hover:text-brand-700">Console</a></li>
                    <li><a href="{{ route('about') }}#team" class="transition hover:text-brand-700">Team</a></li>
                </ul>
            </div>

            <div class="md:col-span-3">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Statement</div>
                <div class="mt-4 rounded-xl border border-brand-100 bg-brand-50/60 p-4 text-sm leading-relaxed text-brand-900">
                    "The patient should not be the messenger between hospitals."
                </div>
            </div>
        </div>

        <div class="mt-12 flex flex-col items-center justify-between gap-3 border-t border-slate-100 pt-6 text-xs text-slate-400 sm:flex-row">
            <span>© {{ now()->year }} BADBAADO — Inter-hospital referral coordination. Demo build.</span>
            <span class="flex items-center gap-2">
                <span class="h-1.5 w-1.5 rounded-full bg-accent-500"></span>
                Built for the space between healthcare facilities
            </span>
        </div>
    </div>
</footer>