import { escapeHtml } from './format';

let toastTimer = null;

export function renderToast(message, type = 'success') {
    const existing = document.querySelector('#app-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.id = 'app-toast';
    toast.className = `toast-in card fixed right-4 bottom-4 z-50 max-w-sm flex items-center gap-2.5 px-4 py-3 text-sm font-medium text-slate-800 ${
        type === 'error'
            ? 'border-red-200 bg-red-50'
            : 'border-brand-100 bg-brand-50'
    }`;
    const icon = type === 'error'
        ? '<svg viewBox="0 0 20 20" fill="none" class="h-4 w-4 shrink-0 text-red-600"><circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.6"/><path d="M7.5 7.5l5 5M12.5 7.5l-5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>'
        : '<svg viewBox="0 0 20 20" fill="none" class="h-4 w-4 shrink-0 text-emerald-600"><circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.6"/><path d="M6.5 10l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    toast.innerHTML = `${icon}<span>${escapeHtml(message)}</span>`;
    document.body.appendChild(toast);

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.remove(), 3500);
}