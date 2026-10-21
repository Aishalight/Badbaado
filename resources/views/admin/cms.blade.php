@extends('layouts.console')

@php
    $hero = $contents->where('section', 'hero')->values();
    $sections = $contents->where('section', 'sections')->values();
    $stats = $contents->where('section', 'stats')->values();
@endphp

@section('title', 'CMS & Public Content · BADBAADO')
@section('heading', 'Content management')

@section('content')
<div class="space-y-6">
    <x-page-header
        eyebrow="Public website management"
        title="Content without touching code"
        description="Edit copy, announcements, and FAQs shown on the public landing page. Changes take effect instantly and are recorded in the audit log."
    >
        <span class="badge bg-brand-50 text-brand-700">CMS</span>
    </x-page-header>

    <!-- Tab navigation -->
    <div class="flex flex-wrap gap-1 border-b border-slate-200 pb-2 mb-6">
        <button type="button" class="tab-pill is-active px-4 py-2.5 text-sm font-medium text-brand-700 bg-brand-50 border-b-2 border-brand-500 mb-[-1px]" data-admin-tab="hero">Hero copy</button>
        <button type="button" class="tab-pill px-4 py-2.5 text-sm font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition" data-admin-tab="stats">Key statistics</button>
        <button type="button" class="tab-pill px-4 py-2.5 text-sm font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition" data-admin-tab="announcements">Announcements</button>
        <button type="button" class="tab-pill px-4 py-2.5 text-sm font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition" data-admin-tab="faqs">FAQs</button>
        <button type="button" class="tab-pill px-4 py-2.5 text-sm font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition" data-admin-tab="sections">Section labels</button>
    </div>

    <!-- Hero copy pane -->
    <section data-admin-pane="hero" class="hidden">
        <x-section-header
            eyebrow="Landing page hero"
            title="Hero copy"
            description="Configure headline copy, subheadings, and action text. Use <span class='kbd'>|</span> to split visual text lines."
        />
        <div class="grid gap-6 sm:grid-cols-2">
            @foreach ($hero as $content)
                <x-card padding="p-5" class="sm:col-span-2">
                    <label class="label font-semibold text-slate-800 mb-2 block">{{ $content->title }}</label>
                    <textarea class="input w-full font-sans" data-update-cms-field="{{ $content->id }}" rows="{{ str_contains($content->key, 'title') ? 3 : 2 }}">{{ $content->content }}</textarea>
                    <div class="mt-4 flex items-center justify-between gap-3">
                        <span class="helper font-mono text-xs text-slate-400">key: {{ $content->key }}</span>
                        <x-btn variant="primary" size="sm" data-update-cms="{{ $content->id }}">Save changes</x-btn>
                    </div>
                </x-card>
            @endforeach
        </div>
    </section>

    <!-- Stats pane -->
    <section data-admin-pane="stats" class="hidden">
        <x-section-header
            eyebrow="Live statistics"
            title="Key statistics"
            description="Configure highlighted metrics displayed on the public landing page."
        />
        <div class="grid gap-5 sm:grid-cols-2">
            @foreach ($stats as $content)
                <x-card padding="p-5">
                    <label class="label font-semibold text-slate-800 mb-2 block">{{ $content->title }}</label>
                    <input class="input w-full" type="text" value="{{ $content->content }}" data-update-cms-field="{{ $content->id }}">
                    <div class="mt-4 flex items-center justify-between gap-3">
                        <span class="helper font-mono text-xs text-slate-400">key: {{ $content->key }}</span>
                        <x-btn variant="primary" size="sm" data-update-cms="{{ $content->id }}">Save</x-btn>
                    </div>
                </x-card>
            @endforeach
        </div>
    </section>

    <!-- Announcements pane -->
    <section data-admin-pane="announcements" class="hidden">
        <x-section-header
            eyebrow="Publish"
            title="Create public announcement"
            description="Share timely operational updates, advisory notes, or maintenance schedules with visitors."
        />
        <form data-create-announcement class="mt-5 grid gap-4 lg:max-w-3xl rounded-xl bg-slate-25/50 p-6 border border-slate-200">
            <div>
                <label class="label font-semibold" for="announcement-title">Announcement title</label>
                <input id="announcement-title" class="input mt-1 w-full" name="title" placeholder="e.g. National Referral Network Expansion" required>
            </div>
            <div>
                <label class="label font-semibold" for="announcement-body">Message body</label>
                <textarea id="announcement-body" class="input mt-1 min-h-32 w-full resize-y" name="body" rows="5" placeholder="Write the announcement details..." required></textarea>
            </div>
            <div class="flex justify-end pt-2">
                <x-btn variant="accent" type="submit">Publish announcement</x-btn>
            </div>
        </form>

        <div class="mt-8">
            <div class="flex items-baseline justify-between gap-4">
                <div>
                    <p class="section-eyebrow">Manage</p>
                    <h3 class="text-[17px] font-bold text-brand-950">Active announcements</h3>
                </div>
                <span class="badge bg-slate-100 text-slate-700">{{ $announcements->count() }} total</span>
            </div>
            <div class="mt-4 grid gap-4">
                @forelse ($announcements as $announcement)
                    <x-card padding="p-5">
                        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-start">
                            <div class="grid gap-4">
                                <div>
                                    <label class="label" for="announcement-{{ $announcement->id }}-title">Title</label>
                                    <input id="announcement-{{ $announcement->id }}-title" class="input mt-1 w-full font-semibold" type="text" name="title" value="{{ $announcement->title }}" data-update-announcement-field="{{ $announcement->id }}">
                                </div>
                                <div>
                                    <label class="label" for="announcement-{{ $announcement->id }}-body">Message</label>
                                    <textarea id="announcement-{{ $announcement->id }}-body" class="input mt-1 min-h-24 w-full resize-y" name="body" rows="3" data-update-announcement-field="{{ $announcement->id }}">{{ $announcement->body }}</textarea>
                                </div>
                                <div class="flex flex-wrap items-center justify-between gap-4 pt-1">
                                    <p class="text-xs text-slate-500">By {{ $announcement->author?->name ?? 'System' }} · {{ $announcement->published_at ? $announcement->published_at->format('d M Y H:i') : 'Inactive' }}</p>
                                    <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 select-none bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200">
                                        <input type="checkbox" name="is_active" data-announcement-active="{{ $announcement->id }}" @checked($announcement->is_active) class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"> Visible to visitors
                                    </label>
                                </div>
                            </div>
                            <div class="flex gap-2 lg:pt-6">
                                <x-btn variant="primary" size="sm" data-update-announcement="{{ $announcement->id }}">Save</x-btn>
                                <x-btn variant="danger" size="sm" data-delete-announcement="{{ $announcement->id }}">Delete</x-btn>
                            </div>
                        </div>
                    </x-card>
                @empty
                    <x-empty-state
                        icon="megaphone"
                        title="No announcements yet"
                        description="Publish the first announcement above."
                    />
                @endforelse
            </div>
        </div>
    </section>

    <!-- FAQs pane -->
    <section data-admin-pane="faqs" class="hidden">
        <x-section-header
            eyebrow="Help centre"
            title="Frequently asked questions"
            description="Add or update FAQ items to assist incoming hospitals and healthcare workers."
        />
        <form data-create-faq class="mt-5 grid gap-4 sm:grid-cols-[1fr_2fr_auto] items-start rounded-xl bg-slate-25/50 p-6 border border-slate-200">
            <div>
                <label class="label font-semibold">Question</label>
                <input class="input mt-1.5 w-full" name="question" placeholder="e.g. How are emergency referrals prioritized?" required>
            </div>
            <div>
                <label class="label font-semibold">Answer</label>
                <input class="input mt-1.5 w-full" name="answer" placeholder="Detailed answer..." required>
            </div>
            <div class="sm:pt-6">
                <x-btn variant="accent" type="submit" class="w-full sm:w-auto">Add FAQ</x-btn>
            </div>
        </form>
        <div class="mt-5 divide-y divide-slate-50">
            @forelse ($faqs as $faq)
                <div class="p-5 flex flex-wrap items-center justify-between gap-4">
                    <div class="min-w-0 flex-1 space-y-2">
                        <input class="input w-full font-semibold" type="text" name="question" value="{{ $faq->question }}" data-update-faq-field="{{ $faq->id }}">
                        <input class="input w-full text-slate-600" type="text" name="answer" value="{{ $faq->answer }}" data-update-faq-field="{{ $faq->id }}">
                        <p class="text-xs text-slate-400">Sort order: {{ $faq->sort_order }} · <label class="inline-flex items-center gap-1.5 ml-2 text-slate-700 select-none"><input type="checkbox" name="is_active" data-faq-active="{{ $faq->id }}" @checked($faq->is_active) class="rounded border-slate-300"> Visible</label></p>
                    </div>
                    <div class="flex gap-2">
                        <x-btn variant="primary" size="sm" data-update-faq="{{ $faq->id }}">Save</x-btn>
                        <x-btn variant="danger" size="sm" data-delete-faq="{{ $faq->id }}">Delete</x-btn>
                    </div>
                </div>
            @empty
                <x-empty-state
                    icon="file-text"
                    title="No FAQs recorded yet"
                    description="Add your first FAQ above."
                />
            @endforelse
        </div>
    </section>

    <!-- Section labels pane -->
    <section data-admin-pane="sections" class="hidden">
        <x-section-header
            eyebrow="Section labels"
            title="Section header labels"
            description="Control the eyebrow text and thematic badges shown above public landing page sections."
        />
        <div class="grid gap-5 sm:grid-cols-2">
            @foreach ($sections as $content)
                <x-card padding="p-5" class="sm:col-span-2">
                    <label class="label font-semibold text-slate-800 mb-2 block">{{ $content->title }}</label>
                    <input class="input w-full" type="text" value="{{ $content->content }}" data-update-cms-field="{{ $content->id }}">
                    <div class="mt-4 flex items-center justify-between gap-3">
                        <span class="helper font-mono text-xs text-slate-400">key: {{ $content->key }}</span>
                        <x-btn variant="primary" size="sm" data-update-cms="{{ $content->id }}">Save</x-btn>
                    </div>
                </x-card>
            @endforeach
        </div>
    </section>
</div>
@endsection
