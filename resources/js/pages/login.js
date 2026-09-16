import { api, setToken, clearToken } from '../lib/api';
import { renderToast } from '../lib/toast';

async function handleSubmit(event) {
    event.preventDefault();

    const form = event.currentTarget;
    const button = form.querySelector('#login-submit');
    const errorBox = form.querySelector('#login-error');

    errorBox.classList.add('hidden');
    button.disabled = true;
    button.textContent = 'Signing in…';

    const payload = {
        email: form.querySelector('#email').value.trim(),
        password: form.querySelector('#password').value,
    };

    try {
        const result = await api.post('/login', payload);
        setToken(result.token);
        renderToast(`Welcome back, ${result.user.name}`);
        window.location.href = '/dashboard';
    } catch (error) {
        errorBox.textContent = error.message;
        errorBox.classList.remove('hidden');
        button.disabled = false;
        button.textContent = 'Sign in';
    }
}

export const loginPage = {
    init() {
        const form = document.querySelector('#login-form');
        if (form) form.addEventListener('submit', handleSubmit);

        const token = sessionStorage.getItem('badbaado_token');
        if (token) window.location.href = '/dashboard';
    },
};