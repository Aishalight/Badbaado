let toastTimer = null;

export function renderToast(message, type = 'success') {
    const existing = document.querySelector('#app-toast');
    if (existing) existing.remove();

    const isError = type === 'error';
    const toast = document.createElement('div');
    toast.id = 'app-toast';
    toast.setAttribute('role', isError ? 'alert' : 'status');
    toast.setAttribute('aria-live', isError ? 'assertive' : 'polite');
    toast.className = `toast-in card fixed right-4 bottom-4 z-50 max-w-sm flex items-center gap-2.5 px-4 py-3 text-sm font-medium text-slate-800 ${
        isError ? 'border-red-200 bg-red-50' : 'border-brand-100 bg-brand-50'
    }`;

    const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    icon.setAttribute('viewBox', '0 0 20 20');
    icon.setAttribute('fill', 'none');
    icon.setAttribute('class', isError
        ? 'h-4 w-4 shrink-0 text-red-600'
        : 'h-4 w-4 shrink-0 text-emerald-600');
    icon.setAttribute('aria-hidden', 'true');

    const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
    circle.setAttribute('cx', '10');
    circle.setAttribute('cy', '10');
    circle.setAttribute('r', '8');
    circle.setAttribute('stroke', 'currentColor');
    circle.setAttribute('stroke-width', '1.6');
    icon.appendChild(circle);

    const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    path.setAttribute('d', isError ? 'M7.5 7.5l5 5M12.5 7.5l-5 5' : 'M6.5 10l2.5 2.5 4.5-5');
    path.setAttribute('stroke', 'currentColor');
    path.setAttribute('stroke-width', '1.6');
    path.setAttribute('stroke-linecap', 'round');
    if (!isError) path.setAttribute('stroke-linejoin', 'round');
    icon.appendChild(path);

    const text = document.createElement('span');
    text.textContent = message === null || message === undefined ? '' : String(message);

    toast.append(icon, text);
    document.body.appendChild(toast);

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.remove(), 3500);
}