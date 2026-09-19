import '../css/app.css';
import '../css/theme.css';
import '../css/console.css';
import { api } from './lib/api';
import { initThemeToggle } from './lib/cinematic';
import { initDashboardCharts } from './pages/dashboard';

const formData = (form) => new FormData(form);
const showError = (error) => window.alert(error.message);

function toast(message, type = 'info') {
    let host = document.querySelector('.toast-host');
    if (! host) {
        host = document.createElement('div');
        host.className = 'toast-host';
        document.body.appendChild(host);
    }
    const el = document.createElement('div');
    el.className = `toast toast--${type}`;
    el.textContent = message;
    host.appendChild(el);
    window.setTimeout(() => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(4px)';
        window.setTimeout(() => el.remove(), 250);
    }, 3500);
}

function confirmDialog(message, { title = 'Please confirm' } = {}) {
    return new Promise((resolve) => {
        const backdrop = document.createElement('div');
        backdrop.className = 'dialog-backdrop';
        backdrop.innerHTML = `
            <div class="dialog" role="dialog" aria-modal="true">
                <h3>${title}</h3>
                <p class="mt-2">${message}</p>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" class="btn-ghost btn-sm" data-dialog-cancel>Cancel</button>
                    <button type="button" class="btn-danger btn-sm" data-dialog-confirm>Confirm</button>
                </div>
            </div>`;
        backdrop.addEventListener('click', (event) => {
            if (event.target === backdrop) {
                close(false);
            }
        });
        backdrop.querySelector('[data-dialog-cancel]').addEventListener('click', () => close(false));
        backdrop.querySelector('[data-dialog-confirm]').addEventListener('click', () => close(true));
        document.body.appendChild(backdrop);

        function close(confirmed) {
            backdrop.remove();
            resolve(confirmed);
        }
    });
}

async function submitReferral(form) {
    const button = document.querySelector('#referral-submit');
    button.disabled = true;
    try {
        const result = await api.post('/referrals', formData(form));
        await api.post(`/referrals/${result.data.id}/transition`, { status: 'sent' });
        window.location.href = `/referrals/${result.data.id}`;
    } catch (error) {
        document.querySelector('#referral-error').textContent = error.message;
        document.querySelector('#referral-error').classList.remove('hidden');
        button.disabled = false;
    }
}

async function transition(button) {
    button.disabled = true;
    try { await api.post(`/referrals/${button.dataset.referralId}/transition`, { status: button.dataset.transition }); window.location.reload(); } catch (error) { showError(error); button.disabled = false; }
}

async function sendMessage(form) {
    const referralId = window.location.pathname.split('/').pop();
    const body = form.querySelector('[name="body"]').value.trim();
    if (!body) return;
    try { await api.post(`/referrals/${referralId}/messages`, { body }); window.location.reload(); } catch (error) { showError(error); }
}

async function requestInformation(button) {
    const body = window.prompt('What information should the referring hospital provide?');
    if (!body?.trim()) return;
    button.disabled = true;
    try { await api.post(`/referrals/${button.dataset.referralId}/messages`, { body: `Information requested: ${body.trim()}` }); window.location.reload(); } catch (error) { showError(error); button.disabled = false; }
}

async function submitAdminForm(form, endpoint) {
    const button = form.querySelector('button[type="submit"]');
    button.disabled = true;
    try { await api.post(endpoint, formData(form)); window.location.reload(); } catch (error) { showError(error); button.disabled = false; }
}

async function updateAdminResource(button, endpoint, payload) {
    button.disabled = true;
    try { await api.patch(endpoint, payload); toast('Saved', 'success'); button.disabled = false; } catch (error) { showError(error); button.disabled = false; }
}

async function deleteAdminResource(button, endpoint, message = 'Are you sure you want to delete this?') {
    button.disabled = true;
    const confirmed = await confirmDialog(message);
    if (! confirmed) { button.disabled = false; return; }
    try { await api.delete(endpoint); window.location.reload(); } catch (error) { showError(error); button.disabled = false; }
}

async function markNotificationRead(button) {
    button.disabled = true;
    try { await api.post(`/notifications/${button.dataset.readNotification}/read`); window.location.reload(); } catch (error) { showError(error); button.disabled = false; }
}

async function markAllNotificationsRead(button) {
    button.disabled = true;
    try { await api.post('/notifications/read-all'); window.location.reload(); } catch (error) { showError(error); button.disabled = false; }
}

function initAdminTabs() {
    document.querySelectorAll('[data-admin-tab]').forEach((pill) => {
        pill.addEventListener('click', () => {
            const tab = pill.dataset.adminTab;
            document.querySelectorAll('[data-admin-tab]').forEach((p) => p.classList.toggle('is-active', p === pill));
            document.querySelectorAll('[data-admin-pane]').forEach((pane) => pane.classList.toggle('hidden', pane.dataset.adminPane !== tab));
        });
    });
}

async function createInlineResource(form, endpoint) {
    const button = form.querySelector('button[type="submit"]');
    button.disabled = true;
    try { await api.post(endpoint, formData(form)); window.location.reload(); } catch (error) { showError(error); button.disabled = false; }
}

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initDashboardCharts();
    initAdminTabs();
    document.querySelector('#referral-form')?.addEventListener('submit', (event) => { event.preventDefault(); submitReferral(event.currentTarget); });
    document.querySelector('#is-emergency')?.addEventListener('change', (event) => {
        if (event.currentTarget.checked) {
            document.querySelector('#urgency').value = 'critical';
        }
    });
    document.querySelectorAll('[data-transition]').forEach((button) => button.addEventListener('click', () => transition(button)));
    document.querySelector('#message-form')?.addEventListener('submit', (event) => { event.preventDefault(); sendMessage(event.currentTarget); });
    document.querySelectorAll('[data-request-information]').forEach((button) => button.addEventListener('click', () => requestInformation(button)));
    document.querySelectorAll('[data-read-notification]').forEach((button) => button.addEventListener('click', () => markNotificationRead(button)));
    document.querySelector('[data-mark-all-read]')?.addEventListener('click', (event) => markAllNotificationsRead(event.currentTarget));
    document.querySelector('#admin-user-form')?.addEventListener('submit', (event) => { event.preventDefault(); submitAdminForm(event.currentTarget, '/admin/users'); });
    document.querySelector('#admin-hospital-form')?.addEventListener('submit', (event) => { event.preventDefault(); submitAdminForm(event.currentTarget, '/admin/hospitals'); });
    document.querySelectorAll('[data-open-user-form], [data-open-hospital-form]').forEach((button) => button.addEventListener('click', () => document.querySelector(`#${button.dataset.openUserForm ? 'admin-user-form' : 'admin-hospital-form'}`)?.classList.remove('hidden')));
    function collectRowInputs(row) {
        const inputs = row.querySelectorAll('input, select');
        const data = {};
        inputs.forEach((input) => {
            if (input.name) {
                if (input.type === 'checkbox') {
                    data[input.name] = input.checked;
                } else {
                    data[input.name] = input.value;
                }
            }
        });
        return data;
    }
    document.querySelectorAll('[data-update-user]').forEach((button) => button.addEventListener('click', () => {
        const row = button.closest('tr');
        const id = button.dataset.updateUser;
        const payload = collectRowInputs(row);
        updateAdminResource(button, `/admin/users/${id}`, payload);
    }));
    document.querySelectorAll('[data-delete-user]').forEach((button) => button.addEventListener('click', () => deleteAdminResource(button, `/admin/users/${button.dataset.deleteUser}`)));
    document.querySelectorAll('[data-update-hospital]').forEach((button) => button.addEventListener('click', () => {
        const row = button.closest('tr');
        const id = button.dataset.updateHospital;
        const payload = collectRowInputs(row);
        updateAdminResource(button, `/admin/hospitals/${id}`, payload);
    }));
    document.querySelectorAll('[data-delete-hospital]').forEach((button) => button.addEventListener('click', () => deleteAdminResource(button, `/admin/hospitals/${button.dataset.deleteHospital}`)));

    // ---- Command Center: CMS ----
    document.querySelectorAll('[data-update-cms]').forEach((button) => button.addEventListener('click', () => {
        const block = button.closest('div').parentElement;
        const id = button.dataset.updateCms;
        const field = block.querySelector('[data-update-cms-field]');
        updateAdminResource(button, `/admin/cms/${id}`, { content: field ? field.value : '' });
    }));

    // ---- Command Center: Announcements ----
    document.querySelector('[data-create-announcement]')?.addEventListener('submit', (event) => { event.preventDefault(); createInlineResource(event.currentTarget, '/admin/announcements'); });
    document.querySelectorAll('[data-update-announcement]').forEach((button) => button.addEventListener('click', () => {
        const row = button.closest('div.flex');
        const id = button.dataset.updateAnnouncement;
        const payload = collectRowInputs(row);
        updateAdminResource(button, `/admin/announcements/${id}`, payload);
    }));
    document.querySelectorAll('[data-announcement-active]').forEach((checkbox) => checkbox.addEventListener('change', () => {
        const row = checkbox.closest('div.flex');
        const id = checkbox.dataset.announcementActive;
        updateAdminResource(checkbox, `/admin/announcements/${id}`, collectRowInputs(row));
    }));
    document.querySelectorAll('[data-delete-announcement]').forEach((button) => button.addEventListener('click', () => deleteAdminResource(button, `/admin/announcements/${button.dataset.deleteAnnouncement}`, 'Delete this announcement from the public site?')));

    // ---- Command Center: FAQs ----
    document.querySelector('[data-create-faq]')?.addEventListener('submit', (event) => { event.preventDefault(); createInlineResource(event.currentTarget, '/admin/faqs'); });
    document.querySelectorAll('[data-update-faq]').forEach((button) => button.addEventListener('click', () => {
        const row = button.closest('div.flex');
        const id = button.dataset.updateFaq;
        const payload = collectRowInputs(row);
        updateAdminResource(button, `/admin/faqs/${id}`, payload);
    }));
    document.querySelectorAll('[data-faq-active]').forEach((checkbox) => checkbox.addEventListener('change', () => {
        const row = checkbox.closest('div.flex');
        const id = checkbox.dataset.faqActive;
        updateAdminResource(checkbox, `/admin/faqs/${id}`, collectRowInputs(row));
    }));
    document.querySelectorAll('[data-delete-faq]').forEach((button) => button.addEventListener('click', () => deleteAdminResource(button, `/admin/faqs/${button.dataset.deleteFaq}`, 'Delete this FAQ from the public site?')));

    // ---- Command Center: Settings ----
    document.querySelector('[data-settings-form]')?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        const payload = {};
        form.querySelectorAll('[data-key]').forEach((input) => {
            payload[input.dataset.key] = input.dataset.setting === 'boolean' ? input.checked : input.value;
        });
        try {
            await api.patch('/admin/settings', { settings: payload });
            toast('Configuration saved', 'success');
            window.location.reload();
        } catch (error) { showError(error); button.disabled = false; }
    });

    // ---- Command Center: Backups ----
    document.querySelector('[data-create-backup]')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        button.disabled = true;
        try { await api.post('/admin/backups', {}); toast('Backup created', 'success'); window.location.reload(); } catch (error) { showError(error); button.disabled = false; }
    });
    document.querySelectorAll('[data-delete-backup]').forEach((button) => button.addEventListener('click', () => deleteAdminResource(button, `/admin/backups/${button.dataset.deleteBackup}`, 'Delete this archive? This removes the file from the backups disk.')));

    // ---- Command Center: Platform alerts ----
    document.querySelector('[data-alert-form]')?.addEventListener('submit', (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        const audience = form.querySelector('[name="audience"]').value;
        const title = form.querySelector('[name="title"]').value.trim();
        const body = form.querySelector('[name="body"]').value.trim();
        api.post('/admin/alerts', { audience, title, body })
            .then(() => { toast('Alert sent', 'success'); window.location.reload(); })
            .catch((error) => { showError(error); button.disabled = false; });
    });
});