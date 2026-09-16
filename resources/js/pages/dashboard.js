import { api } from '../lib/api';
import { escapeHtml, formatDate, statusBadge, urgencyBadge } from '../lib/format';

async function loadAllReferrals() {
    const result = await api.get('/referrals');
    return result.data;
}

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

function referralCard(referral) {
    const direction = referral.referring_hospital?.short_name ?? '?';
    const patient = referral.patient;
    return `
    <a href="#" data-referral-id="${referral.id}" class="referral-card card card-hover block p-4">
        <div class="flex items-start justify-between gap-2">
            <div class="flex items-center gap-2">
                ${urgencyBadge(referral.urgency)}
                ${referral.is_emergency ? '<span class="badge bg-red-50 text-red-600">● Pre-alert</span>' : ''}
            </div>
            ${statusBadge(referral.status)}
        </div>
        <div class="mt-3 text-sm font-semibold text-slate-800">${escapeHtml(patient?.name ?? 'Unknown patient')}</div>
        <div class="mt-1 text-xs text-slate-500">
            ${escapeHtml(patient?.age ?? '—')} · ${escapeHtml(patient?.gender ?? '—')} · ${escapeHtml(referral.department ?? '—')}
        </div>
        <div class="mt-3 flex items-center justify-between text-xs text-slate-400">
            <span class="truncate"><span class="font-medium text-slate-600">${escapeHtml(direction)}</span> → <span class="font-medium text-slate-600">${escapeHtml(referral.receiving_hospital?.short_name ?? '?')}</span></span>
            <span class="ml-2 shrink-0">${formatDate(referral.created_at)}</span>
        </div>
    </a>`;
}

export const dashboardPage = {
    async render(container) {
        const referrals = await loadAllReferrals();

        const active = referrals.filter((r) => !['completed', 'rejected', 'cancelled'].includes(r.status));
        const incoming = referrals.filter((r) =>
            ['sent', 'received', 'under_review'].includes(r.status) &&
            r.receiving_hospital?.id !== undefined,
        );
        const emergency = referrals.filter((r) => r.is_emergency && !['completed', 'rejected', 'cancelled'].includes(r.status));
        const accepted = referrals.filter((r) => ['accepted', 'transfer_in_progress', 'arrived'].includes(r.status));

        container.innerHTML = `
            <div class="mx-auto max-w-6xl">
                <div class="flex items-end justify-between">
                    <div>
                        <h1 class="text-xl font-extrabold tracking-tight text-brand-950">Referral overview</h1>
                        <p class="mt-1 text-sm text-slate-500">One calm view of everything moving across your hospitals.</p>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
                    ${statCard('Active referrals', active.length, 'neutral')}
                    ${statCard('Emergency pre-alerts', emergency.length, 'danger')}
                    ${statCard('Awaiting review', incoming.length, 'warn')}
                    ${statCard('Transfers underway', accepted.length, 'info')}
                </div>

                <div class="mt-8">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Recent activity</h2>
                        <a href="#" data-goto="referrals" class="text-sm font-medium text-brand-600 hover:text-brand-700">View all →</a>
                    </div>
                    <div class="mt-3 grid gap-3 lg:grid-cols-2">
                        ${referrals.slice(0, 8).map(referralCard).join('') || '<div class="text-sm text-slate-400">No referrals yet.</div>'}
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

        document.querySelectorAll('[data-goto="referrals"]').forEach((link) => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                window.dispatchEvent(new CustomEvent('badbaado:navigate', { detail: 'referrals' }));
            });
        });
    },
};