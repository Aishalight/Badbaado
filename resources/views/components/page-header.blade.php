{{--
Consistent page header for console pages
--}}
@props([
    'eyebrow' => null,
    'title' => null,
    'description' => null,
    'actions' => null,
    'back' => false,
    'backLabel' => 'Back',
    'backRoute' => null,
])

<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div class="min-w-0">
        @if ($back && $backRoute)
            <a href="{{ route($backRoute) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700 transition mb-2.5">
                <i data-lucide="arrow-left" class="h-3.5 w-3.5 shrink-0"></i> {{ $backLabel }}
            </a>
        @endif
        @if ($eyebrow)
            <p class="section-eyebrow">{{ $eyebrow }}</p>
        @endif
        @if ($title)
            <h2 class="page-heading mt-1 text-2xl font-extrabold tracking-tight text-brand-950">{{ $title }}</h2>
        @endif
        @if ($description)
            <p class="mt-2 max-w-2xl text-sm text-slate-500 leading-relaxed">{{ $description }}</p>
        @endif
    </div>
    <div class="flex flex-wrap gap-2">
        {{ $actions ?? '' }}
    </div>
</div>
