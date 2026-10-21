{{--
Activity box with see more / see less
--}}
@props([
    'events' => [], // AuditLog collection
    'limit' => 3,
    'title' => 'Referral activity',
    'subtitle' => null,
    'emptyMessage' => 'No activity recorded yet.',
])

@push('head')
<style>
    .activity-entry {
        transition: all 0.2s ease;
    }
    .activity-entry.hidden {
        display: none !important;
    }
</style>
@endpush

<div class="rounded-[14px] border border-slate-200/80 bg-white shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <p class="section-eyebrow">{{ $title }}</p>
            @if ($subtitle)
                <p class="mt-0.5 text-sm text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>
        <span class="badge bg-brand-50 text-brand-700">{{ count($events) }} events</span>
    </div>

    @if (count($events) === 0)
        <div class="px-6 py-8 text-center">
            <div class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-3">
                <i data-lucide="activity" class="h-5 w-5"></i>
            </div>
            <p class="text-sm text-slate-500">{{ $emptyMessage }}</p>
        </div>
    @else
        <div class="divide-y divide-slate-50" id="activity-list">
            @foreach ($events as $index => $event)
                <div class="activity-entry px-6 py-4 flex gap-3 {{ $index >= $limit ? 'hidden' : '' }}"
                     data-activity-index="{{ $index }}">
                    <div class="mt-1.5 flex h-2.5 w-2.5 shrink-0 items-center justify-center rounded-full bg-accent-500 ring-4 ring-accent-50/60"></div>
                    <div class="flex-1 rounded-xl bg-slate-50/50 p-3 border border-slate-100/60">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                            <p class="text-sm font-bold text-slate-800">{{ ucfirst(str_replace('_', ' ', $event->action ?? 'Event')) }}</p>
                            <span class="text-xs font-medium text-slate-400 tabular-nums">{{ $event->created_at?->format('M j, Y H:i') }}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-600 leading-relaxed">
                            Recorded by <span class="font-semibold text-slate-700">{{ $event->user?->name ?? 'System' }}</span>
                            @if ($event->user?->title) <span class="text-slate-400">({{ $event->user->title }})</span> @endif
                        </p>
                        @if ($event->metadata ?? null)
                            <div class="mt-2.5 text-[10px] font-mono text-slate-500 bg-white/40 p-2 rounded border border-slate-200/40">
                                {{ json_encode($event->metadata) }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if (count($events) > $limit)
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30 text-center">
                <button type="button" id="toggle-activity"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600 hover:text-brand-700 transition"
                    data-expanded="false">
                    <span>See more ({{ count($events) - $limit }} earlier events)</span>
                    <i data-lucide="chevron-down" class="h-4 w-4 shrink-0 transition-transform"></i>
                </button>
            </div>
        @endif
    @endif
</div>

@push('scripts')
<script>
    (function () {
        const btn = document.getElementById('toggle-activity');
        if (!btn) return;
        const entries = document.querySelectorAll('[data-activity-index]');
        const total = entries.length;
        const limit = {{ $limit }};
        const icon = btn.querySelector('[data-lucide="chevron-down"]');
        const label = btn.querySelector('span:first-child');
        const chevron = btn.querySelector('i');

        btn.addEventListener('click', () => {
            const expanded = btn.getAttribute('data-expanded') === 'true';
            entries.forEach((el, i) => {
                if (i >= limit) {
                    el.classList.toggle('hidden', expanded);
                }
            });
            btn.setAttribute('data-expanded', String(!expanded));
            if (icon) { lucide.createIcons?.(); }
            if (!expanded) {
                label.textContent = 'Show fewer events';
                chevron.classList.add('rotate-180');
            } else {
                label.textContent = 'See more (' + (total - limit) + ' earlier events)';
                chevron.classList.remove('rotate-180');
            }
        });
    })();
</script>
@endpush
