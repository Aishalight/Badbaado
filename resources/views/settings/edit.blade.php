@extends('layouts.console')
@section('title', 'Settings')
@section('heading', 'Account settings')
@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div>
        <p class="section-eyebrow">Your account</p>
        <h2 class="page-heading mt-2 text-3xl">Settings</h2>
        <p class="mt-2 text-sm text-slate-500">Keep your profile and sign-in details current.</p>
    </div>

    @if (session('status'))
        <div class="rounded-[7px] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-[7px] border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">Please check the highlighted details.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="card p-6 sm:p-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div><h3 class="font-bold text-brand-950">Profile</h3><p class="mt-1 text-sm text-slate-500">This is how your care teams will identify you.</p></div>
            <div class="flex items-center gap-3">
                @if ($user->avatar_path)
                    <img src="{{ asset('storage/'.$user->avatar_path) }}" alt="{{ $user->name }}" class="h-14 w-14 rounded-full object-cover ring-2 ring-brand-100">
                @else
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-brand-50 text-lg font-bold text-brand-700">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                @endif
                <div><p class="text-sm font-semibold text-slate-800">{{ $user->name }}</p><p class="text-xs text-slate-500">{{ $user->role?->name }} · {{ $user->hospital?->short_name ?? 'Platform' }}</p></div>
            </div>
        </div>
        <form method="POST" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data" class="mt-6 grid gap-5 sm:grid-cols-2">
            @csrf @method('PATCH')
            <div><label class="label" for="name">Full name</label><input class="input mt-1" id="name" name="name" value="{{ old('name', $user->name) }}" required></div>
            <div><label class="label" for="email">Email address</label><input class="input mt-1" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required></div>
            <div><label class="label" for="title">Professional title</label><input class="input mt-1" id="title" name="title" value="{{ old('title', $user->title) }}" placeholder="e.g. Emergency physician"></div>
            <div><label class="label" for="phone">Phone</label><input class="input mt-1" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Optional contact number"></div>
            <div class="sm:col-span-2"><label class="label" for="avatar">Profile picture</label><input class="input mt-1 py-2" id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp"><p class="helper">JPG, PNG, or WebP up to 2 MB.</p></div>
            <div class="sm:col-span-2"><button class="btn-primary" type="submit">Save profile</button></div>
        </form>
    </section>

    <section class="card p-6 sm:p-8">
        <div><h3 class="font-bold text-brand-950">Password</h3><p class="mt-1 text-sm text-slate-500">Use a unique password you do not reuse elsewhere.</p></div>
        <form method="POST" action="{{ route('settings.password.update') }}" class="mt-6 grid gap-5 sm:max-w-xl">
            @csrf @method('PATCH')
            <div><label class="label" for="current_password">Current password</label><input class="input mt-1" id="current_password" name="current_password" type="password" required autocomplete="current-password"></div>
            <div><label class="label" for="password">New password</label><input class="input mt-1" id="password" name="password" type="password" required autocomplete="new-password"><p class="helper">At least 8 characters.</p></div>
            <div><label class="label" for="password_confirmation">Confirm new password</label><input class="input mt-1" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"></div>
            <div><button class="btn-secondary" type="submit">Update password</button></div>
        </form>
    </section>
</div>
@endsection
