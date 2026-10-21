{{--
Generic pill badge
--}}
@props(['variant' => 'neutral', 'class' => '', 'pill' => true])

@php
    $variants = [
        'neutral' => 'bg-slate-100 text-slate-600',
        'brand'   => 'bg-brand-50 text-brand-700',
        'accent'  => 'bg-accent-50 text-accent-700',
        'critical'=> 'bg-red-50 text-red-600',
        'emergent'=> 'bg-orange-50 text-orange-600',
        'urgent'  => 'bg-amber-50 text-amber-700',
        'routine' => 'bg-emerald-50 text-emerald-600',
        'success' => 'bg-emerald-50 text-emerald-700',
        'warning' => 'bg-amber-50 text-amber-700',
        'danger'  => 'bg-red-50 text-red-600',
        'info'    => 'bg-accent-50 text-accent-700',
        'outline' => 'bg-transparent border border-slate-200 text-slate-600',
    ];
    $klass = $variants[$variant] ?? $variants['neutral'];
@endphp

<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide
    {{ $klass }}
    {{ $pill ? 'rounded-full' : 'rounded-[6px]' }}
    {{ $class }}">
    {{ $slot }}
</span>
