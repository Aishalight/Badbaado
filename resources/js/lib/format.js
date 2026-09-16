const urgencyColors = {
    critical: 'bg-red-100 text-red-700',
    emergent: 'bg-orange-100 text-orange-700',
    urgent: 'bg-yellow-100 text-yellow-800',
    routine: 'bg-green-100 text-green-700',
};

const urgencyDotColors = {
    critical: 'bg-red-500',
    emergent: 'bg-orange-500',
    urgent: 'bg-yellow-500',
    routine: 'bg-green-500',
};

const statusColors = {
    draft: 'bg-slate-200 text-slate-700',
    sent: 'bg-blue-100 text-blue-700',
    received: 'bg-cyan-100 text-cyan-800',
    under_review: 'bg-yellow-100 text-yellow-800',
    accepted: 'bg-green-100 text-green-700',
    transfer_in_progress: 'bg-orange-100 text-orange-700',
    arrived: 'bg-purple-100 text-purple-700',
    completed: 'bg-emerald-100 text-emerald-700',
    rejected: 'bg-red-100 text-red-700',
    cancelled: 'bg-slate-100 text-slate-500',
};

export function urgencyBadge(urgency) {
    if (!urgency) return '';
    const klass = urgencyColors[urgency] ?? 'bg-slate-200 text-slate-700';
    return `<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ${klass}">
        <span class="h-1.5 w-1.5 rounded-full ${urgencyDotColors[urgency]}"></span>${capitalize(urgency)}
    </span>`;
}

export function statusBadge(status) {
    const klass = statusColors[status] ?? 'bg-slate-200 text-slate-700';
    return `<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ${klass}">${statusLabel(status)}</span>`;
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
    if (!value) return '—';
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