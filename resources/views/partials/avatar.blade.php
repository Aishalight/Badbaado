@php
    $size = $size ?? 'h-10 w-10';
    $rounded = $rounded ?? 'rounded-full';
@endphp
@if ($user->avatar_url)
    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="{{ $size }} shrink-0 {{ $rounded }} bg-slate-100 object-cover">
@else
    <span title="{{ $user->name }}" class="{{ $size }} flex shrink-0 items-center justify-center {{ $rounded }} bg-brand-100 text-xs font-bold text-brand-700">{{ $user->initials }}</span>
@endif
