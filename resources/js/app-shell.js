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
<aside class="hidden w-64 shrink-0 flex-col border-r border-slate-100 bg-white lg:flex">
    <div class="flex items-center gap-2.5 px-6 py-5">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-500 text-base font-black text-white">B</div>
        <div>
            <div class="text-sm font-extrabold tracking-tight text-brand-950">BADBAADO</div>
            <div class="text-[11px] font-medium text-slate-400">Referral Console</div>
        </div>
    </div>
    <nav class="flex-1 space-y-1 px-3 py-4 text-sm font-medium">
        <a href="#" data-view="dashboard" class="shell-nav flex items-center gap-3 rounded-lg px-3 py-2 text-slate-600 transition hover:bg-slate-50 hover:text-brand-700">
            <span class="text-lg">📊</span> Dashboard
        </a>
        <a href="#" data-view="referrals" class="shell-nav flex items-center gap-3 rounded-lg px-3 py-2 text-slate-600 transition hover:bg-slate-50 hover:text-brand-700">
            <span class="text-lg">🔄</span> Referrals
        </a>
        <a href="#" data-view="new-referral" class="shell-nav flex items-center gap-3 rounded-lg px-3 py-2 text-slate-600 transition hover:bg-slate-50 hover:text-brand-700">
            <span class="text-lg">＋</span> New referral
        </a>
        <a href="#" data-view="notifications" class="shell-nav flex items-center gap-3 rounded-lg px-3 py-2 text-slate-600 transition hover:bg-slate-50 hover:text-brand-700">
            <span class="text-lg">🔔</span> Notifications
            <span id="notif-badge" class="ml-auto hidden rounded-full bg-brand-500 px-2 py-0.5 text-[11px] font-bold text-white"></span>
        </a>
    </nav>
    <div class="border-t border-slate-100 p-4">
        <div id="shell-user" class="text-sm"></div>
        <button id="logout-btn" class="mt-3 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700">
            Sign out
        </button>
    </div>
</aside>

<div class="flex min-w-0 flex-1 flex-col">
    <header class="flex items-center justify-between border-b border-slate-100 bg-white px-4 py-3 lg:hidden">
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-500 text-sm font-black text-white">B</div>
            <span class="text-sm font-extrabold text-brand-950">BADBAADO</span>
        </div>
        <select id="mobile-nav" class="rounded-lg border border-slate-200 px-2 py-1.5 text-sm">
            <option value="dashboard">Dashboard</option>
            <option value="referrals">Referrals</option>
            <option value="new-referral">New referral</option>
            <option value="notifications">Notifications</option>
        </select>
    </header>
    <main id="view-container" class="flex-1 overflow-y-auto bg-slate-50 p-4 sm:p-6"></main>
</div>
`;

async function renderView(name) {
    currentView = name;
    const container = document.querySelector('#view-container');
    container.innerHTML = '<div class="py-20 text-center text-sm text-slate-400">Loading…</div>';

    document.querySelectorAll('.shell-nav').forEach((link) => {
        const active = link.dataset.view === name;
        link.classList.toggle('bg-brand-50', active);
        link.classList.toggle('text-brand-800', active);
        link.classList.toggle('font-semibold', active);
        link.classList.toggle('text-slate-600', !active);
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
            const hospital = user.hospital ? `<div class="text-xs text-slate-400">${escapeHtml(user.hospital.short_name)}</div>` : '<div class="text-xs text-slate-400">System admin</div>';
            box.innerHTML = `<div class="font-semibold text-slate-800">${escapeHtml(user.name)}</div>${hospital}<div class="mt-0.5 text-xs text-brand-600">${escapeHtml(user.role?.name ?? '')}</div>`;
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