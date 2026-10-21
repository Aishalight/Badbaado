{{--
Reusable card component following BADBAADO design system
--}}
@props(['class' => '', 'padding' => 'p-6', 'bordered' => true, 'shadow' => true, 'hover' => false])

@php
    $paddingKlass = $padding ? (is_string($padding) ? $padding : 'p-6') : '';
@endphp

<div class="rounded-[14px] border {{ $bordered ? 'border-slate-200/80' : 'border-transparent' }} bg-white
    {{ $shadow ? 'shadow-card' : '' }}
    {{ $hover ? 'card-hover transition-all duration-200 hover:-translate-y-0.5 hover:shadow-card-hover hover:border-brand-200' : '' }}
    {{ $paddingKlass }}
    {{ $class }}">
    {{ $slot }}
</div>
