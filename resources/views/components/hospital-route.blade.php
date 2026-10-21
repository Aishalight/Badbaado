{{--
Hospital-to-hospital route visual: From hospital -> To hospital
--}}
@props([
    'from' => null,
    'to' => null,
    'label' => 'Referral route',
    'department' => null,
    'intendedUser' => null,
])

<section class="rounded-[14px] border border-slate-200/80 bg-white shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
        <p class="section-eyebrow">{{ $label }}</p>
    </div>
    <div class="px-6 py-5 space-y-4">
        <div class="relative flex items-center gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-100 text-brand-700 border border-brand-200">
                <i data-lucide="hospital" class="h-4 w-4 shrink-0"></i>
            </span>
            <div class="flex-1 min-w-0 border-t border-slate-100 pt-3"></div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">From</p>
                <p class="font-semibold text-slate-800">{{ $from?->name ?? 'Referring hospital' }}</p>
                @if ($from?->short_name)
                    <p class="text-xs text-slate-500">{{ $from->short_name }}</p>
                @endif
            </div>
        </div>
        <div class="relative flex items-center gap-3 mt-2">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-accent-100 text-accent-700 border border-accent-200">
                <i data-lucide="{{ $to ? 'building-2' : 'user-round' }}" class="h-4 w-4 shrink-0"></i>
            </span>
            <div class="flex-1 min-w-0 border-t border-slate-100 pt-3"></div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">To</p>
                @if ($to)
                    <p class="font-semibold text-slate-800">{{ $to->name }}</p>
                    @if ($to->short_name)
                        <p class="text-xs text-slate-500">{{ $to->short_name }}</p>
                    @endif
                @elseif ($intendedUser)
                    <p class="font-semibold text-slate-800">{{ $intendedUser->name }}</p>
                    <p class="text-xs text-slate-500">Direct doctor referral</p>
                @else
                    <p class="font-semibold text-slate-800">Receiving hospital</p>
                @endif
            </div>
        </div>
        @if ($department || $intendedUser)
            <div class="grid gap-2 sm:grid-cols-2 border-t border-slate-50 pt-3">
                @if ($department)
                    <div class="flex items-center gap-2">
                        <i data-lucide="stethoscope" class="h-4 w-4 shrink-0 text-slate-400"></i>
                        <span class="text-sm text-slate-700"><span class="font-semibold">Department:</span> {{ $department }}</span>
                    </div>
                @endif
                @if ($intendedUser)
                    <div class="flex items-center gap-2">
                        <i data-lucide="user" class="h-4 w-4 shrink-0 text-slate-400"></i>
                        <span class="text-sm text-slate-700"><span class="font-semibold">Intended clinician:</span> {{ $intendedUser->name }}</span>
                        @if ($intendedUser->title)
                            <span class="text-xs text-slate-500 ml-1">{{ $intendedUser->title }}</span>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>
