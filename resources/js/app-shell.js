import { api, clearToken } from './lib/api';
import { escapeHtml } from './lib/format';
import { dashboardPage } from './pages/dashboard';
import { referralsPage } from './pages/referrals';
import { referralDetailPage } from './pages/referral-detail';
import { newReferralPage } from './pages/new-referral';
import { notificationsPage } from './pages/notifications';

let currentUser = null;
let currentView = 'dashboard';

const shellMarkup = `
<aside class="hidden w-64 shrink-0 flex-col border-r border-slate-200/80 bg-white lg:flex">
    <div class="flex items-center gap-2.5 px-5 py-5">
        <img src="/images/badbaado-logo.jpg" alt="BADBAADO logo" class="h-9 w-9 rounded-[10px] ring-1 ring-slate-200">
        <div>
            <div class="text-sm font-extrabold tracking-tight text-brand-950">BADBAADO</div>
            <div class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">Referral Console</div>
        </div>
    </div>
    <nav class="flex-1 space-y-1 px-3 py-4 text-sm font-medium">
        <a href="#" data-view="dashboard" class="shell-nav flex items-center gap-3 rounded-[10px] px-3 py-2 text-slate-600 transition hover:bg-slate-50 hover:text-brand-700">
            <svg viewBox="0 0 20 20" fill="none" class="h-[18px] w-[18px] shrink-0 text-slate-400">
                <rect x="3" y="3" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                <rect x="11" y="3" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                <rect x="3" y="11" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                <rect x="11" y="11" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            Dashboard
        </a>
        <a href="#" data-view="referrals" class="shell-nav flex items-center gap-3 rounded-[10px] px-3 py-2 text-slate-600 transition hover:bg-slate-50 hover:text-brand-700">
            <svg viewBox="0 0 20 20" fill="none" class="h-[18px] w-[18px] shrink-0 text-slate-400">
                <path d="M4 8h9M13 8l-3-3M13 8l-3 3M16 12H7M7 12l3-3M7 12l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Referrals
        </a>
        <a href="#" data-view="new-referral" class="shell-nav flex items-center gap-3 rounded-[10px] px-3 py-2 text-slate-600 transition hover:bg-slate-50 hover:text-brand-700">
            <svg viewBox="0 0 20 20" fill="none" class="h-[18px] w-[18px] shrink-0 text-slate-400">
                <path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
            New referral
        </a>
        <a href="#" data-view="notifications" class="shell-nav flex items-center gap-3 rounded-[10px] px-3 py-2 text-slate-600 transition hover:bg-slate-50 hover:text-brand-700">
            <svg viewBox="0 0 20 20" fill="none" class="h-[18px] w-[18px] shrink-0 text-slate-400">
                <path d="M10 3a5 5 0 0 1 5 5c0 3 1 4 1 4H4s1-1 1-4a5 5 0 0 1 5-5ZM8.5 16a1.7 1.7 0 0 0 3 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Notifications
            <span id="notif-badge" class="ml-auto hidden rounded-full bg-accent-500 px-2 py-0.5 text-[11px] font-bold text-white"></span>
        </a>
    </nav>
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
        <select id="mobile-nav" class="rounded-lg border border-slate-200 px-2 py-1.5 text-sm">
            <option value="dashboard">Dashboard</option>
            <option value="referrals">Referrals</option>
            <option value="new-referral">New referral</option>
            <option value="notifications">Notifications</option>
        </select>
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
            default:
                container.innerHTML = '<div class="py-20 text-center text-slate-500">Nothing here.</div>';
        }
    } catch (error) {
        container.innerHTML = `<div class="mx-auto max-w-md rounded-xl border border-red-100 bg-red-50 px-4 py-8 text-center text-sm text-red-700">${escapeHtml(error.message)}</div>`;
    }
}

async function loadNotificationCount() {
    try {
        const result = await api.get('/notifications');
        const unread = result.data.filter((n) => !n.read).length;
        const badge = document.querySelector('#notif-badge');
        if (badge) {
            if (unread > 0) {
                badge.textContent = unread;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    } catch {
        // Ignore — non-blocking.
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
                       ${escapeHtml(user.hospital.short_name)}</div>`
                : '<div class="mt-0.5 text-xs text-slate-400">System admin</div>';
            box.innerHTML = `<div class="font-semibold text-slate-800">${escapeHtml(user.name)}</div>${hospital}<div class="mt-1.5"><span class="badge bg-brand-50 text-brand-700">${escapeHtml(user.role?.name ?? 'Member')}</span></div>`;
        }
    },

    getUser() {
        return currentUser;
    },

    mount() {
        const app = document.querySelector('#app');
        app.innerHTML = shellMarkup;

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

        renderView('dashboard');
        loadNotificationCount();
        setInterval(loadNotificationCount, 60000);
    },
};