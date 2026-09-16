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

async function handleDemoLogin(event) {
    const button = event.currentTarget;
    button.disabled = true;

    try {
        const result = await api.post('/login', {
            email: button.dataset.demoEmail,
            password: 'password',
        });
        setToken(result.token);
        renderToast(`Signed in as ${result.user.name}`);
        window.location.href = '/dashboard';
    } catch (error) {
        renderToast(error.message, 'error');
        button.disabled = false;
    }
}

export const loginPage = {
    init() {
        const form = document.querySelector('#login-form');
        if (form) form.addEventListener('submit', handleSubmit);

        document.querySelectorAll('.demo-login').forEach((button) => {
            button.addEventListener('click', handleDemoLogin);
        });

        const token = sessionStorage.getItem('badbaado_token');
        if (token) window.location.href = '/dashboard';
    },
};