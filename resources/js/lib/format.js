const urgencyMeta = {
    critical: { label: 'Critical', klass: 'bg-red-50 text-red-600' },
    emergent: { label: 'High', klass: 'bg-orange-50 text-orange-600' },
    urgent: { label: 'Medium', klass: 'bg-amber-50 text-amber-700' },
    routine: { label: 'Normal', klass: 'bg-emerald-50 text-emerald-600' },
};

const urgencyDotColors = {
    critical: 'bg-red-500',
    emergent: 'bg-orange-500',
    urgent: 'bg-amber-500',
    routine: 'bg-emerald-500',
};

const statusColors = {
    draft: 'bg-slate-100 text-slate-600',
    sent: 'bg-brand-50 text-brand-700',
    received: 'bg-accent-50 text-accent-700',
    under_review: 'bg-amber-50 text-amber-700',
    accepted: 'bg-emerald-50 text-emerald-700',
    transfer_in_progress: 'bg-orange-50 text-orange-600',
    arrived: 'bg-purple-50 text-purple-700',
    completed: 'bg-emerald-100 text-emerald-700',
    rejected: 'bg-red-50 text-red-600',
    cancelled: 'bg-slate-100 text-slate-500',
};

export function urgencyBadge(urgency) {
    if (!urgency) return '';
    const meta = urgencyMeta[urgency] ?? { label: capitalize(urgency), klass: 'bg-slate-100 text-slate-600' };
    return `<span class="urgency-pill ${meta.klass}">${meta.label}</span>`;
}

export function urgencyDot(urgency) {
    if (!urgency) return '';
    return `<span class="inline-block h-1.5 w-1.5 rounded-full ${urgencyDotColors[urgency] ?? 'bg-slate-400'}"></span>`;
}

export function statusBadge(status) {
    const klass = statusColors[status] ?? 'bg-slate-100 text-slate-600';
    return `<span class="status-pill ${klass}">${statusLabel(status)}</span>`;
}

export function capitalize(value) {
    return value.charAt(0).toUpperCase() + value.slice(1);
}

export function statusLabel(status) {
    const labels = {
        draft: 'Draft',
        sent: 'Sent',
        received: 'Received',
        under_review: 'Under review',
        accepted: 'Accepted',
        transfer_in_progress: 'Transfer in progress',
        arrived: 'Arrived',
        completed: 'Completed',
        rejected: 'Rejected',
        cancelled: 'Cancelled',
    };
    return labels[status] ?? capitalize(status);
}

export function formatDate(value) {
    if (!value) return '-';
    return new Date(value).toLocaleString(undefined, {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

export function formatVitals(vitals) {
    if (!vitals || typeof vitals !== 'object') return [];
    const labels = { bp: 'BP', hr: 'HR', rr: 'RR', spo2: 'SpO₂', temp: 'Temp' };
    return Object.entries(labels)
        .filter(([key]) => vitals[key] !== undefined && vitals[key] !== null)
        .map(([key, label]) => ({ label, value: `${vitals[key]}${key === 'temp' ? '°C' : ''}` }));
}

export function escapeHtml(value) {
    if (value === null || value === undefined) return '';
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}