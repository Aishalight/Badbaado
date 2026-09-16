import { api } from '../lib/api';
import { escapeHtml, formatDate } from '../lib/format';

const ACTION_LABELS = {
    referral_created: 'Referral created',
    referral_attachments_added: 'Files attached',
    referral_sent: 'Referral sent',
    referral_acknowledged: 'Referral acknowledged',
    referral_review_started: 'Review started',
    referral_accepted: 'Referral accepted',
    referral_rejected: 'Referral declined',
    referral_transfer_started: 'Transfer started',
    referral_arrived: 'Patient arrived',
    referral_completed: 'Referral completed',
    referral_cancelled: 'Referral cancelled',
    user_created: 'User created',
    user_updated: 'User updated',
    hospital_created: 'Hospital registered',
    hospital_updated: 'Hospital updated',
};

const ACTION_TONES = {
    user_created: 'bg-emerald-50 text-emerald-700',
    user_updated: 'bg-sky-50 text-sky-700',
    hospital_created: 'bg-indigo-50 text-indigo-700',
    hospital_updated: 'bg-indigo-50 text-indigo-700',
    referral_rejected: 'bg-red-50 text-red-600',
};

function tone(action) {
    const map = {
        danger: 'bg-red-50 text-red-600',
        warn: 'bg-orange-50 text-orange-600',
        ok: 'bg-emerald-50 text-emerald-700',
        info: 'bg-sky-50 text-sky-700',
    };
    const byAction = ACTION_TONES[action];
    if (byAction) return byAction;
    if (action.startsWith('referral_rejected')) return map.danger;
    if (action.startsWith('referral_created') || action.startsWith('referral_sent')) return map.info;
    if (action.startsWith('referral_completed') || action.startsWith('referral_accepted')) return map.ok;
    if (action.startsWith('referral_arrived') || action.startsWith('referral_transfer')) return map.warn;
    return 'bg-slate-100 text-slate-600';
}

export const activityPage = {
    async render(container) {
        const logs = (await api.get('/admin/activity')).data;

        container.innerHTML = `
            <div class="mx-auto max-w-4xl">
                <div>
                    <h1 class="page-heading text-2xl">Activity log</h1>
                    <p class="mt-1 text-sm text-slate-500">Every meaningful action across the platform, in order.</p>
                </div>

                <div id="activity-feed" class="mt-6 space-y-2">
                    ${logs.map((log) => `
                        <div class="card flex items-start gap-3 p-4">
                            <span class="mt-0.5 h-2 w-2 shrink-0 rounded-full ${tone(log.action).split(' ')[0] === 'bg-red-50' ? 'bg-red-400' : tone(log.action).split(' ')[0] === 'bg-emerald-50' ? 'bg-emerald-400' : 'bg-sky-400'}"></span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="badge ${tone(log.action)}">${ACTION_LABELS[log.action] ?? escapeHtml(log.action.replaceAll('_', ' '))}</span>
                                    <span class="text-sm text-slate-700">${escapeHtml(log.user?.name ?? 'System')}${log.user?.title ? ` · ${escapeHtml(log.user.title)}` : ''}</span>
                                </div>
                                <div class="mt-1 text-xs text-slate-400">
                                    ${escapeHtml(log.entity_type ?? '')}${log.entity_id ? ` #${log.entity_id}` : ''}
                                    ${log.metadata?.referral_number ? ` · ${escapeHtml(log.metadata.referral_number)}` : ''}
                                    ${log.metadata?.role ? ` · ${escapeHtml(log.metadata.role)}` : ''}
                                    ${log.metadata?.hospital_id ? ` · hospital #${log.metadata.hospital_id}` : ''}
                                    ${log.ip_address ? ` · ${escapeHtml(log.ip_address)}` : ''}
                                </div>
                            </div>
                            <span class="shrink-0 text-xs text-slate-400">${formatDate(log.created_at)}</span>
                        </div>`).join('') || '<div class="text-sm text-slate-400">No activity recorded yet.</div>'}
                </div>
            </div>
        `;
    },
};