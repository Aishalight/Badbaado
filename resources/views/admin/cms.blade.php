@extends('layouts.console')

@php
    $hero = $contents->where('section', 'hero')->values();
    $sections = $contents->where('section', 'sections')->values();
    $stats = $contents->where('section', 'stats')->values();
@endphp

@section('title', 'CMS & Public Content')
@section('heading', 'CMS & Public Content')
@section('content')
<div class="space-y-6">
    <div>
        <p class="section-eyebrow">Public website</p>
        <h2 class="page-heading mt-2 text-3xl">Content without touching code.</h2>
        <p class="mt-2 max-w-2xl text-sm text-slate-500">Edit the copy shown on the public landing page. Changes take effect immediately and are written to the audit log. Contact details are managed under <a href="{{ route('admin.config') }}" class="underline">System Configuration</a>.</p>
    </div>

    <div class="tab-pills">
        <button type="button" class="tab-pill is-active" data-admin-tab="hero">Hero</button>
        <button type="button" class="tab-pill" data-admin-tab="stats">Stats</button>
        <button type="button" class="tab-pill" data-admin-tab="announcements">Announcements</button>
        <button type="button" class="tab-pill" data-admin-tab="faqs">FAQs</button>
        <button type="button" class="tab-pill" data-admin-tab="sections">Section Labels</button>
    </div>

    <section data-admin-pane="hero" class="card grid gap-4 p-6 sm:grid-cols-2">
        @foreach ($hero as $content)
            <div class="sm:col-span-2">
                <label class="label">{{ $content->title }}</label>
                <textarea class="input mt-1 w-full" data-update-cms-field="{{ $content->id }}" rows="{{ str_contains($content->key, 'title') ? 3 : 2 }}">{{ $content->content }}</textarea>
                <div class="mt-1 flex items-center justify-between"><span class="helper">{{ $content->key }}</span><button class="btn-secondary btn-sm" data-update-cms="{{ $content->id }}" type="button">Save</button></div>
            </div>
        @endforeach
        <p class="sm:col-span-2 helper">The hero headline uses <span class="mono">|</span> to separate visual lines.</p>
    </section>

    <section data-admin-pane="stats" class="card grid gap-4 p-6 sm:grid-cols-2">
        @foreach ($stats as $content)
            <div>
                <label class="label">{{ $content->title }}</label>
                <input class="input mt-1 w-full" type="text" value="{{ $content->content }}" data-update-cms-field="{{ $content->id }}">
                <div class="mt-1 flex items-center justify-between"><span class="helper">{{ $content->key }}</span><button class="btn-secondary btn-sm" data-update-cms="{{ $content->id }}" type="button">Save</button></div>
            </div>
        @endforeach
    </section>

    <section data-admin-pane="announcements" class="card p-6 hidden">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div><p class="section-eyebrow">Publish</p><h3 class="dashboard-section-title mt-1.5">Announcements</h3></div>
            <form data-create-announcement class="grid gap-3 sm:grid-cols-[minmax(0,14rem)_minmax(0,1fr)_auto] items-start">
                <input class="input" name="title" placeholder="Title" required>
                <input class="input" name="body" placeholder="Message body" required>
                <button class="btn-accent btn-sm" type="submit">Publish</button>
            </form>
        </div>
        <div class="mt-5 divide-y divide-slate-100">
            @forelse ($announcements as $announcement)
                <div class="flex flex-wrap items-center justify-between gap-4 py-4">
                    <div class="min-w-0 flex-1">
                        <input class="input w-full" type="text" name="title" value="{{ $announcement->title }}" data-update-announcement-field="{{ $announcement->id }}">
                        <input class="input mt-2 w-full" type="text" name="body" value="{{ $announcement->body }}" data-update-announcement-field="{{ $announcement->id }}">
                        <p class="faint mt-1.5 text-xs">By {{ $announcement->author?->name ?? 'System' }} · {{ $announcement->published_at ? $announcement->published_at->format('d M Y H:i') : 'Inactive' }} · <label class="inline-flex items-center gap-1 ml-1 select-none"><input type="checkbox" name="is_active" data-announcement-active="{{ $announcement->id }}" @checked($announcement->is_active)> Visible</label></p>
                    </div>
                    <div class="flex gap-2">
                        <button class="btn-secondary btn-sm" data-update-announcement="{{ $announcement->id }}" type="button">Save</button>
                        <button class="btn-danger btn-sm" data-delete-announcement="{{ $announcement->id }}" type="button">Delete</button>
                    </div>
                </div>
            @empty
                <div class="empty-state"><p>No announcements yet. Publish the first one above.</p></div>
            @endforelse
        </div>
    </section>

    <section data-admin-pane="faqs" class="card p-6 hidden">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div><p class="section-eyebrow">Help centre</p><h3 class="dashboard-section-title mt-1.5">Frequently asked questions</h3></div>
            <form data-create-faq class="grid gap-3 sm:grid-cols-[minmax(0,12rem)_minmax(0,1fr)_auto] items-start">
                <input class="input" name="question" placeholder="Question" required>
                <input class="input" name="answer" placeholder="Answer" required>
                <button class="btn-accent btn-sm" type="submit">Add</button>
            </form>
        </div>
        <div class="mt-5 divide-y divide-slate-100">
            @forelse ($faqs as $faq)
                <div class="flex flex-wrap items-center justify-between gap-4 py-4">
                    <div class="min-w-0 flex-1">
                        <input class="input w-full" type="text" name="question" value="{{ $faq->question }}" data-update-faq-field="{{ $faq->id }}">
                        <input class="input mt-2 w-full" type="text" name="answer" value="{{ $faq->answer }}" data-update-faq-field="{{ $faq->id }}">
                        <p class="faint mt-1.5 text-xs">Order {{ $faq->sort_order }} · <label class="inline-flex items-center gap-1 ml-1 select-none"><input type="checkbox" name="is_active" data-faq-active="{{ $faq->id }}" @checked($faq->is_active)> Visible</label></p>
                    </div>
                    <div class="flex gap-2">
                        <button class="btn-secondary btn-sm" data-update-faq="{{ $faq->id }}" type="button">Save</button>
                        <button class="btn-danger btn-sm" data-delete-faq="{{ $faq->id }}" type="button">Delete</button>
                    </div>
                </div>
            @empty
                <div class="empty-state"><p>No FAQs yet. Add the first one above.</p></div>
            @endforelse
        </div>
    </section>

    <section data-admin-pane="sections" class="card grid gap-4 p-6 sm:grid-cols-2">
        <p class="sm:col-span-2 faint text-xs">These labels control the eyebrow text above each public section. Contact details (email, phone, address) are managed under System Configuration.</p>
        @foreach ($sections as $content)
            <div class="sm:col-span-2">
                <label class="label">{{ $content->title }}</label>
                <input class="input mt-1 w-full" type="text" value="{{ $content->content }}" data-update-cms-field="{{ $content->id }}">
                <div class="mt-1 flex items-center justify-between"><span class="helper">{{ $content->key }}</span><button class="btn-secondary btn-sm" data-update-cms="{{ $content->id }}" type="button">Save</button></div>
            </div>
        @endforeach
    </section>
</div>
@endsection