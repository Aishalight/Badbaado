@extends('layouts.console')

@php
    $descriptions = [
        'Platform' => 'Identity and public presentation of the platform.',
        'Contact' => 'How people reach the operations team from the public site.',
        'Access & security' => 'Authentication controls governing who can join and stay.',
        'Operations' => 'Runtime behaviour such as backup retention.',
    ];
@endphp

@section('title', 'System Configuration')
@section('heading', 'System Configuration')
@section('content')
<div class="space-y-6">
    <div>
        <p class="section-eyebrow">Platform settings</p>
        <h2 class="page-heading mt-2 text-3xl">System configuration.</h2>
        <p class="mt-2 max-w-2xl text-sm text-slate-500">Tunables stored in the database and read live. Each save is validated, authorized, and written to the audit log.</p>
    </div>

    <form class="grid gap-6 xl:grid-cols-2" data-settings-form>
        @foreach ($groups as $group => $settings)
            <section class="card p-6">
                <div><p class="section-eyebrow">Group</p><h3 class="dashboard-section-title mt-1.5">{{ $group }}</h3><p class="faint mt-1 text-xs">{{ $descriptions[$group] ?? 'Platform configuration.' }}</p></div>
                <div class="mt-4">
                    @foreach ($settings as $setting)
                        <div class="kv-row">
                            <div><label class="kv-row__key">{{ $setting->label }}</label><p class="kv-row__desc">{{ $setting->description }}</p></div>
                            @if ($setting->type === 'boolean')
                                <label class="flex items-center gap-2 text-sm shrink-0">
                                    <input type="checkbox" name="settings[{{ $setting->key }}]" value="1" data-setting="boolean" data-key="{{ $setting->key }}" @checked($setting->typedValue())>
                                    <span class="soft text-xs">{{ $setting->typedValue() ? 'Enabled' : 'Disabled' }}</span>
                                </label>
                            @elseif ($setting->type === 'integer')
                                <input class="input w-28 text-right" type="number" name="settings[{{ $setting->key }}]" value="{{ $setting->typedValue() }}" data-key="{{ $setting->key }}">
                            @else
                                <input class="input w-64 max-w-full text-right" type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->typedValue() }}" data-key="{{ $setting->key }}">
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
        <div class="xl:col-span-2 flex justify-end"><button class="btn-accent" type="submit">Save configuration</button></div>
    </form>
</div>
@endsection