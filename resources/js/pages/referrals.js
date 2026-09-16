import { api } from '../lib/api';
import { appShell } from '../app-shell';
import { escapeHtml, formatDate, statusBadge, urgencyBadge } from '../lib/format';

let currentTab = 'all';
let currentSearch = '';

function matchQuery(referral, query) {
    if (!query) return true;
    const haystack = [
        referral.referral_number,
        referral.department,
        referral.patient?.name,
        referral.patient?.reference,
        referral.referring_hospital?.short_name,
        referral.receiving_hospital?.short_name,
    ]
        .filter(Boolean)
        .join(' ')
        .toLowerCase();
    return haystack.includes(query.toLowerCase());
}

function referralRow(referral) {
    return `
    <a href="#" data-referral-id="${referral.id}" class="referral-card card card-hover grid grid-cols-2 items-center gap-2 p-4 sm:grid-cols-12">
        <div class="sm:col-span-2">
            <div class="font-mono text-xs font-bold text-slate-500">${escapeHtml(referral.referral_number)}</div>
        </div>
        <div class="sm:col-span-3">
            <div class="text-sm font-semibold text-slate-800">${escapeHtml(referral.patient?.name ?? 'Unknown')}</div>
            <div class="text-xs text-slate-400">${escapeHtml(referral.department ?? '—')}</div>
        </div>
        <div class="hidden sm:col-span-2 sm:block">
            <div class="text-xs text-slate-600">${escapeHtml(referral.referring_hospital?.short_name ?? '?')} → ${escapeHtml(referral.receiving_hospital?.short_name ?? '?')}</div>
        </div>
        <div class="hidden sm:col-span-2 sm:flex sm:items-center">${urgencyBadge(referral.urgency)}</div>
        <div class="hidden sm:col-span-2 sm:flex sm:items-center">${statusBadge(referral.status)}</div>
        <div class="hidden text-right text-xs text-slate-400 sm:col-span-1 sm:block">${formatDate(referral.created_at)}</div>
    </a>`;
}

export const referralsPage = {
    async render(container) {
        const result = await api.get('/referrals');
        const referrals = result.data;

        const applyFilters = () => {
            const me = appShell.getUser();
            const myHospital = me?.hospital?.id;

            const filtered = referrals.filter((r) => {
                if (currentTab === 'incoming' && !(myHospital && myHospital === r.receiving_hospital?.id)) return false;
                if (currentTab === 'outgoing' && !(myHospital && myHospital === r.referring_hospital?.id)) return false;
                if (currentTab === 'emergency' && !r.is_emergency) return false;
                return matchQuery(r, currentSearch);
            });

            document.querySelector('#referral-list').innerHTML = filtered.map(referralRow).join('')
                || '<div class="col-span-full py-10 text-center text-sm text-slate-400">No referrals match.</div>';

            document.querySelectorAll('.referral-card').forEach((card) => {
                card.addEventListener('click', (event) => {
                    event.preventDefault();
                    window.dispatchEvent(new CustomEvent('badbaado:open-referral', { detail: card.dataset.referralId }));
                });
            });
        };

        container.innerHTML = `
            <div class="mx-auto max-w-6xl">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h1 class="page-heading text-2xl">Referrals</h1>
                        <p class="mt-1 text-sm text-slate-500">${result.meta?.total ?? referrals.length} referrals across your visibility.</p>
                    </div>
                    <button id="new-referral-btn" class="btn-primary">
                        <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        New referral
                    </button>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex gap-1 rounded-[10px] border border-slate-200 bg-white p-1 text-sm shadow-sm">
                        <button class="tab-btn rounded-lg px-3 py-1.5 font-medium" data-tab="all">All</button>
                        <button class="tab-btn rounded-lg px-3 py-1.5 font-medium" data-tab="incoming">Incoming</button>
                        <button class="tab-btn rounded-lg px-3 py-1.5 font-medium" data-tab="outgoing">Outgoing</button>
                        <button class="tab-btn rounded-lg px-3 py-1.5 font-medium" data-tab="emergency">Emergency</button>
                    </div>
                    <input id="referral-search" type="search" placeholder="Search referrals…" class="input w-full sm:w-64">
                </div>

                <div id="referral-list" class="mt-4 space-y-2.5"></div>
            </div>
        `;

        document.querySelectorAll('.tab-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                currentTab = btn.dataset.tab;
                document.querySelectorAll('.tab-btn').forEach((b) => {
                    const active = b === btn;
                    b.classList.toggle('bg-brand-500', active);
                    b.classList.toggle('text-white', active);
                    b.classList.toggle('text-slate-600', !active);
                });
                applyFilters();
            });
        });

        document.querySelector('#referral-search').addEventListener('input', (event) => {
            currentSearch = event.target.value.trim();
            applyFilters();
        });

        document.querySelector('#new-referral-btn').addEventListener('click', () => {
            window.dispatchEvent(new CustomEvent('badbaado:navigate', { detail: 'new-referral' }));
        });

        document.querySelectorAll('.tab-btn').forEach((b) => {
            const active = b.dataset.tab === 'all';
            b.classList.toggle('bg-brand-500', active);
            b.classList.toggle('text-white', active);
            b.classList.toggle('text-slate-600', !active);
        });

        applyFilters();
    },
};