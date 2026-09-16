import { api } from '../lib/api';
import { appShell } from '../app-shell';
import { escapeHtml } from '../lib/format';
import { renderToast } from '../lib/toast';

function suggestUrgency(input) {
    const text = Object.values(input).filter(Boolean).join(' ').toLowerCase();
    const critical = ['unconscious', 'unresponsive', 'cardiac', 'stroke', 'seizure', 'respiratory', 'spo2', 'trauma', 'bleeding'];
    const emergent = ['chest pain', 'shortness of breath', 'fracture', 'low spo', 'heart', 'neuro'];
    const urgent = ['fever', 'pain', 'observation', 'high risk', 'worsening'];

    if (critical.some((k) => text.includes(k))) return { urgency: 'critical', confidence: 0.92, reason: 'Vitals and presenting symptoms indicate a life-threatening condition requiring immediate pre-alert.' };
    if (emergent.some((k) => text.includes(k))) return { urgency: 'emergent', confidence: 0.78, reason: 'Symptoms suggest a time-sensitive condition that should reach the receiving team promptly.' };
    if (urgent.some((k) => text.includes(k))) return { urgency: 'urgent', confidence: 0.64, reason: 'Condition requires prompt attention but is not immediately life-threatening.' };
    return { urgency: 'routine', confidence: 0.55, reason: 'No acute red flags detected; treat as routine referral.' };
}

function toneClasses(urgency) {
    const map = {
        critical: 'border-red-200 bg-red-50/70 text-red-800',
        emergent: 'border-orange-200 bg-orange-50/70 text-orange-800',
        urgent: 'border-amber-200 bg-amber-50/70 text-amber-900',
        routine: 'border-emerald-200 bg-emerald-50/70 text-emerald-800',
    };
    return map[urgency] ?? 'border-slate-200 bg-slate-50 text-slate-700';
}

const urgencyOptions = [
    ['routine', 'Normal'],
    ['urgent', 'Medium'],
    ['emergent', 'High'],
    ['critical', 'Critical'],
];

let hospitalsCache = null;

async function loadHospitals() {
    if (!hospitalsCache) {
        const result = await api.get('/hospitals');
        hospitalsCache = result.data;
    }
    return hospitalsCache;
}

export const newReferralPage = {
    async render(container) {
        const me = appShell.getUser();
        const hospitals = await loadHospitals();
        const myId = me?.hospital?.id;
        const options = hospitals
            .filter((h) => h.id !== myId)
            .map((h) => `<option value="${h.id}">${escapeHtml(h.name)}</option>`)
            .join('');

        container.innerHTML = `
            <div class="mx-auto max-w-3xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="page-heading text-2xl">New referral</h1>
                        <p class="mt-1 text-sm text-slate-500">Build a structured handover. The receiving team sees everything at once.</p>
                    </div>
                    <button id="new-ref-back" class="btn btn-secondary btn-sm">← Back</button>
                </div>

                <form id="referral-form" class="mt-6 space-y-5">
                    <section class="card p-6">
                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Destination</div>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-1">
                                <label class="label" for="rf-receiving">Receiving hospital</label>
                                <select id="rf-receiving" required class="input mt-1.5">
                                    <option value="">Select hospital…</option>${options}
                                </select>
                            </div>
                            <div>
                                <label class="label" for="rf-department">Department</label>
                                <input id="rf-department" type="text" placeholder="e.g. Cardiology" required class="input mt-1.5">
                            </div>
                        </div>
                    </section>

                    <section class="card p-6">
                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Patient</div>
                        <div class="mt-4 grid gap-4 sm:grid-cols-4">
                            <div class="sm:col-span-2">
                                <label class="label" for="rf-patient-name">Full name</label>
                                <input id="rf-patient-name" type="text" required class="input mt-1.5">
                            </div>
                            <div>
                                <label class="label" for="rf-age">Age</label>
                                <input id="rf-age" type="number" min="0" max="130" class="input mt-1.5">
                            </div>
                            <div>
                                <label class="label" for="rf-gender">Gender</label>
                                <select id="rf-gender" class="input mt-1.5">
                                    <option value="">—</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <section class="card p-6">
                        <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Clinical handover</div>
                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="label" for="rf-reason">Reason for referral</label>
                                <textarea id="rf-reason" rows="2" required class="input mt-1.5" placeholder="Why is this patient being referred?"></textarea>
                            </div>
                            <div>
                                <label class="label" for="rf-symptoms">Symptoms</label>
                                <textarea id="rf-symptoms" rows="2" class="input mt-1.5"></textarea>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-5">
                                <div><label class="label" for="vit-bp">BP</label><input id="vit-bp" placeholder="120/80" class="input mt-1.5"></div>
                                <div><label class="label" for="vit-hr">HR</label><input id="vit-hr" type="number" placeholder="80" class="input mt-1.5"></div>
                                <div><label class="label" for="vit-rr">RR</label><input id="vit-rr" type="number" placeholder="16" class="input mt-1.5"></div>
                                <div><label class="label" for="vit-spo2">SpO₂</label><input id="vit-spo2" type="number" placeholder="98" class="input mt-1.5"></div>
                                <div><label class="label" for="vit-temp">Temp</label><input id="vit-temp" type="number" step="0.1" placeholder="36.8" class="input mt-1.5"></div>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="label" for="rf-consciousness">Consciousness</label>
                                    <select id="rf-consciousness" class="input mt-1.5">
                                        <option value="">—</option>
                                        <option value="alert">Alert</option>
                                        <option value="confused">Confused</option>
                                        <option value="lethargic">Lethargic</option>
                                        <option value="unresponsive">Unresponsive</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="label" for="rf-conditions">Existing conditions</label>
                                    <input id="rf-conditions" type="text" placeholder="e.g. Diabetes, HTN" class="input mt-1.5">
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="card p-6">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="flex items-center gap-3 rounded-[10px] border border-slate-200 bg-white p-4 transition hover:border-brand-200">
                                <input id="rf-trauma" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-accent-600 focus:ring-accent-300">
                                <span class="text-sm font-medium text-slate-700">Trauma indicator</span>
                            </label>
                            <label class="flex items-center gap-3 rounded-[10px] border border-slate-200 bg-white p-4 transition hover:border-brand-200">
                                <input id="rf-emergency" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-accent-600 focus:ring-accent-300">
                                <span class="text-sm font-medium text-slate-700">Emergency pre-alert</span>
                            </label>
                        </div>
                    </section>

                    <section class="card p-6">
                        <div class="flex items-center justify-between">
                            <div class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Urgency (assisted)</div>
                            <button type="button" id="recompute-ai" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Re-assess</button>
                        </div>
                        <div id="ai-box" class="mt-3 hidden rounded-[12px] border p-4 text-sm"></div>
                        <div class="mt-4 grid gap-2 sm:grid-cols-4">
                            ${urgencyOptions.map(([value, label]) => `
                                <label class="cursor-pointer">
                                    <input type="radio" name="urgency" value="${value}" class="peer sr-only">
                                    <span class="urgency-pill w-full justify-center border border-slate-200 bg-white py-3 text-slate-500 uppercase transition peer-checked:border-accent-400 peer-checked:bg-accent-50 peer-checked:text-accent-800">${label}</span>
                                </label>`).join('')}
                        </div>
                        <p class="mt-2 text-xs text-slate-400">Urgency is a clinical decision. The AI suggestion informs, never decides.</p>
                    </section>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button" id="ref-cancel" class="btn btn-ghost">Cancel</button>
                        <button type="submit" id="ref-submit" class="btn btn-primary px-6">Create referral</button>
                    </div>
                </form>
            </div>
        `;

        const runAI = () => {
            const input = {
                reason: document.querySelector('#rf-reason').value,
                symptoms: document.querySelector('#rf-symptoms').value,
                bp: document.querySelector('#vit-bp').value,
                hr: document.querySelector('#vit-hr').value,
                spo2: document.querySelector('#vit-spo2').value,
                consciousness: document.querySelector('#rf-consciousness').value,
                trauma: document.querySelector('#rf-trauma').checked,
                emergency: document.querySelector('#rf-emergency').checked,
            };
            const suggestion = suggestUrgency(input);
            const box = document.querySelector('#ai-box');
            box.classList.remove('hidden');
            box.className = `mt-3 rounded-[12px] border p-4 text-sm ${toneClasses(suggestion.urgency)}`;
            const label = urgencyOptions.find(([value]) => value === suggestion.urgency)?.[1] ?? suggestion.urgency;
            box.innerHTML = `
                <div class="flex items-center justify-between gap-3">
                    <span class="inline-flex items-center gap-2 font-bold">
                        <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4"><path d="M12 2a2 2 0 0 0-2 2v1H8v5H6V8H3l1 4h2v4l3 2v2h6v-5h2l1-4h-1V4l-5-2Z" fill="currentColor" opacity="0.9"/></svg>
                        Suggested: <span class="urgency-pill bg-white text-current my-0.5">${label}</span>
                    </span>
                    <span class="text-xs opacity-70">${Math.round(suggestion.confidence * 100)}% confidence</span>
                </div>
                <div class="mt-1 text-xs opacity-90">${escapeHtml(suggestion.reason)}</div>`;
            const radio = document.querySelector(`input[value="${suggestion.urgency}"]`);
            if (radio) radio.checked = true;
        };

        ['#rf-reason', '#rf-symptoms', '#vit-bp', '#vit-hr', '#vit-spo2', '#rf-consciousness', '#rf-trauma', '#rf-emergency'].forEach((sel) => {
            document.querySelector(sel).addEventListener('input', debounce(runAI, 500));
        });
        document.querySelector('#recompute-ai').addEventListener('click', runAI);

        const back = document.querySelector('#new-ref-back');
        if (back) back.addEventListener('click', () => window.dispatchEvent(new CustomEvent('badbaado:navigate', { detail: 'referrals' })));
        document.querySelector('#ref-cancel').addEventListener('click', () => window.dispatchEvent(new CustomEvent('badbaado:navigate', { detail: 'referrals' })));

        document.querySelector('#referral-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const button = document.querySelector('#ref-submit');
            button.disabled = true;
            button.textContent = 'Creating…';

            const vitals = {};
            const vitMap = { bp: 'vit-bp', hr: 'vit-hr', rr: 'vit-rr', spo2: 'vit-spo2', temp: 'vit-temp' };
            for (const [key, sel] of Object.entries(vitMap)) {
                const val = document.querySelector(sel).value;
                if (val !== '') vitals[key] = key === 'bp' ? val : Number(val);
            }

            const urgency = document.querySelector('input[name="urgency"]:checked')?.value ?? null;

            const payload = {
                receiving_hospital_id: Number(document.querySelector('#rf-receiving').value),
                department: document.querySelector('#rf-department').value.trim(),
                referral_reason: document.querySelector('#rf-reason').value.trim(),
                symptoms: document.querySelector('#rf-symptoms').value.trim() || null,
                vitals: Object.keys(vitals).length ? vitals : null,
                consciousness: document.querySelector('#rf-consciousness').value || null,
                trauma_indicator: document.querySelector('#rf-trauma').checked,
                is_emergency: document.querySelector('#rf-emergency').checked,
                existing_conditions: document.querySelector('#rf-conditions').value.trim() || null,
                urgency,
                ai_suggestion: document.querySelector('#ai-box')?.dataset?.suggestion ?? null,
                patient: {
                    name: document.querySelector('#rf-patient-name').value.trim(),
                    age: document.querySelector('#rf-age').value ? Number(document.querySelector('#rf-age').value) : null,
                    gender: document.querySelector('#rf-gender').value || null,
                },
            };

            try {
                const created = await api.post('/referrals', payload);
                renderToast(`Referral ${created.data.referral_number} created.`);
                window.dispatchEvent(new CustomEvent('badbaado:open-referral', { detail: created.data.id }));
            } catch (error) {
                renderToast(error.message, 'error');
                button.disabled = false;
                button.textContent = 'Create referral';
            }
        });
    },
};

function debounce(fn, delay) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}