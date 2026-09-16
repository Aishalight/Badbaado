import { api } from '../lib/api';
import { appShell } from '../app-shell';
import { escapeHtml, formatDate, statusBadge, urgencyBadge } from '../lib/format';

function statCard(label, value, tone) {
    const tones = {
        danger: 'text-red-600',
        warn: 'text-orange-500',
        info: 'text-brand-600',
        ok: 'text-emerald-600',
        neutral: 'text-brand-950',
    };
    return `
    <div class="metric-card">
        <div class="flex items-center justify-between">
            <div class="metric-value ${tones[tone]}">${value}</div>
            <span class="hidden h-8 w-8 items-center justify-center rounded-[10px] bg-brand-50 text-brand-600 ring-1 ring-brand-100 sm:flex">
                <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4">
                    <path d="M3 10h14M3 10l4-4M3 10l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        </div>
        <div class="metric-label">${label}</div>
    </div>`;
}

function referralRow(referral) {
    return `
    <a href="#" data-referral-id="${referral.id}" class="referral-card card card-hover block p-4">
        <div class="flex items-start justify-between gap-2">
            <div class="flex items-center gap-2">
                ${urgencyBadge(referral.urgency)}
                ${referral.is_emergency ? '<span class="badge bg-red-50 text-red-600">● Pre-alert</span>' : ''}
            </div>
            ${statusBadge(referral.status)}
        </div>
        <div class="mt-3 text-sm font-semibold text-slate-800">${escapeHtml(referral.patient?.name ?? 'Unknown patient')}</div>
        <div class="mt-1 text-xs text-slate-500">
            ${escapeHtml(referral.patient?.age ?? '—')} · ${escapeHtml(referral.patient?.gender ?? '—')} · ${escapeHtml(referral.department ?? '—')}
        </div>
        <div class="mt-3 flex items-center justify-between text-xs text-slate-400">
            <span class="truncate"><span class="font-medium text-slate-600">${escapeHtml(referral.referring_hospital?.short_name ?? '?')}</span> → <span class="font-medium text-slate-600">${escapeHtml(referral.receiving_hospital?.short_name ?? '?')}</span></span>
            <span class="ml-2 shrink-0">${formatDate(referral.created_at)}</span>
        </div>
    </a>`;
}

function statusBar(statusCounts) {
    const total = Object.values(statusCounts).reduce((a, b) => a + (b || 0), 0);
    if (!total) return '<div class="text-sm text-slate-400">No referrals recorded yet.</div>';
    const order = ['draft', 'sent', 'received', 'under_review', 'accepted', 'transfer_in_progress', 'arrived', 'completed', 'rejected', 'cancelled'];
    const labels = { sent: 'sent', received: 'received', under_review: 'in review', transfer_in_progress: 'in transfer' };
    return `
    <div class="flex h-9 w-full overflow-hidden rounded-lg">
        ${order.filter((s) => statusCounts[s]).map((s) => `<div title="${s}: ${statusCounts[s]}" class="min-w-0" style="width:${(statusCounts[s] / total) * 100}%;background:${statusColor(s)}"></div>`).join('')}
    </div>
    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1.5 text-xs">
        ${order.filter((s) => statusCounts[s]).map((s) => `
            <span class="inline-flex items-center gap-1.5 text-slate-500">
                <span class="h-2 w-2 rounded-full" style="background:${statusColor(s)}"></span>
                ${labels[s] ?? s} · <b class="text-slate-700">${statusCounts[s]}</b>
            </span>`).join('')}
    </div>`;
}

function statusColor(status) {
    const map = {
        draft: '#64748B', sent: '#38BDF8', received: '#818CF8', under_review: '#F59E0B',
        accepted: '#10B981', transfer_in_progress: '#F97316', arrived: '#0EA5E9',
        completed: '#22C55E', rejected: '#EF4444', cancelled: '#94A3B8',
    };
    return map[status] ?? '#94A3B8';
}

function monthlyChart(monthly) {
    if (!monthly.length) return '<div class="text-sm text-slate-400">No history yet.</div>';
    const max = Math.max(...monthly.map((m) => m.count), 1);
    return `
    <div class="flex h-40 items-end gap-2">
        ${monthly.map((m) => `
            <div class="flex flex-1 flex-col items-center gap-1.5">
                <span class="text-[11px] font-semibold text-slate-500">${m.count}</span>
                <div class="w-full rounded-t-md bg-gradient-to-t from-brand-500 to-accent-400" style="height:${Math.max(4, (m.count / max) * 100)}%"></div>
                <span class="text-[10px] font-medium uppercase text-slate-400">${m.month}</span>
            </div>`).join('')}
    </div>`;
}

export const analyticsPage = {
    async render(container) {
        const me = appShell.getUser();
        const isSystem = me?.role?.slug === 'system_admin';
        const overview = (await api.get('/analytics/overview')).data;
        const referrals = (await api.get('/referrals')).data;

        const scopeLabel = isSystem ? 'All hospitals, nationwide referral flow.' : `${me?.hospital?.name ?? 'Your hospital'} — your referral flow.`;

        container.innerHTML = `
            <div class="mx-auto max-w-6xl">
                <div class="flex items-end justify-between">
                    <div>
                        <h1 class="page-heading text-2xl">Overview</h1>
                        <p class="mt-1 text-sm text-slate-500">${escapeHtml(scopeLabel)}</p>
                    </div>
                    ${isSystem ? `<span class="badge bg-brand-50 text-brand-700">${overview.total_hospitals} hospitals · ${overview.users_count} users</span>` : ''}
                </div>

                <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
                    ${statCard('Total referrals', overview.total_referrals, 'neutral')}
                    ${statCard('Emergency pre-alerts', overview.emergency_count, 'danger')}
                    ${statCard('Active transfers', overview.active_transfers, 'info')}
                    ${statCard('Staff users', overview.users_count, 'ok')}
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-2">
                    <div class="card p-6">
                        <div class="flex items-center justify-between">
                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Referral status</div>
                            <span class="text-xs text-slate-400">${overview.total_referrals} total</span>
                        </div>
                        <div class="mt-4">${statusBar(overview.status_counts)}</div>
                    </div>
                    <div class="card p-6">
                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Monthly referrals</div>
                        <div class="mt-6">${monthlyChart(overview.monthly)}</div>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-2">
                    <div class="card p-6">
                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Urgency mix</div>
                        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            ${(['critical', 'emergent', 'urgent', 'routine']).map((u) => `
                                <div class="rounded-lg bg-slate-50 p-3">
                                    <div class="text-lg font-bold ${u === 'critical' ? 'text-red-600' : u === 'emergent' ? 'text-orange-500' : u === 'urgent' ? 'text-amber-600' : 'text-emerald-600'}">${overview.urgency_counts[u] ?? 0}</div>
                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">${u}</div>
                                </div>`).join('')}
                        </div>
                    </div>
                    <div class="card p-6">
                        <div class="flex items-center justify-between">
                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Recent referrals</div>
                            <span class="text-xs text-slate-400">${referrals.length} shown</span>
                        </div>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            ${referrals.slice(0, 6).map(referralRow).join('') || '<div class="text-sm text-slate-400">No referrals yet.</div>'}
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.querySelectorAll('.referral-card').forEach((card) => {
            card.addEventListener('click', (event) => {
                event.preventDefault();
                window.dispatchEvent(new CustomEvent('badbaado:open-referral', { detail: card.dataset.referralId }));
            });
        });
    },
};