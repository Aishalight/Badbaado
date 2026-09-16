let toastTimer = null;

export function renderToast(message, type = 'success') {
    const existing = document.querySelector('#app-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.id = 'app-toast';
    toast.className = `fixed right-4 bottom-4 z-50 max-w-sm rounded-lg border px-4 py-3 text-sm font-medium shadow-lg transition ${
        type === 'error'
            ? 'border-red-200 bg-red-50 text-red-800'
            : 'border-brand-200 bg-brand-50 text-brand-900'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.remove(), 3500);
}