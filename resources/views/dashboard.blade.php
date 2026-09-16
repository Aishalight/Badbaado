@extends('layouts.app')

@section('title', 'Dashboard — BADBAADO')

@section('body')
<div id="app" class="flex min-h-screen">
    <div id="boot-screen" class="flex flex-1 items-center justify-center bg-[#F6F8FB]">
        <div class="flex flex-col items-center">
            <div class="card relative flex h-20 w-20 items-center justify-center rounded-3xl shadow-float">
                <img src="{{ asset('images/badbaado-logo.jpg') }}" alt="BADBAADO logo" class="h-12 w-12 rounded-xl">
                <span class="absolute -bottom-1.5 -right-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 ring-4 ring-white">
                    <svg viewBox="0 0 20 20" fill="none" class="h-3.5 w-3.5 text-white">
                        <path d="M5 10l3 3 7-7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </div>
            <span class="mt-4 text-sm font-semibold text-brand-800">Loading BADBAADO…</span>
            <span class="mt-1 text-[11px] uppercase tracking-[0.16em] text-slate-400">Connecting Hospitals, Connecting Care</span>
        </div>
    </div>
</div>
@endsection

@push('head')
<script>
    window.BADBAADO = { app: true };
</script>
@endpush