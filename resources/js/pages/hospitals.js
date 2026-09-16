import { api } from '../lib/api';
import { escapeHtml } from '../lib/format';
import { renderToast } from '../lib/toast';

export const hospitalsPage = {
    async render(container) {
        const hospitals = (await api.get('/admin/hospitals')).data;

        container.innerHTML = `
            <div class="mx-auto max-w-6xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="page-heading text-2xl">Hospitals</h1>
                        <p class="mt-1 text-sm text-slate-500">The care network BADBAADO connects.</p>
                    </div>
                    <button id="hos-add-toggle" class="btn btn-primary btn-sm">+ Register hospital</button>
                </div>

                <form id="hos-create-form" class="card mt-6 hidden p-6">
                    <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Register a hospital</div>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="sm:col-span-2"><label class="label" for="nh-name">Hospital name</label><input id="nh-name" required class="input mt-1.5"></div>
                        <div><label class="label" for="nh-short">Short name</label><input id="nh-short" required placeholder="e.g. AHL" class="input mt-1.5"></div>
                        <div><label class="label" for="nh-code">Code</label><input id="nh-code" required placeholder="e.g. AHL" class="input mt-1.5"></div>
                        <div><label class="label" for="nh-location">Location</label><input id="nh-location" class="input mt-1.5"></div>
                        <div><label class="label" for="nh-level">Level</label><input id="nh-level" placeholder="e.g. Tertiary" class="input mt-1.5"></div>
                        <div><label class="label" for="nh-phone">Phone</label><input id="nh-phone" class="input mt-1.5"></div>
                        <div><label class="label" for="nh-email">Email</label><input id="nh-email" type="email" class="input mt-1.5"></div>
                        <div><label class="label" for="nh-active">Status</label><select id="nh-active" class="input mt-1.5"><option value="1">Active</option><option value="0">Inactive</option></select></div>
                        <div class="flex items-end"><button type="submit" class="btn btn-primary w-full">Register</button></div>
                    </div>
                </form>

                <div class="card mt-6 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[720px] text-left text-sm">
                            <thead>
                                <tr class="border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400">
                                    <th class="px-5 py-3">Hospital</th>
                                    <th class="px-5 py-3">Location</th>
                                    <th class="px-5 py-3">Level</th>
                                    <th class="px-5 py-3">Staff</th>
                                    <th class="px-5 py-3">Status</th>
                                    <th class="px-5 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="hos-rows">
                                ${hospitals.map((h) => `
                                <tr class="border-b border-slate-100 ${h.is_active ? '' : 'opacity-50'}">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2 font-semibold text-slate-800">
                                            ${escapeHtml(h.short_name)}
                                            <span class="badge bg-slate-100 text-slate-500">${escapeHtml(h.code)}</span>
                                        </div>
                                        <div class="text-xs text-slate-400">${escapeHtml(h.name)}</div>
                                    </td>
                                    <td class="px-5 py-3 text-slate-500">${escapeHtml(h.location ?? '—')}</td>
                                    <td class="px-5 py-3 text-slate-500">${escapeHtml(h.level ?? '—')}</td>
                                    <td class="px-5 py-3 text-slate-500">${h.users_count ?? 0}</td>
                                    <td class="px-5 py-3">
                                        <span class="badge ${h.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'}">${h.is_active ? 'Active' : 'Inactive'}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <button data-hos-toggle="${h.id}" class="btn btn-ghost btn-sm">${h.is_active ? 'Deactivate' : 'Activate'}</button>
                                    </td>
                                </tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                    ${hospitals.length === 0 ? '<div class="px-5 py-8 text-center text-sm text-slate-400">No hospitals registered.</div>' : ''}
                </div>
            </div>
        `;

        const toggle = document.querySelector('#hos-add-toggle');
        const form = document.querySelector('#hos-create-form');
        toggle.addEventListener('click', () => form.classList.toggle('hidden'));

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const payload = {
                name: document.querySelector('#nh-name').value.trim(),
                short_name: document.querySelector('#nh-short').value.trim(),
                code: document.querySelector('#nh-code').value.trim(),
                location: document.querySelector('#nh-location').value.trim() || null,
                level: document.querySelector('#nh-level').value.trim() || null,
                phone: document.querySelector('#nh-phone').value.trim() || null,
                email: document.querySelector('#nh-email').value.trim() || null,
                is_active: document.querySelector('#nh-active').value === '1',
            };

            try {
                await api.post('/admin/hospitals', payload);
                renderToast('Hospital registered.');
                form.reset();
                hospitalsPage.render(container);
            } catch (error) {
                renderToast(error.message, 'error');
            }
        });

        document.querySelectorAll('[data-hos-toggle]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.hosToggle;
                const target = hospitals.find((h) => h.id === Number(id));
                api.patch(`/admin/hospitals/${id}`, { is_active: !target.is_active })
                    .then(() => renderToast(target.is_active ? 'Hospital deactivated.' : 'Hospital activated.'))
                    .then(() => hospitalsPage.render(container))
                    .catch((error) => renderToast(error.message, 'error'));
            });
        });
    },
};