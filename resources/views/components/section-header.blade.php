{{--
Section header with optional actions
--}}
@props([
    'eyebrow' => null,
    'title' => null,
    'description' => null,
    'actions' => null,
    'divider' => true,
])

<div class="{{ $divider ? 'border-b border-slate-100 pb-4 mb-6' : 'mb-6' }}">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            @if ($eyebrow)
                <p class="section-eyebrow">{{ $eyebrow }}</p>
            @endif
            @if ($title)
                <h3 class="mt-1 text-[17px] font-bold text-brand-950 tracking-tight">{{ $title }}</h3>
            @endif
            @if ($description)
                <p class="mt-1 text-sm text-slate-500 leading-relaxed">{{ $description }}</p>
            @endif
        </div>
        @if ($actions)
            <div class="flex items-center gap-2 shrink-0">{{ $actions }}</div>
        @endif
    </div>
</div>
