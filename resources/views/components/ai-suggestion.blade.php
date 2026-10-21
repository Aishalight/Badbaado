{{--
AI-assisted urgency suggestion card (assistive, subordinate to human decision)
--}}
@props([
    'suggestion' => null, // ['urgency' => 'HIGH', 'confidence' => 0.87, 'reason' => '...']
    'humanDecision' => null,
])

@if (!$suggestion) @return @endif

@php
    $urgency = strtoupper($suggestion['urgency'] ?? '');
    $urgencyClasses = [
        'CRITICAL' => 'bg-red-50 text-red-600 border-red-100',
        'EMERGENT' => 'bg-orange-50 text-orange-600 border-orange-100',
        'URGENT'   => 'bg-amber-50 text-amber-700 border-amber-100',
        'ROUTINE'  => 'bg-emerald-50 text-emerald-600 border-emerald-100',
    ];
    $urgencyKlass = $urgencyClasses[$urgency] ?? 'bg-slate-100 text-slate-600 border-slate-200';
@endphp

<section class="rounded-[14px] border border-accent-200/60 bg-accent-50/40 shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-accent-200/40 flex items-start justify-between gap-4">
        <div class="flex-1">
            <p class="section-eyebrow text-accent-700">AI assistance only</p>
            <h3 class="mt-1 text-[16px] font-bold text-brand-950">AI-assisted urgency suggestion</h3>
        </div>
        <span class="urgency-pill inline-flex items-center gap-2 rounded-[6px] px-2.5 py-1.5 text-[11px] font-bold uppercase tracking-wide border
            {{ $urgencyKlass }}
        ">
            <span class="h-1.5 w-1.5 rounded-full bg-current shrink-0"></span>
            {{ $urgency }}
        </span>
    </div>
    <div class="px-6 py-5 space-y-3">
        <p class="text-sm text-slate-700 leading-relaxed">{{ $suggestion['reason'] ?? '' }}</p>
        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-slate-500">Confidence:</span>
            <span class="text-sm font-semibold text-brand-950">{{ number_format(($suggestion['confidence'] ?? 0) * 100) }}%</span>
        </div>
    </div>
    <div class="px-6 py-4 border-t border-accent-200/40 bg-white/30">
        <div class="flex items-center gap-2">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-600">
                <i data-lucide="user" class="h-3.5 w-3.5"></i>
            </span>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Final urgency decision</p>
                <p class="text-sm font-semibold text-slate-700">
                    {{ $humanDecision ?? 'Healthcare professional' }}
                </p>
                <p class="text-[10px] text-slate-500">The clinical decision remains with the healthcare professional.</p>
            </div>
        </div>
    </div>
</section>
