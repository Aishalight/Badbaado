@php
    $size = $size ?? 'h-10 w-10';
    $rounded = $rounded ?? 'rounded-lg';
@endphp
@if ($hospital->logo_url)
    <img src="{{ $hospital->logo_url }}" alt="{{ $hospital->name }}" class="{{ $size }} shrink-0 {{ $rounded }} border border-slate-200 bg-white object-contain">
@else
    <span title="{{ $hospital->name }}" class="{{ $size }} flex shrink-0 items-center justify-center {{ $rounded }} bg-brand-100 text-xs font-bold text-brand-700">{{ $hospital->initials }}</span>
@endif
