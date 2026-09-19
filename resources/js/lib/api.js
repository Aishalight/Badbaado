function getCsrfToken() {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/);

    return match ? decodeURIComponent(match[1]) : null;
}

async function raw(method, path, body) {
    const isFormData = body instanceof FormData;
    const headers = { Accept: 'application/json' };
    if (!isFormData) headers['Content-Type'] = 'application/json';

    const csrfToken = getCsrfToken();
    if (method !== 'GET' && csrfToken) headers['X-XSRF-TOKEN'] = csrfToken;

    const response = await fetch(`/api${path}`, {
        method,
        headers,
        body: body !== undefined ? (isFormData ? body : JSON.stringify(body)) : undefined,
    });

    let data = null;
    try {
        data = await response.json();
    } catch {
        data = null;
    }

    if (response.status === 401) {
        window.location.href = '/login';
        throw new Error('Your session has expired. Please sign in again.');
    }

    if (!response.ok) {
        const message = data?.message ?? `Request failed (${response.status})`;
        throw new Error(message);
    }

    return data;
}

export const api = {
    get: (path) => raw('GET', path),
    post: (path, body) => raw('POST', path, body),
    put: (path, body) => raw('PUT', path, body),
    patch: (path, body) => raw('PATCH', path, body),
    delete: (path) => raw('DELETE', path),
    download: (path) => fetch(`/api${path}`, { headers: { Accept: 'application/octet-stream' } }),
};