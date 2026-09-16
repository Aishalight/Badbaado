import '../css/app.css';
import { api } from './lib/api';
import { loginPage } from './pages/login';
import { appShell } from './app-shell';
import { renderToast } from './lib/toast';
import { initReveal } from './lib/reveal';

const boot = () => {
    const hasToken = Boolean(sessionStorage.getItem('badbaado_token'));

    if (window.BADBAADO.loginOnly) {
        loginPage.init();
        return;
    }

    if (window.BADBAADO.publicPage) {
        initReveal();
        return;
    }

    if (!hasToken) {
        window.location.href = '/login';
        return;
    }

    appShell.mount();

    api.get('/me')
        .then((user) => appShell.setUser(user))
        .catch(() => {
            sessionStorage.removeItem('badbaado_token');
            window.location.href = '/login';
        });
};

document.addEventListener('DOMContentLoaded', boot);

window.addEventListener('unhandledrejection', (event) => {
    renderToast(event.reason?.message ?? 'Something went wrong.', 'error');
});