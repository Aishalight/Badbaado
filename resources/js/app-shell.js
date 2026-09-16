import { api, clearToken } from './lib/api';
import { escapeHtml } from './lib/format';
import { dashboardPage } from './pages/dashboard';
import { referralsPage } from './pages/referrals';
import { referralDetailPage } from './pages/referral-detail';
import { newReferralPage } from './pages/new-referral';
import { notificationsPage } from './pages/notifications';
import { analyticsPage } from './pages/analytics';
import { usersPage } from './pages/users';
import { hospitalsPage } from './pages/hospitals';
import { activityPage } from './pages/activity';

let currentUser = null;
let currentView = 'dashboard';

const NAV = {
    healthcare_worker: ['dashboard', 'referrals', 'new-referral', 'notifications'],
    referral_coordinator: ['dashboard', 'referrals', 'new-referral', 'notifications'],
    hospital_admin: ['analytics', 'users', 'referrals', 'notifications'],
    system_admin: ['analytics', 'hospitals', 'users', 'activity', 'notifications'],
};

const DEFAULT_VIEW = {
    healthcare_worker: 'dashboard',
    referral_coordinator: 'dashboard',
    hospital_admin: 'analytics',
    system_admin: 'analytics',
};

const ICONS = {
    dashboard: `<svg viewBox="0 0 20 20" fill="none" class="h-[18px] w-[18px] shrink-0 text-slate-400">
        <rect x="3" y="3" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
        <rect x="11" y="3" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
        <rect x="3" y="11" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
        <rect x="11" y="11" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
    </svg>`,
    referrals: `<svg viewBox="0 0 20 20" fill="none" class="h-[18px] w-[18px] shrink-0 text-slate-400">
        <path d="M4 8h9M13 8l-3-3M13 8l-3 3M16 12H7M7 12l3-3M7 12l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>`,
    'new-referral': `<svg viewBox="0 0 20 20" fill="none" class="h-[18px] w-[18px] shrink-0 text-slate-400">
        <path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
    </svg>`,
    notifications: `<svg viewBox="0 0 20 20" fill="none" class="h-[18px] w-[18px] shrink-0 text-slate-400">
        <path d="M10 3a5 5 0 0 1 5 5c0 3 1 4 1 4H4s1-1 1-4a5 5 0 0 1 5-5ZM8.5 16a1.7 1.7 0 0 0 3 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>`,
    analytics: `<svg viewBox="0 0 20 20" fill="none" class="h-[18px] w-[18px] shrink-0 text-slate-400">
        <path d="M4 15V9M9 15V5M14 15v-4M18 16.5H2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
    </svg>`,
    hospitals: `<svg viewBox="0 0 20 20" fill="none" class="h-[18px] w-[18px] shrink-0 text-slate-400">
        <path d="M4 18V4.5A1.5 1.5 0 0 1 5.5 3h9A1.5 1.5 0 0 1 16 4.5V18M3 18h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>`,
    users: `<svg viewBox="0 0 20 20" fill="none" class="h-[18px] w-[18px] shrink-0 text-slate-400">
        <circle cx="9" cy="7" r="3" stroke="currentColor" stroke-width="1.5"/>
        <path d="M4 17c0-2.5 2.2-4 5-4s5 1.5 5 4M14.5 5.2a3 3 0 0 1 0 5.6M15 13.5c1.8.7 3 2 3 3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
    </svg>`,
    activity: `<svg viewBox="0 0 20 20" fill="none" class="h-[18px] w-[18px] shrink-0 text-slate-400">
        <rect x="3.5" y="4" width="13" height="12" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
        <path d="M7 8h6M7 12h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
    </svg>`,
};

const LABELS = {
    dashboard: 'Dashboard',
    referrals: 'Referrals',
    'new-referral': 'New referral',
    notifications: 'Notifications',
    analytics: 'Overview',
    hospitals: 'Hospitals',
    users: 'Users',
    activity: 'Activity log',
};

const shellMarkup = `
<aside class="hidden w-64 shrink-0 flex-col border-r border-slate-200/80 bg-white lg:flex">
    <div class="flex items-center gap-2.5 px-5 py-5">
        <img src="/images/badbaado-logo.jpg" alt="BADBAADO logo" class="h-9 w-9 rounded-[10px] ring-1 ring-slate-200">
        <div>
            <div class="text-sm font-extrabold tracking-tight text-brand-950">BADBAADO</div>
            <div class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">Referral Console</div>
        </div>
    </div>
    <nav id="shell-nav" class="flex-1 space-y-1 px-3 py-4 text-sm font-medium"></nav>
    <div class="border-t border-slate-200/80 p-4">
        <div id="shell-user" class="text-sm"></div>
        <button id="logout-btn" class="btn btn-ghost btn-sm mt-3 w-full justify-center">
            <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4">
                <path d="M8 4H5v12h3M13 7l3 3-3 3M6 10h9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Sign out
        </button>
    </div>
</aside>

<div class="flex min-w-0 flex-1 flex-col">
    <header class="flex items-center justify-between border-b border-slate-200/80 bg-white px-4 py-3 lg:hidden">
        <div class="flex items-center gap-2">
            <img src="/images/badbaado-logo.jpg" alt="BADBAADO logo" class="h-8 w-8 rounded-lg ring-1 ring-slate-200">
            <span class="text-sm font-extrabold text-brand-950">BADBAADO</span>
        </div>
        <select id="mobile-nav" class="rounded-lg border border-slate-200 px-2 py-1.5 text-sm font-medium"></select>
    </header>
    <main id="view-container" class="flex-1 overflow-y-auto bg-[#F6F8FB] p-4 sm:p-6"></main>
</div>
`;

async function renderView(name) {
    currentView = name;
    const container = document.querySelector('#view-container');
    container.innerHTML = `
        <div class="mx-auto max-w-6xl">
            <div class="flex items-center justify-between">
                <div class="space-y-2"><div class="skeleton h-6 w-44"></div><div class="skeleton h-3.5 w-72"></div></div>
            </div>
            <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div class="skeleton h-24"></div><div class="skeleton h-24"></div><div class="skeleton h-24"></div><div class="skeleton h-24"></div>
            </div>
            <div class="mt-8 space-y-2.5"><div class="skeleton h-16"></div><div class="skeleton h-16"></div><div class="skeleton h-16"></div></div>
        </div>`;

    document.querySelectorAll('.shell-nav').forEach((link) => {
        const active = link.dataset.view === name;
        link.classList.toggle('bg-brand-50', active);
        link.classList.toggle('text-brand-800', active);
        link.classList.toggle('font-semibold', active);
        link.classList.toggle('ring-1', active);
        link.classList.toggle('ring-brand-100', active);
        link.classList.toggle('text-slate-600', !active);
        link.querySelector('svg')?.classList.toggle('text-brand-600', active);
        link.querySelector('svg')?.classList.toggle('text-slate-400', !active);
    });

    const mobile = document.querySelector('#mobile-nav');
    if (mobile) mobile.value = name;

    try {
        switch (name) {
            case 'dashboard':
                await dashboardPage.render(container);
                break;
            case 'referrals':
                await referralsPage.render(container);
                break;
            case 'new-referral':
                await newReferralPage.render(container);
                break;
            case 'notifications':
                await notificationsPage.render(container);
                break;
            case 'analytics':
                await analyticsPage.render(container);
                break;
            case 'users':
                await usersPage.render(container);
                break;
            case 'hospitals':
                await hospitalsPage.render(container);
                break;
            case 'activity':
                await activityPage.render(container);
                break;
            default:
                container.innerHTML = '<div class="py-20 text-center text-slate-500">Nothing here.</div>';
        }
    } catch (error) {
        container.innerHTML = `<div class="mx-auto max-w-md rounded-xl border border-red-100 bg-red-50 px-4 py-8 text-center text-sm text-red-700">${escapeHtml(error.message)}</div>`;
    }
}

function buildNav(roleSlug) {
    const views = NAV[roleSlug] ?? NAV.healthcare_worker;
    const nav = document.querySelector('#shell-nav');
    const mobile = document.querySelector('#mobile-nav');
    nav.innerHTML = views.map((view) => `
        <a href="#" data-view="${view}" class="shell-nav flex items-center gap-3 rounded-[10px] px-3 py-2 text-slate-600 transition hover:bg-slate-50 hover:text-brand-700">
            ${ICONS[view]}
            ${LABELS[view]}
        </a>`).join('');
    mobile.innerHTML = views.map((view) => `<option value="${view}">${LABELS[view]}</option>`).join('');
    mobile.classList.remove('hidden');
}

async function renderDetail(id) {
    currentView = 'referral-detail';
    const container = document.querySelector('#view-container');
    container.innerHTML = `
        <div class="mx-auto max-w-5xl">
            <div class="mt-2 space-y-2"><div class="skeleton h-7 w-72"></div><div class="skeleton h-4 w-96"></div></div>
            <div class="mt-6 grid gap-4 lg:grid-cols-3">
                <div class="space-y-4 lg:col-span-2"><div class="skeleton h-28"></div><div class="skeleton h-28"></div><div class="skeleton h-64"></div></div>
                <div class="space-y-4"><div class="skeleton h-40"></div><div class="skeleton h-40"></div></div>
            </div>
        </div>`;
    try {
        await referralDetailPage.render(container, id);
    } catch (error) {
        container.innerHTML = `<div class="mx-auto max-w-md rounded-xl border border-red-100 bg-red-50 px-4 py-8 text-center text-sm text-red-700">${escapeHtml(error.message)}</div>`;
    }
}

export const appShell = {
    setUser(user) {
        currentUser = user;
        const box = document.querySelector('#shell-user');
        if (box) {
            const hospital = user.hospital
                ? `<div class="mt-0.5 flex items-center gap-1.5 text-xs text-slate-400">
                       <svg viewBox="0 0 20 20" fill="none" class="h-3.5 w-3.5"><path d="M4 9l6-5 6 5v8a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
                       ${escapeHtml(user.hospital?.short_name ?? 'Platform')}</div>`
                : '<div class="mt-0.5 text-xs text-slate-400">System admin</div>';
            box.innerHTML = `<div class="font-semibold text-slate-800">${escapeHtml(user.name)}</div>${hospital}<div class="mt-1.5"><span class="badge bg-brand-50 text-brand-700">${escapeHtml(user.role?.name ?? 'Member')}</span></div>`;
        }
    },

    getUser() {
        return currentUser;
    },

    mount(user) {
        currentUser = user;
        const app = document.querySelector('#app');
        app.innerHTML = shellMarkup;

        this.setUser(user);
        buildNav(user.role?.slug);

        document.querySelectorAll('.shell-nav').forEach((link) => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                renderView(link.dataset.view);
            });
        });

        const mobile = document.querySelector('#mobile-nav');
        if (mobile) {
            mobile.addEventListener('change', () => renderView(mobile.value));
        }

        document.querySelector('#logout-btn').addEventListener('click', async () => {
            try {
                await api.post('/logout');
            } catch {
                // Token already invalid — proceed anyway.
            }
            clearToken();
            window.location.href = '/login';
        });

        renderView(DEFAULT_VIEW[user.role?.slug] ?? 'dashboard');

        window.addEventListener('badbaado:navigate', (event) => {
            const target = event.detail;
            const allowed = NAV[user.role?.slug] ?? NAV.healthcare_worker;
            if (allowed.includes(target)) renderView(target);
        });

        window.addEventListener('badbaado:open-referral', (event) => renderDetail(event.detail));
    },
};