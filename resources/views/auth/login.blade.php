@extends('layouts.app')

@section('title', 'Sign in — BADBAADO')

@section('body')
<div class="flex min-h-full">
    <div class="hidden w-1/2 flex-col justify-between bg-brand-950 p-12 lg:flex">
        <div class="flex items-center gap-2.5">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-500 text-xl font-black text-white">B</div>
            <span class="text-xl font-extrabold tracking-tight text-white">BADBAADO</span>
        </div>
        <blockquote class="max-w-md">
            <p class="text-2xl font-bold leading-snug text-white">
                "Information arrives before the patient."
            </p>
            <footer class="mt-4 text-sm text-slate-400">
                The principle every referral is built around.
            </footer>
        </blockquote>
        <p class="text-sm text-slate-500">
            Inter-hospital referral & emergency pre-alert coordination.
        </p>
    </div>

    <div class="flex flex-1 items-center justify-center bg-slate-50 px-4 py-12">
        <div class="w-full max-w-md">
            <div class="mb-8 lg:hidden">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-500 text-xl font-black text-white">B</div>
                    <span class="text-xl font-extrabold tracking-tight text-brand-950">BADBAADO</span>
                </div>
            </div>

            <h1 class="text-2xl font-extrabold tracking-tight text-brand-950">Welcome back</h1>
            <p class="mt-1 text-sm text-slate-500">Sign in to the referral coordination console.</p>

            <form id="login-form" class="mt-8 space-y-5" novalidate>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email address</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        required
                        autocomplete="email"
                        placeholder="you@hospital.bd"
                        class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none"
                    >
                </div>

                <div id="login-error" class="hidden rounded-lg border border-red-100 bg-red-50 px-3.5 py-2.5 text-sm text-red-700"></div>

                <button
                    type="submit"
                    id="login-submit"
                    class="w-full rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    Sign in
                </button>
            </form>

            <div class="mt-6 rounded-xl border border-brand-100 bg-brand-50 px-4 py-3 text-xs leading-relaxed text-brand-800">
                <strong class="font-bold">Demo access</strong> — use
                <code class="rounded bg-white px-1 py-0.5 font-mono">admin@badbaado.bd</code> /
                <code class="rounded bg-white px-1 py-0.5 font-mono">password</code>
                (system admin), or any hospital account.
            </div>

            <p class="mt-8 text-center text-sm text-slate-500">
                <a href="/" class="font-medium text-brand-600 transition hover:text-brand-700">← Back to home</a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('head')
<script>
    window.BADBAADO = { loginOnly: true };
</script>
@endpush