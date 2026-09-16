import { api } from '../lib/api';
import { appShell } from '../app-shell';
import { escapeHtml } from '../lib/format';
import { renderToast } from '../lib/toast';

const ROLE_LABELS = {
    healthcare_worker: 'Healthcare worker',
    referral_coordinator: 'Referral coordinator',
    hospital_admin: 'Hospital admin',
    system_admin: 'System admin',
};

let hospitals = [];

async function loadHospitals() {
    if (!hospitals.length) {
        const result = await api.get('/admin/hospitals');
        hospitals = result.data;
    }
    return hospitals;
}

function roleSelect(selected, extra) {
    const roles = ['healthcare_worker', 'referral_coordinator', 'hospital_admin', ...(extra || [])];
    return roles.map((r) => `<option value="${r}" ${r === selected ? 'selected' : ''}>${ROLE_LABELS[r]}</option>`).join('');
}

export const usersPage = {
    async render(container) {
        const me = appShell.getUser();
        const isSystem = me?.role?.slug === 'system_admin';
        const list = (await api.get('/admin/users')).data;
        if (isSystem) await loadHospitals();

        const scopeLabel = isSystem ? 'Everyone on the platform, every role.' : `Staff at ${escapeHtml(me?.hospital?.name ?? 'your hospital')}.`;

        container.innerHTML = `
            <div class="mx-auto max-w-6xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="page-heading text-2xl">Users</h1>
                        <p class="mt-1 text-sm text-slate-500">${scopeLabel}</p>
                    </div>
                    <button id="user-add-toggle" class="btn btn-primary btn-sm">+ Add user</button>
                </div>

                <form id="user-create-form" class="card mt-6 hidden p-6">
                    <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">New user account</div>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div><label class="label" for="nu-name">Full name</label><input id="nu-name" required class="input mt-1.5"></div>
                        <div><label class="label" for="nu-email">Email</label><input id="nu-email" type="email" required class="input mt-1.5"></div>
                        <div><label class="label" for="nu-password">Temporary password</label><input id="nu-password" type="password" required minlength="8" class="input mt-1.5"></div>
                        <div><label class="label" for="nu-title">Title</label><input id="nu-title" placeholder="e.g. Registrar" class="input mt-1.5"></div>
                        <div><label class="label" for="nu-phone">Phone</label><input id="nu-phone" class="input mt-1.5"></div>
                        <div>
                            <label class="label" for="nu-role">Role</label>
                            <select id="nu-role" class="input mt-1.5">${roleSelect('healthcare_worker', isSystem ? ['system_admin'] : [])}</select>
                        </div>
                        ${isSystem ? `
                        <div>
                            <label class="label" for="nu-hospital">Hospital</label>
                            <select id="nu-hospital" class="input mt-1.5">
                                <option value="">— system ambulant —</option>
                                ${hospitals.filter((h) => h.is_active).map((h) => `<option value="${h.id}">${escapeHtml(h.name)}</option>`).join('')}
                            </select>
                        </div>` : ''}
                        <div class="flex items-end"><button type="submit" class="btn btn-primary w-full">Create user</button></div>
                    </div>
                    <p class="mt-3 text-xs text-slate-400">The user changes their password at their first sign-in.</p>
                </form>

                <div class="card mt-6 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[720px] text-left text-sm">
                            <thead>
                                <tr class="border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400">
                                    <th class="px-5 py-3">User</th>
                                    <th class="px-5 py-3">Hospital</th>
                                    <th class="px-5 py-3">Role</th>
                                    <th class="px-5 py-3">Status</th>
                                    <th class="px-5 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="user-rows">
                                ${list.map((u) => `
                                <tr class="border-b border-slate-100 ${u.is_active ? '' : 'opacity-50'}">
                                    <td class="px-5 py-3">
                                        <div class="font-semibold text-slate-800">${escapeHtml(u.name)}</div>
                                        <div class="text-xs text-slate-400">${escapeHtml(u.email)}${u.title ? ` · ${escapeHtml(u.title)}` : ''}</div>
                                    </td>
                                    <td class="px-5 py-3 text-slate-500">${escapeHtml(u.hospital?.short_name ?? '—')}</td>
                                    <td class="px-5 py-3">
                                        <select data-user-role="${u.id}" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs" ${u.id === me?.id ? 'disabled' : ''}>
                                            ${roleSelect(u.role?.slug, isSystem ? ['system_admin'] : [])}
                                        </select>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="badge ${u.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'}">${u.is_active ? 'Active' : 'Suspended'}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        ${u.id === me?.id ? '<span class="text-xs text-slate-300">You</span>' : `
                                        <button data-user-toggle="${u.id}" class="btn btn-ghost btn-sm">${u.is_active ? 'Suspend' : 'Restore'}</button>`}
                                    </td>
                                </tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                    ${list.length === 0 ? '<div class="px-5 py-8 text-center text-sm text-slate-400">No users match.</div>' : ''}
                </div>
            </div>
        `;

        const toggle = document.querySelector('#user-add-toggle');
        const create = document.querySelector('#user-create-form');
        toggle.addEventListener('click', () => create.classList.toggle('hidden'));

        create.addEventListener('submit', async (event) => {
            event.preventDefault();
            const payload = {
                name: document.querySelector('#nu-name').value.trim(),
                email: document.querySelector('#nu-email').value.trim(),
                password: document.querySelector('#nu-password').value,
                title: document.querySelector('#nu-title').value.trim() || null,
                phone: document.querySelector('#nu-phone').value.trim() || null,
                role_slug: document.querySelector('#nu-role').value,
            };
            if (isSystem) payload.hospital_id = document.querySelector('#nu-hospital').value ? Number(document.querySelector('#nu-hospital').value) : null;
            if (!payload.hospital_id && payload.role_slug !== 'system_admin') {
                renderToast('Assign a hospital to this user.', 'error');
                return;
            }

            try {
                await api.post('/admin/users', payload);
                renderToast('User created.');
                create.reset();
                usersPage.render(container);
            } catch (error) {
                renderToast(error.message, 'error');
            }
        });

        document.querySelectorAll('[data-user-role]').forEach((select) => {
            select.addEventListener('change', () => {
                api.patch(`/admin/users/${select.dataset.userRole}`, { role_slug: select.value })
                    .then(() => renderToast('Role updated.'))
                    .catch((error) => renderToast(error.message, 'error'));
            });
        });

        document.querySelectorAll('[data-user-toggle]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.userToggle;
                const target = list.find((u) => u.id === Number(id));
                api.patch(`/admin/users/${id}`, { is_active: !target.is_active })
                    .then(() => renderToast(target.is_active ? 'User suspended.' : 'User restored.'))
                    .then(() => usersPage.render(container))
                    .catch((error) => renderToast(error.message, 'error'));
            });
        });
    },
};