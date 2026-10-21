{{--
Metric / stat card
--}}
@props([
    'label' => null,
    'value' => null,
    'note' => null,
    'icon' => null,
    'variant' => 'blue', // blue, cyan, red, amber, violet, brand
    'trend' => null,
])

@php
    $colors = [
        'blue'   => ['icon' => 'text-brand-500', 'bg' => 'bg-brand-50', 'border' => 'border-brand-100'],
        'cyan'   => ['icon' => 'text-accent-500', 'bg' => 'bg-accent-50', 'border' => 'border-accent-100'],
        'red'    => ['icon' => 'text-red-500', 'bg' => 'bg-red-50', 'border' => 'border-red-100'],
        'amber'  => ['icon' => 'text-amber-500', 'bg' => 'bg-amber-50', 'border' => 'border-amber-100'],
        'violet' => ['icon' => 'text-violet-500', 'bg' => 'bg-violet-50', 'border' => 'border-violet-100'],
        'brand'  => ['icon' => 'text-brand-600', 'bg' => 'bg-brand-50', 'border' => 'border-brand-100'],
    ];
    $color = $colors[$variant] ?? $colors['blue'];
    $caption = $note ?? $trend;
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-[16px] border border-slate-200/80 bg-white shadow-card p-6']) }}>
    <div class="flex items-start justify-between gap-3">
        @if ($icon)
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $color['bg'] }} {{ $color['icon'] }} border {{ $color['border'] }}">
                <i data-lucide="{{ $icon }}" class="h-5 w-5 shrink-0" aria-hidden="true"></i>
            </span>
        @endif
        @if ($caption)
            <span class="text-xs font-semibold text-slate-500">{{ $caption }}</span>
        @endif
    </div>
    <p class="dashboard-stat__value mt-5">{{ $value }}</p><p class="dashboard-stat__label">{{ $label }}</p>
</div>
