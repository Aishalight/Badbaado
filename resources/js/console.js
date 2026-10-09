import "../css/app.css";
import "../css/theme.css";
import "../css/console.css";
import { api } from "./lib/api";
import { initThemeToggle } from "./lib/cinematic";
import { initDashboardCharts } from "./pages/dashboard";
import * as alarms from "./lib/alarms";

const formData = (form) => new FormData(form);

function toastHost() {
    let host = document.querySelector(".toast-host");

    if (!host) {
        host = document.createElement("div");
        host.className = "toast-host";
        document.body.appendChild(host);
    }

    host.setAttribute("role", "status");
    host.setAttribute("aria-live", "polite");
    host.setAttribute("aria-atomic", "false");

    return host;
}

function toast(message, type = "info") {
    const el = document.createElement("div");
    el.className = `toast toast--${type}`;
    el.textContent = message;
    toastHost().appendChild(el);
    window.setTimeout(() => {
        el.style.opacity = "0";
        el.style.transform = "translateY(4px)";
        window.setTimeout(() => el.remove(), 250);
    }, 3500);
}

const showError = (error) => toast(error.message, "error");

/**
 * Laravel reports nested keys as `patient.name` / `attachments.0` while the
 * matching control may be named `patient[name]`, `attachments[0]`, or
 * `settings[auth.registration_enabled]`, so every plausible spelling is tried.
 */
const fieldSelectors = (field) => {
    const root = field.split(".")[0];
    const dotted = `[${field.replace(/\./g, "][")}]`;
    const indexed = field.replace(/\.(\d+)/g, "[$1]");
    const rootWrapped = field.replace(/\.(.+)/, "[$1]");

    return [
        `[name=${JSON.stringify(field)}]`,
        `[name=${JSON.stringify(rootWrapped)}]`,
        `[name=${JSON.stringify(indexed)}]`,
        `[name=${JSON.stringify(`${root}[]`)}]`,
        `[name=${JSON.stringify(dotted)}]`,
        `[name=${JSON.stringify(`${dotted}[]`)}]`,
    ];
};

/**
 * Paints 422 field errors next to their inputs and returns the first offender so
 * the caller can move focus there.
 */
function showFieldErrors(form, errors) {
    form.querySelectorAll("[data-field-error]").forEach((node) => {
        node.textContent = "";
        node.removeAttribute("id");
    });
    form.querySelectorAll('[aria-invalid="true"]').forEach((node) =>
        node.removeAttribute("aria-invalid"),
    );
    form.querySelectorAll("[aria-describedby]").forEach((node) => {
        if (node.dataset.errorDescribedBy)
            node.removeAttribute("aria-describedby");
    });

    if (!errors) return null;

    let first = null;

    Object.entries(errors).forEach(([field, messages]) => {
        if (field === "message") return;

        const input = fieldSelectors(field)
            .map((selector) => form.querySelector(selector))
            .find((node) => node);

        if (!input) return;

        input.setAttribute("aria-invalid", "true");

        const key = `field-error-${field.replace(/[^\w-]/g, "-")}`;
        let holder = form.querySelector(
            `[data-field-error=${JSON.stringify(field)}]`,
        );

        if (!holder) {
            holder =
                input.parentElement?.querySelector("[data-field-error]") ??
                null;
        }

        if (!holder) {
            holder = Object.assign(document.createElement("small"), {
                className: "field-error",
                dataset: { fieldError: field },
            });
            input.insertAdjacentElement("afterend", holder);
        }

        holder.id = key;
        holder.textContent = [].concat(messages).join(" ");

        const describedBy = (input.getAttribute("aria-describedby") ?? "")
            .split(/\s+/)
            .filter(Boolean);
        if (!describedBy.includes(key)) {
            describedBy.push(key);
            input.setAttribute("aria-describedby", describedBy.join(" "));
            input.dataset.errorDescribedBy = "true";
        }

        first ??= input;
    });

    return first;
}

const FOCUSABLE =
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';

/**
 * Accessible modal: focus moves in, Tab is trapped, Escape cancels, and focus
 * returns to whatever opened it.
 */
function openDialog({
    title,
    body,
    confirmLabel = "Confirm",
    danger = false,
    input = null,
}) {
    return new Promise((resolve) => {
        const opener = document.activeElement;
        const titleId = `dialog-title-${Date.now()}`;

        const backdrop = document.createElement("div");
        backdrop.className = "dialog-backdrop";

        const dialog = document.createElement("div");
        dialog.className = "dialog";
        dialog.setAttribute("role", "dialog");
        dialog.setAttribute("aria-modal", "true");
        dialog.setAttribute("aria-labelledby", titleId);

        const heading = document.createElement("h3");
        heading.id = titleId;
        heading.textContent = title;

        const text = document.createElement("p");
        text.className = "mt-2";
        text.textContent = body;

        const actions = document.createElement("div");
        actions.className = "mt-5 flex justify-end gap-2";

        const cancel = Object.assign(document.createElement("button"), {
            type: "button",
            className: "btn-ghost btn-sm",
            textContent: "Cancel",
        });

        const confirm = Object.assign(document.createElement("button"), {
            type: "button",
            className: danger ? "btn-danger btn-sm" : "btn-accent btn-sm",
            textContent: confirmLabel,
        });

        let field = null;

        if (input) {
            const fieldId = `dialog-field-${Date.now()}`;

            text.id = `${titleId}-body`;
            dialog.setAttribute("aria-describedby", text.id);
            dialog.append(heading, text);

            dialog.append(heading, text);

            const label = Object.assign(document.createElement("label"), {
                htmlFor: fieldId,
                className: "mt-4 block text-sm font-semibold text-slate-700",
                textContent: input.label ?? "",
            });

            field = Object.assign(document.createElement("textarea"), {
                id: fieldId,
                className: "form-input mt-1 w-full",
                rows: 3,
                placeholder: input.placeholder ?? "",
                name: "dialog-input",
            });

            dialog.append(label, field);

            if (input.hint) {
                const hint = Object.assign(document.createElement("p"), {
                    id: `${fieldId}-hint`,
                    className: "mt-1 text-xs text-slate-500",
                    textContent: input.hint,
                });

                dialog.appendChild(hint);
                dialog.setAttribute("aria-describedby", hint.id);
            }

            dialog.appendChild(actions);
        } else {
            text.id = `${titleId}-body`;
            dialog.append(heading, text, actions);
            dialog.setAttribute("aria-describedby", text.id);
        }

        actions.append(cancel, confirm);
        backdrop.appendChild(dialog);

        function onKeydown(event) {
            if (event.key === "Escape") {
                event.preventDefault();
                close(input ? null : false);

                return;
            }

            if (event.key !== "Tab") return;

            const focusable = [...dialog.querySelectorAll(FOCUSABLE)].filter(
                (node) => !node.disabled,
            );
            if (focusable.length === 0) return;

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }

        function close(value) {
            document.removeEventListener("keydown", onKeydown);
            backdrop.remove();
            if (opener instanceof HTMLElement) opener.focus();
            resolve(value);
        }

        cancel.addEventListener("click", () => close(input ? null : false));
        confirm.addEventListener("click", () =>
            close(input ? field.value.trim() || null : true),
        );
        backdrop.addEventListener("click", (event) => {
            if (event.target === backdrop) close(input ? null : false);
        });
        document.addEventListener("keydown", onKeydown);
        document.body.appendChild(backdrop);

        (field ?? confirm).focus();
    });
}

const confirmDialog = (
    message,
    { title = "Please confirm", confirmLabel = "Confirm" } = {},
) => openDialog({ title, body: message, confirmLabel, danger: true });

async function submitReferral(form) {
    const button = document.querySelector("#referral-submit");
    const banner = document.querySelector("#referral-error");
    button.disabled = true;
    banner.classList.add("hidden");
    try {
        const result = await api.post("/referrals", formData(form));
        await api.post(`/referrals/${result.data.id}/transition`, {
            status: "sent",
        });
        window.location.href = `/referrals/${result.data.id}`;
    } catch (error) {
        const firstInvalid = showFieldErrors(form, error.errors);
        banner.textContent = error.message;
        banner.classList.remove("hidden");
        button.disabled = false;
        if (firstInvalid) firstInvalid.focus();
    }
}

const DESTRUCTIVE_TRANSITIONS = {
    rejected:
        "Decline this referral? The referring hospital will be told it was not accepted.",
    withdrawn:
        "Withdraw this referral? The receiving team will lose it from their queue.",
};

async function transition(button) {
    const next = button.dataset.transition;

    if (DESTRUCTIVE_TRANSITIONS[next]) {
        const confirmed = await confirmDialog(DESTRUCTIVE_TRANSITIONS[next], {
            title: "This cannot be undone",
            confirmLabel:
                next === "withdrawn" ? "Withdraw referral" : "Decline referral",
        });

        if (!confirmed) return;
    }

    button.disabled = true;
    try {
        await api.post(`/referrals/${button.dataset.referralId}/transition`, {
            status: next,
        });
        window.location.reload();
    } catch (error) {
        showError(error);
        button.disabled = false;
    }
}

async function sendMessage(form) {
    const referralId = window.location.pathname.split("/").pop();
    const body = form.querySelector('[name="body"]').value.trim();
    if (!body) return;
    try {
        const result = await api.post(`/referrals/${referralId}/messages`, {
            body,
        });
        appendMessage(result.data);
        form.querySelector('[name="body"]').value = "";
    } catch (error) {
        showError(error);
    }
}

function appendMessage(message) {
    const list = document.querySelector("#message-list");
    if (!list || document.querySelector(`[data-message-id="${message.id}"]`))
        return;

    list.querySelector(".text-slate-500")?.remove();
    const item = document.createElement("div");
    item.className = "rounded-xl bg-slate-50 p-3";
    item.dataset.messageId = message.id;

    const meta = document.createElement("div");
    meta.className = "flex justify-between text-xs font-bold text-slate-600";
    const sender = document.createElement("span");
    sender.textContent = message.sender?.name ?? "Care team";
    const timestamp = document.createElement("span");
    timestamp.textContent = new Date(message.created_at).toLocaleString([], {
        dateStyle: "medium",
        timeStyle: "short",
    });
    meta.append(sender, timestamp);

    const body = document.createElement("p");
    body.className = "mt-1 text-sm text-slate-700";
    body.textContent = message.body;
    item.append(meta, body);
    list.appendChild(item);
    list.scrollTop = list.scrollHeight;
}

async function pollReferralUpdates() {
    const page = document.querySelector("#referral-page");
    if (!page || document.hidden) return;

    const referralId = page.dataset.referralId;
    const lastMessageId = [...document.querySelectorAll("[data-message-id]")]
        .map((item) => Number(item.dataset.messageId))
        .reduce((highest, id) => Math.max(highest, id), 0);

    try {
        const [messages, referral] = await Promise.all([
            api.get(
                `/referrals/${referralId}/messages?after_id=${lastMessageId}`,
            ),
            api.get(`/referrals/${referralId}`),
        ]);
        messages.data?.forEach(appendMessage);
        const updatedAt = referral.data?.updated_at;
        if (updatedAt && updatedAt !== page.dataset.referralUpdatedAt)
            window.location.reload();
    } catch {
        // Ignore transient polling failures; the next interval retries.
    }
}

function initReferralLiveUpdates() {
    if (document.querySelector("#referral-page"))
        window.setInterval(pollReferralUpdates, 3000);
}

async function requestInformation(button) {
    const body = await openDialog({
        title: "Request more information",
        body: "Describe what the referring hospital should send back before you can proceed.",
        confirmLabel: "Send request",
        input: {
            label: "What do you need from the referring hospital?",
            hint: "Be specific so the receiving team can act on this quickly.",
            placeholder: "e.g. Latest ECG and troponin result",
        },
    });

    if (!body) return;

    button.disabled = true;
    try {
        const result = await api.post(
            `/referrals/${button.dataset.referralId}/messages`,
            {
                body: `Information requested: ${body}`,
            },
        );
        appendMessage(result.data);
        button.disabled = false;
    } catch (error) {
        showError(error);
        button.disabled = false;
    }
}

async function submitAdminForm(form, endpoint) {
    const button = form.querySelector('button[type="submit"]');
    button.disabled = true;
    try {
        await api.post(endpoint, formData(form));
        window.location.reload();
    } catch (error) {
        const firstInvalid = showFieldErrors(form, error.errors);
        toast(error.message, "error");
        button.disabled = false;
        if (firstInvalid) firstInvalid.focus();
    }
}

async function updateAdminResource(button, endpoint, payload) {
    button.disabled = true;
    try {
        await api.patch(endpoint, payload);
        toast("Saved", "success");
        button.disabled = false;
    } catch (error) {
        showError(error);
        button.disabled = false;
    }
}

async function deleteAdminResource(
    button,
    endpoint,
    message = "Are you sure you want to delete this?",
) {
    button.disabled = true;
    const confirmed = await confirmDialog(message);
    if (!confirmed) {
        button.disabled = false;
        return;
    }
    try {
        await api.delete(endpoint);
        window.location.reload();
    } catch (error) {
        showError(error);
        button.disabled = false;
    }
}

async function markNotificationRead(button) {
    button.disabled = true;
    try {
        await api.post(
            `/notifications/${button.dataset.readNotification}/read`,
        );
        alarms.acknowledge();
        window.location.reload();
    } catch (error) {
        showError(error);
        button.disabled = false;
    }
}

async function markAllNotificationsRead(button) {
    button.disabled = true;
    try {
        await api.post("/notifications/read-all");
        alarms.acknowledge();
        window.location.reload();
    } catch (error) {
        showError(error);
        button.disabled = false;
    }
}

function imagePreview(input) {
    const target = input.parentElement.querySelector("[data-image-preview]");
    if (!target || !input.files?.length) return;
    const url = URL.createObjectURL(input.files[0]);
    target.replaceChildren(
        Object.assign(new Image(), {
            src: url,
            alt: "",
            class: "h-full w-full object-cover",
        }),
    );
}

async function uploadRowImage(button, endpoint, field) {
    const row = button.closest("tr");
    const input = row.querySelector(`input[type="file"][name="${field}"]`);
    if (!input?.files?.length) {
        toast("Choose an image first", "error");
        input?.focus();

        return;
    }

    const original = button.textContent;
    button.disabled = true;
    button.textContent = "Uploading…";
    const payload = new FormData();
    payload.append(field, input.files[0]);
    try {
        await api.patch(endpoint, payload);
        toast("Image saved", "success");
        window.location.reload();
    } catch (error) {
        showError(error);
        button.disabled = false;
        button.textContent = original;
    }
}

function initDestinationPicker() {
    const hospitalTarget = document.querySelector("#hospital-target");
    const doctorTarget = document.querySelector("#doctor-target");
    const hospital = document.querySelector("#hospital");
    const search = document.querySelector("#doctor-search");
    const doctor = document.querySelector("#intended-doctor");
    const modes = document.querySelectorAll('[name="destination_type"]');
    if (!hospitalTarget || !doctorTarget || !hospital || !search || !doctor)
        return;

    async function searchDoctors() {
        if (search.value.trim().length < 2) {
            doctor.replaceChildren(
                new Option("Search by name or specialty", ""),
            );
            doctor.disabled = true;
            return;
        }

        try {
            const result = await api.get(
                `/doctors?q=${encodeURIComponent(search.value.trim())}&per_page=50`,
            );
            const doctors = result.data ?? [];
            doctor.replaceChildren(new Option("Choose doctor", ""));
            doctors.forEach((item) => {
                const detail = [
                    item.title,
                    item.specialty?.name,
                    item.practice?.name,
                ]
                    .filter(Boolean)
                    .join(" — ");
                doctor.append(
                    new Option(
                        detail ? `${item.name} — ${detail}` : item.name,
                        String(item.id),
                    ),
                );
            });
            doctor.disabled = doctors.length === 0;
        } catch (error) {
            showError(error);
            doctor.replaceChildren(new Option("Unable to load doctors", ""));
            doctor.disabled = true;
        }
    }

    function renderMode() {
        const doctorMode =
            document.querySelector('[name="destination_type"]:checked')
                ?.value === "doctor";
        hospitalTarget.classList.toggle("hidden", doctorMode);
        doctorTarget.classList.toggle("hidden", !doctorMode);
        hospital.disabled = doctorMode;
        hospital.required = !doctorMode;
        doctor.disabled = !doctorMode;
        doctor.required = doctorMode;
        if (!doctorMode) {
            doctor.value = "";
            doctor.replaceChildren(
                new Option("Search by name or specialty", ""),
            );
        }
    }

    let searchTimer;
    modes.forEach((mode) => mode.addEventListener("change", renderMode));
    search.addEventListener("input", () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(searchDoctors, 250);
    });
    renderMode();
}

function initAdminTabs() {
    document.querySelectorAll("[data-admin-tab]").forEach((pill) => {
        pill.addEventListener("click", () => {
            const tab = pill.dataset.adminTab;
            document
                .querySelectorAll("[data-admin-tab]")
                .forEach((p) => p.classList.toggle("is-active", p === pill));
            document
                .querySelectorAll("[data-admin-pane]")
                .forEach((pane) =>
                    pane.classList.toggle(
                        "hidden",
                        pane.dataset.adminPane !== tab,
                    ),
                );
        });
    });
}

async function createInlineResource(form, endpoint) {
    const button = form.querySelector('button[type="submit"]');
    button.disabled = true;
    try {
        await api.post(endpoint, formData(form));
        window.location.reload();
    } catch (error) {
        const firstInvalid = showFieldErrors(form, error.errors);
        toast(error.message, "error");
        button.disabled = false;
        if (firstInvalid) firstInvalid.focus();
    }
}

/**
 * Alarm switch on the settings page. The permission is only ever requested
 * from this click, so nothing is asked of the browser on page load.
 */
function initAlarmSettings() {
    const panel = document.querySelector("[data-alarm-settings]");
    if (!panel) return;

    const toggle = panel.querySelector("[data-alarm-toggle]");
    const state = panel.querySelector("[data-alarm-state]");
    const note = panel.querySelector("[data-alarm-note]");
    const testButton = panel.querySelector("[data-alarm-test]");
    const testResult = panel.querySelector("[data-alarm-test-result]");

    if (!alarms.isSupported()) {
        toggle.disabled = true;
        note.textContent =
            "This browser does not support system notifications, so alarms cannot be enabled here.";
        testButton.disabled = true;

        return;
    }

    const paint = () => {
        const on = alarms.isEnabled();
        toggle.checked = on;
        state.textContent = on ? "On" : "Off";

        if (!on) {
            note.textContent =
                "Off by default. Nothing is requested from your browser until you turn this on.";

            return;
        }

        note.textContent =
            alarms.permission() === "granted"
                ? "Alarms are on for this browser. They sound while BADBAADO has an open tab; a closed browser cannot be reached without a push service."
                : "Alarms are on, but this browser is still withholding notification permission.";
    };

    toggle.addEventListener("change", async () => {
        if (toggle.checked) {
            const result = await alarms.enable();

            if (result === "denied") {
                note.textContent =
                    "This browser blocked notifications for BADBAADO. Re-allow it in the site settings, then turn alarms back on.";
            } else if (result === "unsupported") {
                toggle.disabled = true;
            } else {
                alarms.start();
            }
        } else {
            alarms.disable();
        }

        paint();
    });

    testButton.addEventListener("click", () => {
        const granted = alarms.permission() === "granted";
        alarms.preview("critical");

        testResult.textContent = granted
            ? "Played a critical alarm and an operating-system notification."
            : "Played the sound only — this browser has not granted notification permission.";
    });

    alarms.onChange(paint);
    paint();
}

document.addEventListener("DOMContentLoaded", () => {
    initThemeToggle();
    initDashboardCharts();
    initAdminTabs();
    initDestinationPicker();
    initReferralLiveUpdates();
    initAlarmSettings();
    if (alarms.isEnabled()) alarms.start();
    document
        .querySelector("#referral-form")
        ?.addEventListener("submit", (event) => {
            event.preventDefault();
            submitReferral(event.currentTarget);
        });
    document
        .querySelector("#is-emergency")
        ?.addEventListener("change", (event) => {
            if (event.currentTarget.checked) {
                document.querySelector("#urgency").value = "critical";
            }
        });
    document
        .querySelectorAll("[data-transition]")
        .forEach((button) =>
            button.addEventListener("click", () => transition(button)),
        );
    document
        .querySelector("#message-form")
        ?.addEventListener("submit", (event) => {
            event.preventDefault();
            sendMessage(event.currentTarget);
        });
    document
        .querySelectorAll("[data-request-information]")
        .forEach((button) =>
            button.addEventListener("click", () => requestInformation(button)),
        );
    document
        .querySelectorAll("[data-read-notification]")
        .forEach((button) =>
            button.addEventListener("click", () =>
                markNotificationRead(button),
            ),
        );
    document
        .querySelector("[data-mark-all-read]")
        ?.addEventListener("click", (event) =>
            markAllNotificationsRead(event.currentTarget),
        );
    document
        .querySelector("#admin-user-form")
        ?.addEventListener("submit", (event) => {
            event.preventDefault();
            submitAdminForm(event.currentTarget, "/admin/users");
        });
    document
        .querySelector("#admin-hospital-form")
        ?.addEventListener("submit", (event) => {
            event.preventDefault();
            submitAdminForm(event.currentTarget, "/admin/hospitals");
        });
    document
        .querySelectorAll("[data-open-user-form], [data-open-hospital-form]")
        .forEach((button) =>
            button.addEventListener("click", () =>
                document
                    .querySelector(
                        `#${button.dataset.openUserForm ? "admin-user-form" : "admin-hospital-form"}`,
                    )
                    ?.classList.remove("hidden"),
            ),
        );
    function collectRowInputs(row) {
        const inputs = row.querySelectorAll("input, select");
        const data = {};
        inputs.forEach((input) => {
            if (input.name && input.type !== "file") {
                if (input.type === "checkbox") {
                    data[input.name] = input.checked;
                } else {
                    data[input.name] = input.value;
                }
            }
        });
        return data;
    }
    document.querySelectorAll("[data-update-user]").forEach((button) =>
        button.addEventListener("click", () => {
            const row = button.closest("tr");
            const id = button.dataset.updateUser;
            const payload = collectRowInputs(row);
            updateAdminResource(button, `/admin/users/${id}`, payload);
        }),
    );
    document
        .querySelectorAll("[data-delete-user]")
        .forEach((button) =>
            button.addEventListener("click", () =>
                deleteAdminResource(
                    button,
                    `/admin/users/${button.dataset.deleteUser}`,
                ),
            ),
        );
    document
        .querySelectorAll("[data-upload-user-photo]")
        .forEach((button) =>
            button.addEventListener("click", () =>
                uploadRowImage(
                    button,
                    `/admin/users/${button.dataset.uploadUserPhoto}`,
                    "avatar",
                ),
            ),
        );
    document
        .querySelectorAll("[data-upload-hospital-logo]")
        .forEach((button) =>
            button.addEventListener("click", () =>
                uploadRowImage(
                    button,
                    `/admin/hospitals/${button.dataset.uploadHospitalLogo}`,
                    "logo",
                ),
            ),
        );
    document
        .querySelectorAll("#admin-user-form [data-image-input]")
        .forEach((input) =>
            input.addEventListener("change", () => imagePreview(input)),
        );
    document
        .querySelectorAll("#admin-hospital-form [data-image-input]")
        .forEach((input) =>
            input.addEventListener("change", () => imagePreview(input)),
        );
    document.querySelectorAll("[data-update-hospital]").forEach((button) =>
        button.addEventListener("click", () => {
            const row = button.closest("tr");
            const id = button.dataset.updateHospital;
            const payload = collectRowInputs(row);
            updateAdminResource(button, `/admin/hospitals/${id}`, payload);
        }),
    );
    document
        .querySelectorAll("[data-delete-hospital]")
        .forEach((button) =>
            button.addEventListener("click", () =>
                deleteAdminResource(
                    button,
                    `/admin/hospitals/${button.dataset.deleteHospital}`,
                ),
            ),
        );

    // ---- Command Center: CMS ----
    document.querySelectorAll("[data-update-cms]").forEach((button) =>
        button.addEventListener("click", () => {
            const block = button.closest("div").parentElement;
            const id = button.dataset.updateCms;
            const field = block.querySelector("[data-update-cms-field]");
            updateAdminResource(button, `/admin/cms/${id}`, {
                content: field ? field.value : "",
            });
        }),
    );

    // ---- Command Center: Announcements ----
    document
        .querySelector("[data-create-announcement]")
        ?.addEventListener("submit", (event) => {
            event.preventDefault();
            createInlineResource(event.currentTarget, "/admin/announcements");
        });
    document.querySelectorAll("[data-update-announcement]").forEach((button) =>
        button.addEventListener("click", () => {
            const row = button.closest("div.flex");
            const id = button.dataset.updateAnnouncement;
            const payload = collectRowInputs(row);
            updateAdminResource(button, `/admin/announcements/${id}`, payload);
        }),
    );
    document
        .querySelectorAll("[data-announcement-active]")
        .forEach((checkbox) =>
            checkbox.addEventListener("change", () => {
                const row = checkbox.closest("div.flex");
                const id = checkbox.dataset.announcementActive;
                updateAdminResource(
                    checkbox,
                    `/admin/announcements/${id}`,
                    collectRowInputs(row),
                );
            }),
        );
    document
        .querySelectorAll("[data-delete-announcement]")
        .forEach((button) =>
            button.addEventListener("click", () =>
                deleteAdminResource(
                    button,
                    `/admin/announcements/${button.dataset.deleteAnnouncement}`,
                    "Delete this announcement from the public site?",
                ),
            ),
        );

    // ---- Command Center: FAQs ----
    document
        .querySelector("[data-create-faq]")
        ?.addEventListener("submit", (event) => {
            event.preventDefault();
            createInlineResource(event.currentTarget, "/admin/faqs");
        });
    document.querySelectorAll("[data-update-faq]").forEach((button) =>
        button.addEventListener("click", () => {
            const row = button.closest("div.flex");
            const id = button.dataset.updateFaq;
            const payload = collectRowInputs(row);
            updateAdminResource(button, `/admin/faqs/${id}`, payload);
        }),
    );
    document.querySelectorAll("[data-faq-active]").forEach((checkbox) =>
        checkbox.addEventListener("change", () => {
            const row = checkbox.closest("div.flex");
            const id = checkbox.dataset.faqActive;
            updateAdminResource(
                checkbox,
                `/admin/faqs/${id}`,
                collectRowInputs(row),
            );
        }),
    );
    document
        .querySelectorAll("[data-delete-faq]")
        .forEach((button) =>
            button.addEventListener("click", () =>
                deleteAdminResource(
                    button,
                    `/admin/faqs/${button.dataset.deleteFaq}`,
                    "Delete this FAQ from the public site?",
                ),
            ),
        );

    // ---- Command Center: Settings ----
    document
        .querySelector("[data-settings-form]")
        ?.addEventListener("submit", async (event) => {
            event.preventDefault();
            const form = event.currentTarget;
            const button = form.querySelector('button[type="submit"]');
            button.disabled = true;
            const payload = {};
            form.querySelectorAll("[data-key]").forEach((input) => {
                payload[input.dataset.key] =
                    input.dataset.setting === "boolean"
                        ? input.checked
                        : input.value;
            });
            try {
                await api.patch("/admin/settings", { settings: payload });
                toast("Configuration saved", "success");
                window.location.reload();
            } catch (error) {
                showError(error);
                button.disabled = false;
            }
        });

    // ---- Command Center: Backups ----
    document
        .querySelector("[data-create-backup]")
        ?.addEventListener("click", async (event) => {
            const button = event.currentTarget;
            button.disabled = true;
            try {
                await api.post("/admin/backups", {});
                toast("Backup created", "success");
                window.location.reload();
            } catch (error) {
                showError(error);
                button.disabled = false;
            }
        });
    document
        .querySelectorAll("[data-delete-backup]")
        .forEach((button) =>
            button.addEventListener("click", () =>
                deleteAdminResource(
                    button,
                    `/admin/backups/${button.dataset.deleteBackup}`,
                    "Delete this archive? This removes the file from the backups disk.",
                ),
            ),
        );

    // ---- Command Center: Platform alerts ----
    document
        .querySelector("[data-alert-form]")
        ?.addEventListener("submit", (event) => {
            event.preventDefault();
            const form = event.currentTarget;
            const button = form.querySelector('button[type="submit"]');
            button.disabled = true;
            const audience = form.querySelector('[name="audience"]').value;
            const title = form.querySelector('[name="title"]').value.trim();
            const body = form.querySelector('[name="body"]').value.trim();
            api.post("/admin/alerts", { audience, title, body })
                .then(() => {
                    toast("Alert sent", "success");
                    window.location.reload();
                })
                .catch((error) => {
                    showError(error);
                    button.disabled = false;
                });
        });
});
