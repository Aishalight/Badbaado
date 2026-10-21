{{--
Designed empty state component
--}}
@props([
    'icon' => 'inbox',
    'title' => 'No data yet',
    'description' => null,
    'action' => null,
    'actionLabel' => null,
    'class' => '',
    'padding' => 'py-12',
])

<div class="text-center {{ $padding }} {{ $class }}">
    <div class="inline-flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-4">
        <i data-lucide="{{ $icon }}" class="h-6 w-6 shrink-0"></i>
    </div>
    <h3 class="text-lg font-bold text-brand-950">{{ $title }}</h3>
    @if ($description)
        <p class="mt-2 max-w-xs mx-auto text-sm text-slate-500 leading-relaxed">{{ $description }}</p>
    @endif
    @if ($action && $actionLabel)
        <div class="mt-6">
            <x-btn variant="accent" class="px-6" href="{{ $action }}">{{ $actionLabel }}</x-btn>
        </div>
    @endif
</div>
