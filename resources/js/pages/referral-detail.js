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
    <div class="card p-6">
        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Progress</div>
        <div class="mt-4 flex flex-wrap items-center gap-1.5">
            ${FLOW.map((step) => {
                const state = stepState(referral.status, step);
                const wrapper = state === 'done' ? 'bg-brand-500 text-white'
                    : state === 'active' ? 'bg-accent-500 text-white ring-2 ring-accent-200'
                    : 'bg-slate-100 text-slate-500';
                return `<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold ${wrapper}">
                    <span class="h-1.5 w-1.5 rounded-full ${state === 'todo' ? 'bg-slate-300' : 'bg-white/80'}"></span>${step.replaceAll('_', ' ')}</span>`;
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

function formatBytes(bytes) {
    if (!bytes) return '';
    const units = ['B', 'KB', 'MB', 'GB'];
    const index = Math.min(units.length - 1, Math.floor(Math.log(bytes) / Math.log(1024)));
    return `${(bytes / 1024 ** index).toFixed(index ? 1 : 0)} ${units[index]}`;
}

function attachmentsBlock(attachments) {
    return `
    <div class="card p-6">
        <div class="flex items-center justify-between">
            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Attachments</div>
            <span class="text-xs text-slate-400">${attachments.length} file${attachments.length === 1 ? '' : 's'}</span>
        </div>
        <div class="mt-4 space-y-2">
            ${attachments.map((a) => `
                <button data-download-attachment="${a.id}" class="flex w-full items-center gap-3 rounded-[10px] border border-slate-200 bg-white px-3 py-2.5 text-left transition hover:border-brand-200 hover:bg-brand-50/40">
                    <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4 shrink-0 text-brand-500"><path d="M7 3h4l4 4v9a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-medium text-slate-700">${escapeHtml(a.original_name)}</span>
                        <span class="block text-xs text-slate-400">${formatBytes(a.size)} · ${formatDate(a.created_at)}</span>
                    </span>
                    <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4 shrink-0 text-slate-400"><path d="M10 3v9M10 12l-3-3M10 12l3-3M4 14v2a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>`).join('') || '<div class="text-sm text-slate-400">No files attached.</div>'}
        </div>
    </div>`;
}

function aiSuggestionBlock(referral) {
    if (!referral.ai_suggestion) return '';
    const ai = referral.ai_suggestion;
    return `
    <div class="card border-brand-100 bg-brand-50/60 p-5">
        <div class="flex items-center gap-2 text-sm font-bold text-brand-900">
            <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4 text-accent-600"><path d="M12 2a2 2 0 0 0-2 2v1H8v5H6V8H3l1 4h2v4l3 2v2h6v-5h2l1-4h-1V4l-5-2Z" fill="currentColor" opacity="0.9"/></svg>
            AI urgency suggestion
        </div>
        <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-brand-800">
            <span class="text-slate-500">Suggested</span>
            ${urgencyBadge(ai.urgency)}
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
            <div class="mt-5 border-t border-slate-200/70 pt-5">
                <div class="flex flex-wrap gap-2">
                    ${actions.map((action) => {
                        const toneClass = action.tone === 'danger' ? 'btn-danger'
                            : action.tone === 'ghost' ? 'btn-secondary'
                            : action.tone === 'orange' ? 'btn-secondary border-orange-200 bg-orange-50 text-orange-700 hover:bg-orange-100'
                            : 'btn-primary';
                        return `<button data-transition="${action.target}" class="transition-btn btn ${toneClass}">${action.label}</button>`;
                    }).join('')}
                </div>
                <div id="reject-reason-wrap" class="mt-3 hidden">
                    <textarea id="reject-reason" rows="2" placeholder="Reason for declining (required)…" class="input"></textarea>
                </div>
            </div>`;
        }

        container.innerHTML = `
            <div class="mx-auto max-w-5xl">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <button id="back-btn" class="btn btn-secondary btn-sm">← Back</button>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-sm font-bold text-brand-800">${escapeHtml(referral.referral_number)}</span>
                                ${statusBadge(referral.status)}
                                ${urgencyBadge(referral.urgency)}
                                ${referral.is_emergency ? '<span class="badge bg-red-50 text-red-600">● Pre-alert</span>' : ''}
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

                        <div class="card p-6">
                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Clinical picture</div>
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

                        <div class="card p-6">
                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Vitals</div>
                            <div class="mt-4">${vitalsBlock(referral.vitals)}</div>
                        </div>

                        ${attachmentsBlock(referral.attachments ?? [])}

                        ${referral.rejection_reason ? `
                        <div class="rounded-xl border border-red-100 bg-red-50 p-5">
                            <div class="text-sm font-bold text-red-700">Decline reason</div>
                            <div class="mt-1 text-sm text-red-800">${escapeHtml(referral.rejection_reason)}</div>
                        </div>` : ''}

                        ${actionForm}
                    </div>

                    <div class="space-y-4">
                        <div class="card p-6">
                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Patient</div>
                            <div class="mt-4 space-y-3">
                                ${infoRow('Name', escapeHtml(referral.patient?.name ?? '—'))}
                                ${infoRow('Reference', escapeHtml(referral.patient?.reference ?? '—'))}
                                ${infoRow('Age', escapeHtml(referral.patient?.age ?? '—'))}
                                ${infoRow('Gender', escapeHtml(referral.patient?.gender ?? '—'))}
                                ${infoRow('Blood group', escapeHtml(referral.patient?.blood_group ?? '—'))}
                            </div>
                        </div>

                        <div class="card p-6">
                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Referred by</div>
                            <div class="mt-4 space-y-3">
                                ${infoRow('Name', escapeHtml(referral.referring_user?.name ?? '—'))}
                                ${infoRow('Role', escapeHtml(referral.referring_user?.title ?? '—'))}
                                ${referral.coordinator ? infoRow('Coordinator', escapeHtml(referral.coordinator.name)) : ''}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 card p-6">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Discussion</div>
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
                        <input id="message-input" type="text" placeholder="Add a note for both hospitals…" class="input flex-1">
                        <button type="submit" class="btn btn-primary">Send</button>
                    </form>
                </div>
            </div>
        `;

        document.querySelector('#back-btn').addEventListener('click', () => {
            window.dispatchEvent(new CustomEvent('badbaado:navigate', { detail: 'referrals' }));
        });

        document.querySelectorAll('[data-download-attachment]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const id = btn.dataset.downloadAttachment;
                const attachment = (referral.attachments ?? []).find((a) => a.id === Number(id));
                btn.disabled = true;
                try {
                    const response = await api.download(`/referrals/${referral.id}/attachments/${id}`);
                    if (!response.ok) {
                        const data = await response.json().catch(() => null);
                        throw new Error(data?.message ?? 'Download failed.');
                    }
                    const blob = await response.blob();
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = attachment?.original_name ?? 'attachment';
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    URL.revokeObjectURL(url);
                } catch (error) {
                    renderToast(error.message, 'error');
                } finally {
                    btn.disabled = false;
                }
            });
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