import { api } from '../lib/api';
import { escapeHtml, formatDate } from '../lib/format';
import { renderToast } from '../lib/toast';

export const notificationsPage = {
    async render(container) {
        const { data: notifications } = await api.get('/notifications');

        container.innerHTML = `
            <div class="mx-auto max-w-3xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-xl font-extrabold tracking-tight text-brand-950">Notifications</h1>
                        <p class="mt-1 text-sm text-slate-500">Every change on referrals you can see lands here.</p>
                    </div>
                    <button id="mark-all-btn" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 ${notifications.length ? '' : 'hidden'}">Mark all read</button>
                </div>

                <div id="notification-list" class="mt-6 space-y-2.5">
                    ${notifications.map((n) => `
                        <div class="rounded-xl border ${n.read_at ? 'border-slate-100 bg-white' : 'border-brand-200 bg-brand-50'} p-4">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-bold text-slate-800">${escapeHtml(n.title ?? 'Update')}</span>
                                        ${n.read_at ? '' : '<span class="rounded-full bg-brand-500 px-2 py-0.5 text-[10px] font-bold text-white">NEW</span>'}
                                    </div>
                                    <p class="mt-1 text-sm text-slate-600">${escapeHtml(n.body ?? '')}</p>
                                    <div class="mt-2 text-xs text-slate-400">${formatDate(n.created_at)}</div>
                                </div>
                                ${n.referral_id ? `<button data-ref="${n.referral_id}" class="notif-link shrink-0 rounded-lg border border-brand-200 bg-white px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-50">Open</button>` : ''}
                            </div>
                        </div>`).join('') || '<div class="rounded-xl border border-dashed border-slate-200 bg-white py-14 text-center text-sm text-slate-400">You\'re all caught up — no notifications.</div>'}
                </div>
            </div>
        `;

        document.querySelectorAll('.notif-link').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const notification = notifications.find((n) => n.id && btn.dataset.ref && n.referral_id === Number(btn.dataset.ref));
                if (notification && !notification.read_at) {
                    try {
                        await api.post(`/notifications/${notification.id}/read`);
                    } catch (_) {
                        // best-effort; navigation still proceeds
                    }
                }
                window.dispatchEvent(new CustomEvent('badbaado:open-referral', { detail: btn.dataset.ref }));
            });
        });

        const markAll = document.querySelector('#mark-all-btn');
        markAll?.addEventListener('click', async () => {
            try {
                await api.post('/notifications/read-all');
                renderToast('All notifications marked as read.');
                notificationsPage.render(container);
            } catch (error) {
                renderToast(error.message, 'error');
            }
        });
    },
};