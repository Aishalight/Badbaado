const TOKEN_KEY = 'badbaado_token';

export function getToken() {
    return sessionStorage.getItem(TOKEN_KEY);
}

export function setToken(token) {
    sessionStorage.setItem(TOKEN_KEY, token);
}

export function clearToken() {
    sessionStorage.removeItem(TOKEN_KEY);
}

async function raw(method, path, body) {
    const headers = { Accept: 'application/json', 'Content-Type': 'application/json' };
    const token = getToken();
    if (token) headers.Authorization = `Bearer ${token}`;

    const response = await fetch(`/api${path}`, {
        method,
        headers,
        body: body !== undefined ? JSON.stringify(body) : undefined,
    });

    let data = null;
    try {
        data = await response.json();
    } catch {
        data = null;
    }

    if (response.status === 401) {
        clearToken();
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
};