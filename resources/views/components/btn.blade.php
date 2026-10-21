{{--
Reusable button component
--}}
@props([
    'type' => 'button',
    'variant' => 'primary', // primary, accent, secondary, ghost, danger, link, outline
    'size' => 'md', // sm, md, lg
    'full' => false,
    'disabled' => false,
    'loading' => false,
    'class' => '',
    'href' => null,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-[10px] font-semibold transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60';
    $variants = [
        'primary'   => 'bg-brand-500 text-white shadow-sm hover:bg-brand-600 hover:shadow-md focus-visible:ring-brand-100 active:translate-y-px',
        'accent'    => 'bg-gradient-to-br from-brand-500 via-accent-500 to-accent-600 text-white shadow-sm hover:opacity-92 focus-visible:ring-accent-100 active:translate-y-px',
        'secondary' => 'border border-slate-200 bg-white text-slate-700 hover:border-brand-300 hover:text-brand-700 hover:shadow-sm focus-visible:ring-brand-100 active:translate-y-px',
        'ghost'     => 'text-slate-600 hover:bg-slate-50 hover:text-brand-700 focus-visible:ring-brand-100 active:translate-y-px',
        'danger'    => 'bg-red-500 text-white shadow-sm hover:bg-red-600 hover:shadow-md focus-visible:ring-red-100 active:translate-y-px',
        'outline'   => 'border border-slate-200 bg-transparent text-slate-700 hover:border-brand-400 hover:text-brand-700 focus-visible:ring-brand-100 active:translate-y-px',
        'link'      => 'bg-transparent text-brand-600 hover:text-brand-700 underline hover:underline underline-offset-4 focus-visible:ring-brand-100 px-0 py-0',
    ];
    $sizes = [
        'sm' => 'px-3 py-1.5 text-[13px]',
        'md' => 'px-4 py-2.5 text-sm',
        'lg' => 'px-6 py-3.5 text-base',
    ];
    $variantKlass = $variants[$variant] ?? $variants['primary'];
    $sizeKlass    = $sizes[$size] ?? $sizes['md'];
    $fullKlass    = $full ? 'w-full' : '';
    $tag = $href ? 'a' : 'button';
    $attrs = $href ? "href=\"{{ $href }}\"" : "type=\"{{ $type }}\"";
    if ($disabled) { $attrs .= ' disabled'; }
@endphp

<{{ $tag }} {{ $attrs }} {{ $attributes }} class="{{ $base }} {{ $variantKlass }} {{ $sizeKlass }} {{ $fullKlass }} {{ $class }}"{{ $disabled ? ' disabled' : '' }}>
    @if ($loading)
        <svg class="animate-spin h-4 w-4 text-current shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
    @endif
    {{ $slot }}
</{{ $tag }}>
