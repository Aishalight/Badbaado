@extends('layouts.console')

@section('title', $referral->referral_number . ' · BADBAADO')
@section('heading', 'Referral details')

@section('content')
<div id="referral-page" data-referral-id="{{ $referral->id }}" data-referral-updated-at="{{ $referral->updated_at?->toISOString() }}" class="mx-auto max-w-7xl">
    <!-- Page header -->
    <x-page-header
        eyebrow="{{ $referral->referral_number }}"
        title="{{ $referral->patient?->full_name ?? 'Patient referral' }}"
        description="Created {{ $referral->created_at?->format('M j, Y \a\t H:i') }} · {{ $referral->department ?? 'General' }}"
        :back="true"
        back-route="referrals.index"
    >
        <div class="flex items-center gap-2">
            {!! \App\Support\ReferralFormat::urgencyPill($referral->urgency?->value) !!}
            {!! \App\Support\ReferralFormat::statusPill($referral->status?->value) !!}
        </div>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-[1.6fr_0.9fr]">
        <!-- Main column -->
        <div class="space-y-6">
            <!-- Clinical summary -->
            <section class="rounded-[16px] border border-slate-200/80 bg-white shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="section-eyebrow">Clinical summary</p>
                        <h3 class="text-[16px] font-bold text-brand-950">Patient & clinical picture</h3>
                    </div>
                </div>
                <div class="px-6 py-5 divide-y divide-slate-50">
                    <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Patient</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $referral->patient?->name ?? 'Not recorded' }}</p>
                            @if ($referral->patient?->age)
                                <p class="text-xs text-slate-500">{{ $referral->patient->age }} years</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Department</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $referral->department ?? 'Not specified' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Urgency</p>
                            {!! \App\Support\ReferralFormat::urgencyPill($referral->urgency?->value) !!}
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Emergency</p>
                            <span class="badge {{ $referral->is_emergency ? 'bg-red-50 text-red-600' : 'bg-slate-100 text-slate-600' }}">
                                {{ $referral->is_emergency ? 'Yes' : 'No' }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Reason for referral</p>
                            <p class="mt-1 whitespace-pre-line text-sm text-slate-700 leading-relaxed">{{ $referral->referral_reason ?: 'Not recorded' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Current symptoms</p>
                            <p class="mt-1 whitespace-pre-line text-sm text-slate-700 leading-relaxed">{{ $referral->symptoms ?: 'Not recorded' }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Current interventions</p>
                            <p class="mt-1 whitespace-pre-line text-sm text-slate-700 leading-relaxed">{{ $referral->current_interventions ?: 'Not recorded' }}</p>
                        </div>
                    </div>

                    @php($vitals = \App\Support\ReferralFormat::vitals($referral->vitals ?? []))
                    @if (count($vitals) > 0)
                        <div class="mt-5">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Vital signs</p>
                            <div class="flex gap-3">
                                @foreach ($vitals as $vital)
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 border border-slate-100">
                                        <span>{{ $vital['label'] }}:</span> <span class="text-brand-700">{{ $vital['value'] }}</span>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            <!-- Messages -->
            <section class="rounded-[16px] border border-slate-200/80 bg-white shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="section-eyebrow">Conversation</p>
                        <h3 class="text-[16px] font-bold text-brand-950">Referral messages</h3>
                    </div>
                    <span class="badge bg-slate-100 text-slate-600">{{ count($messages) }}</span>
                </div>
                <div class="px-6 py-4">
                    <div class="h-[300px] overflow-y-auto space-y-4 pr-2" id="message-list">
                        @forelse ($messages as $message)
                            <div class="rounded-xl p-3 {{ $message->sender_id === auth()->id() ? 'bg-brand-50 border border-brand-100 ml-auto max-w-[80%]' : 'bg-slate-50 border border-slate-100 mr-auto max-w-[80%]' }} {{ $message->sender_id === auth()->id() ? 'flex flex-row-reverse' : '' }}">
                                <div class="min-w-0 {{ $message->sender_id === auth()->id() ? 'ml-3' : 'mr-3' }} {{ $message->sender_id === auth()->id() ? 'text-right' : 'text-left' }}">
                                    <div class="flex items-center justify-between gap-2 mb-1 {{ $message->sender_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                                        <span class="text-xs font-bold {{ $message->sender_id === auth()->id() ? 'text-brand-700' : 'text-slate-600' }}">{{ $message->sender?->name ?? 'You' }}</span>
                                        <span class="text-[10px] text-slate-400">{{ $message->created_at?->format('M j, H:i') }}</span>
                                    </div>
                                    <p class="mt-0 text-sm text-slate-700 leading-relaxed">{{ $message->body }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <p class="text-sm text-slate-500">No messages yet.</p>
                                <p class="text-xs text-slate-400">Start the conversation about this referral.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                <form id="message-form" class="border-t border-slate-100 px-6 py-4 flex gap-3" method="POST" action="{{ route('referrals.messages.store', $referral) }}">
                    @csrf
                    <input name="body" class="input flex-1" placeholder="Write an update..." required>
                    <x-btn variant="primary" size="sm">Send</x-btn>
                </form>
            </section>
        </div>

        <!-- Sidebar -->
        <aside class="space-y-6">
            <!-- Referral route -->
            <x-hospital-route
                :from="$referral->referringHospital"
                :to="$referral->receivingHospital"
                department="{{ $referral->department }}"
                :intended-user="$referral->intendedUser"
            />

            <!-- Next actions -->
            <section class="rounded-[16px] border border-slate-200/80 bg-white shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <p class="section-eyebrow">Next action</p>
                    <h3 class="text-[16px] font-bold text-brand-950">Authorized actions</h3>
                </div>
                <div class="px-6 py-5 space-y-3">
                    @forelse($actions as $action)
                        <form method="POST" action="{{ route('referrals.transition', $referral) }}" class="transition-form" data-action="{{ $action['target'] }}">
                            @csrf
                            <input type="hidden" name="status" value="{{ $action['target'] }}">
                            @if ($action['tone'] === 'danger')
                                <input type="hidden" name="rejection_reason" value="">
                            @endif
                            <button type="submit" class="w-full btn btn-{{ $action['tone'] === 'danger' ? 'danger' : ($action['tone'] === 'secondary' ? 'secondary' : 'primary') }} text-left justify-between transition-form-btn">
                                <span>{{ $action['label'] }}</span>
                                <i data-lucide="arrow-right" class="h-4 w-4 shrink-0 opacity-50"></i>
                            </button>
                        </form>
                    @empty
                        <p class="text-sm text-slate-500 text-center">This referral is closed.</p>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>

    <!-- AI suggestion -->
    @if($referral->ai_suggestion)
        <x-ai-suggestion :suggestion="$referral->ai_suggestion" :human-decision="'Healthcare professional'" />
    @endif

    <!-- Activity box -->
    <section class="mt-8">
        <x-activity-box
            :events="$timeline"
            :limit="3"
            title="Referral activity"
            subtitle="Every transition and decision is recorded for accountability."
        />
    </section>
</div>

@push('scripts')
<script>
    (function () {
        // Transition forms: prompt for rejection reason when declining
        document.querySelectorAll('.transition-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                var reasonInput = form.querySelector('input[name="rejection_reason"]');
                var action = form.getAttribute('data-action');

                if (action === 'rejected' && reasonInput && !reasonInput.value.trim()) {
                    event.preventDefault();
                    var reason = prompt('Please enter a reason for declining this referral:');
                    if (reason === null || !reason.trim()) {
                        return;
                    }
                    reasonInput.value = reason;
                    form.submit();
                }
            });
        });

        // Message form: submit via AJAX and append message
        var messageForm = document.getElementById('message-form');
        var messageList = document.getElementById('message-list');
        if (messageForm && messageList) {
            messageForm.addEventListener('submit', function (event) {
                event.preventDefault();
                var bodyInput = messageForm.querySelector('input[name="body"]');
                var btn = messageForm.querySelector('button');
                var originalText = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = '<i data-lucide="loader-2" class="h-4 w-4 animate-spin"></i> Sending...';

                fetch(messageForm.getAttribute('action'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({ body: bodyInput.value }),
                })
                .then(function (response) {
                    if (!response.ok) return response.json().then(function (err) { throw err; });
                    return response.json();
                })
                .then(function () {
                    bodyInput.value = '';
                    var now = new Date();
                    var timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    var body = document.createElement('div');
                    body.textContent = document.querySelector('input[name="body"]').value || '';
                    var html = '<div class="rounded-xl p-3 bg-brand-50 border border-brand-100 ml-auto max-w-[80%] flex flex-row-reverse">' +
                        '<div class="min-w-0 ml-3 text-right">' +
                        '<div class="flex items-center justify-between gap-2 mb-1 flex-row-reverse">' +
                        '<span class="text-xs font-bold text-brand-700">You</span>' +
                        '<span class="text-[10px] text-slate-400">Just now</span>' +
                        '</div>' +
                        '<p class="mt-0 text-sm text-slate-700 leading-relaxed whitespace-pre-wrap">' + body.textContent + '</p>' +
                        '</div>' +
                        '</div>';
                    messageList.insertAdjacentHTML('beforeend', html);
                    lucide.createIcons();
                })
                .catch(function (error) {
                    alert('Failed to send message: ' + (error.message || 'unknown error'));
                })
                .finally(function () {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    lucide.createIcons();
                });
            });
        }
    })();
</script>
@endpush
@endsection
