import { api } from '../lib/api';
import { appShell } from '../app-shell';
import { escapeHtml, formatDate, formatVitals, statusBadge, urgencyBadge } from '../lib/format';
import { renderToast } from '../lib/toast';

const FLOW = ['draft', 'sent', 'received', 'under_review', 'accepted', 'transfer_in_progress', 'arrived', 'completed'];

function stepState(current, step) {
    const currentIndex = FLOW.indexOf(current);
    const stepIndex = FLOW.indexOf(step);
    if (currentIndex > stepIndex) return 'done';
    if (currentIndex === stepIndex) return 'active';
    return 'todo';
}

function timeline(referral) {
    return `
    <div class="rounded-xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="text-sm font-bold uppercase tracking-wide text-slate-500">Progress</div>
        <div class="mt-4 flex flex-wrap items-center gap-1.5">
            ${FLOW.map((step) => {
                const state = stepState(referral.status, step);
                const wrapper = state === 'done' ? 'bg-brand-500' : state === 'active' ? 'bg-brand-600 ring-2 ring-brand-200' : 'bg-slate-200';
                const text = state === 'todo' ? 'text-slate-500' : 'text-white';
                return `<span class="rounded-full px-2.5 py-1 text-[11px] font-bold ${wrapper} ${text}">${step.replaceAll('_', ' ')}</span>`;
            }).join('')}
        </div>
    </div>`;
}

function availableActions(referral, me) {
    const actions = [];
    const status = referral.status;

    if (status === 'draft') {
        if (me?.hospital?.id === referral.referring_hospital?.id) {
            actions.push({ target: 'sent', label: 'Send referral', tone: 'brand' });
            actions.push({ target: 'cancelled', label: 'Cancel', tone: 'ghost' });
        }
    }
    if (status === 'sent') {
        actions.push({ target: 'received', label: 'Acknowledge', tone: 'brand' });
    }
    if (status === 'received') {
        actions.push({ target: 'under_review', label: 'Start review', tone: 'brand' });
    }
    if (status === 'under_review') {
        actions.push({ target: 'accepted', label: 'Accept referral', tone: 'brand' });
        actions.push({ target: 'rejected', label: 'Decline', tone: 'danger' });
    }
    if (status === 'accepted') {
        actions.push({ target: 'transfer_in_progress', label: 'Transfer in progress', tone: 'brand' });
    }
    if (status === 'transfer_in_progress') {
        actions.push({ target: 'arrived', label: 'Patient arrived', tone: 'orange' });
    }
    if (status === 'arrived') {
        actions.push({ target: 'completed', label: 'Mark completed', tone: 'brand' });
    }

    return actions;
}

function vitalsBlock(vitals) {
    const rows = formatVitals(vitals);
    if (!rows.length) return '<div class="text-sm text-slate-400">No vitals recorded.</div>';
    return `<div class="grid grid-cols-2 gap-3 sm:grid-cols-5">${rows.map((row) => `
        <div class="rounded-lg bg-slate-50 p-3 text-center">
            <div class="text-[11px] font-semibold uppercase text-slate-400">${row.label}</div>
            <div class="mt-1 text-lg font-bold text-brand-800">${row.value}</div>
        </div>`).join('')}</div>`;
}

function infoRow(label, value) {
    return `
    <div>
        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">${label}</div>
        <div class="mt-1 text-sm text-slate-800">${value}</div>
    </div>`;
}

function aiSuggestionBlock(referral) {
    if (!referral.ai_suggestion) return '';
    const ai = referral.ai_suggestion;
    const suggestedTone = ai.urgency === 'critical' ? 'text-red-700' : ai.urgency === 'emergent' ? 'text-orange-700' : 'text-slate-700';
    return `
    <div class="rounded-xl border border-brand-100 bg-brand-50 p-5">
        <div class="flex items-center gap-2 text-sm font-bold text-brand-900">
            <span>✦</span> AI urgency suggestion
        </div>
        <div class="mt-2 text-sm text-brand-800">
            Suggested urgency <span class="font-bold ${suggestedTone}">${ai.urgency}</span>
            <span class="text-xs text-slate-500">· ${Math.round((ai.confidence ?? 0) * 100)}% confidence</span>
        </div>
        ${ai.reason ? `<div class="mt-1 text-xs text-slate-600">${escapeHtml(ai.reason)}</div>` : ''}
    </div>`;
}

export const referralDetailPage = {
    async render(container, id) {
        const me = appShell.getUser();
        const referral = (await api.get(`/referrals/${id}`)).data;
        const messages = (await api.get(`/referrals/${id}/messages`)).data;

        const actions = availableActions(referral, me);

        let actionForm = '';
        if (actions.length) {
            actionForm = `
            <div class="mt-5 border-t border-slate-100 pt-5">
                <div class="flex flex-wrap gap-2">
                    ${actions.map((action) => `
                        <button data-transition="${action.target}"
                            class="transition-btn rounded-lg px-4 py-2 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-50 ${
                                action.tone === 'danger' ? 'border border-red-200 bg-red-50 text-red-700 hover:bg-red-100'
                                : action.tone === 'ghost' ? 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
                                : action.tone === 'orange' ? 'border border-orange-200 bg-orange-50 text-orange-700 hover:bg-orange-100'
                                : 'border border-brand-300 bg-brand-500 text-white hover:bg-brand-600'}"
                        >${action.label}</button>`).join('')}
                </div>
                <div id="reject-reason-wrap" class="mt-3 hidden">
                    <textarea id="reject-reason" rows="2" placeholder="Reason for declining (required)…" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none"></textarea>
                </div>
            </div>`;
        }

        container.innerHTML = `
            <div class="mx-auto max-w-5xl">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <button id="back-btn" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">← Back</button>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-sm font-bold text-brand-800">${escapeHtml(referral.referral_number)}</span>
                                ${statusBadge(referral.status)}
                                ${urgencyBadge(referral.urgency)}
                                ${referral.is_emergency ? '<span class="rounded-full bg-red-100 px-2 py-1 text-[11px] font-bold text-red-700">PRE-ALERT</span>' : ''}
                            </div>
                            <div class="mt-1 text-sm text-slate-500">
                                ${escapeHtml(referral.referring_hospital?.name ?? '?')} → ${escapeHtml(referral.receiving_hospital?.name ?? '?')}
                            </div>
                        </div>
                    </div>
                    <div class="text-xs text-slate-400">Created ${formatDate(referral.created_at)}</div>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-3">
                    <div class="space-y-4 lg:col-span-2">
                        ${aiSuggestionBlock(referral)}
                        ${timeline(referral)}

                        <div class="rounded-xl border border-slate-100 bg-white p-6 shadow-sm">
                            <div class="text-sm font-bold uppercase tracking-wide text-slate-500">Clinical picture</div>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                ${infoRow('Department', escapeHtml(referral.department ?? '—'))}
                                ${infoRow('Consciousness', escapeHtml(referral.consciousness ?? '—'))}
                                ${infoRow('Trauma indicator', referral.trauma_indicator ? '<span class="font-bold text-red-600">Yes</span>' : '<span class="text-slate-600">No</span>')}
                                ${infoRow('Referral reason', escapeHtml(referral.referral_reason ?? '—'))}
                            </div>
                            ${referral.symptoms ? `<div class="mt-4">${infoRow('Symptoms', escapeHtml(referral.symptoms))}</div>` : ''}
                            ${referral.existing_conditions ? `<div class="mt-4">${infoRow('Existing conditions', escapeHtml(referral.existing_conditions))}</div>` : ''}
                            ${referral.current_interventions ? `<div class="mt-4">${infoRow('Current interventions', escapeHtml(referral.current_interventions))}</div>` : ''}
                            ${referral.notes ? `<div class="mt-4">${infoRow('Notes', escapeHtml(referral.notes))}</div>` : ''}
                        </div>

                        <div class="rounded-xl border border-slate-100 bg-white p-6 shadow-sm">
                            <div class="text-sm font-bold uppercase tracking-wide text-slate-500">Vitals</div>
                            <div class="mt-4">${vitalsBlock(referral.vitals)}</div>
                        </div>

                        ${referral.rejection_reason ? `
                        <div class="rounded-xl border border-red-100 bg-red-50 p-5">
                            <div class="text-sm font-bold text-red-700">Decline reason</div>
                            <div class="mt-1 text-sm text-red-800">${escapeHtml(referral.rejection_reason)}</div>
                        </div>` : ''}

                        ${actionForm}
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-xl border border-slate-100 bg-white p-6 shadow-sm">
                            <div class="text-sm font-bold uppercase tracking-wide text-slate-500">Patient</div>
                            <div class="mt-4 space-y-3">
                                ${infoRow('Name', escapeHtml(referral.patient?.name ?? '—'))}
                                ${infoRow('Reference', escapeHtml(referral.patient?.reference ?? '—'))}
                                ${infoRow('Age', escapeHtml(referral.patient?.age ?? '—'))}
                                ${infoRow('Gender', escapeHtml(referral.patient?.gender ?? '—'))}
                                ${infoRow('Blood group', escapeHtml(referral.patient?.blood_group ?? '—'))}
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-100 bg-white p-6 shadow-sm">
                            <div class="text-sm font-bold uppercase tracking-wide text-slate-500">Referred by</div>
                            <div class="mt-4 space-y-3">
                                ${infoRow('Name', escapeHtml(referral.referring_user?.name ?? '—'))}
                                ${infoRow('Role', escapeHtml(referral.referring_user?.title ?? '—'))}
                                ${referral.coordinator ? infoRow('Coordinator', escapeHtml(referral.coordinator.name)) : ''}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 rounded-xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-bold uppercase tracking-wide text-slate-500">Discussion</div>
                        <span class="text-xs text-slate-400">${messages.length} messages</span>
                    </div>
                    <div id="message-thread" class="mt-4 space-y-3">
                        ${messages.map((m) => `
                            <div class="flex gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-700">${escapeHtml((m.sender?.name ?? '?')[0])}</div>
                                <div class="min-w-0">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-sm font-semibold text-slate-800">${escapeHtml(m.sender?.name ?? 'Unknown')}</span>
                                        <span class="text-[11px] text-slate-400">${formatDate(m.created_at)}</span>
                                    </div>
                                    <p class="mt-0.5 text-sm text-slate-600">${escapeHtml(m.body)}</p>
                                </div>
                            </div>`).join('') || '<div class="text-sm text-slate-400">No messages yet.</div>'}
                    </div>
                    <form id="message-form" class="mt-4 flex gap-2">
                        <input id="message-input" type="text" placeholder="Add a note for both hospitals…" class="flex-1 rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">
                        <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-600">Send</button>
                    </form>
                </div>
            </div>
        `;

        document.querySelector('#back-btn').addEventListener('click', () => {
            window.dispatchEvent(new CustomEvent('badbaado:navigate', { detail: 'referrals' }));
        });

        document.querySelectorAll('.transition-btn').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const target = btn.dataset.transition;
                const rejectWrap = document.querySelector('#reject-reason-wrap');
                if (target === 'rejected') {
                    rejectWrap?.classList.toggle('hidden');
                    return;
                }
                if (rejectWrap && !rejectWrap.classList.contains('hidden') && target !== 'rejected') {
                    rejectWrap.classList.add('hidden');
                }

                const body = { status: target };
                if (target === 'rejected') {
                    const reason = document.querySelector('#reject-reason').value.trim();
                    if (!reason) {
                        renderToast('Please provide a reason for declining.', 'error');
                        rejectWrap?.classList.remove('hidden');
                        return;
                    }
                    body.rejection_reason = reason;
                }

                btn.disabled = true;
                try {
                    await api.post(`/referrals/${referral.id}/transition`, body);
                    renderToast('Referral updated.');
                    referralDetailPage.render(container, id);
                } catch (error) {
                    renderToast(error.message, 'error');
                    btn.disabled = false;
                }
            });
        });

        document.querySelector('#message-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const input = document.querySelector('#message-input');
            const body = input.value.trim();
            if (!body) return;

            try {
                await api.post(`/referrals/${referral.id}/messages`, { body });
                input.value = '';
                renderToast('Message sent.');
                const thread = document.querySelector('#message-thread');
                const msg = (await api.get(`/referrals/${id}/messages`)).data;
                thread.innerHTML = msg.map((m) => `
                    <div class="flex gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-700">${escapeHtml((m.sender?.name ?? '?')[0])}</div>
                        <div class="min-w-0">
                            <div class="flex items-baseline gap-2">
                                <span class="text-sm font-semibold text-slate-800">${escapeHtml(m.sender?.name ?? 'Unknown')}</span>
                                <span class="text-[11px] text-slate-400">${formatDate(m.created_at)}</span>
                            </div>
                            <p class="mt-0.5 text-sm text-slate-600">${escapeHtml(m.body)}</p>
                        </div>
                    </div>`).join('');
            } catch (error) {
                renderToast(error.message, 'error');
            }
        });
    },
};