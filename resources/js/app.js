import '../css/app.css';
import '../css/theme.css';
import '../css/marketing.css';
import { loginPage } from './pages/login';
import { renderToast } from './lib/toast';
import { initReveal } from './lib/reveal';
import { initCinematic } from './lib/cinematic';

const boot = () => {
    const page = window.BADBAADO ?? {};

    if (page.loginOnly) {
        loginPage.init();
        initCinematic();
        return;
    }

    if (page.publicPage) {
        initReveal();
        initCinematic();
        return;
    }

};

document.addEventListener('DOMContentLoaded', boot);

window.addEventListener('unhandledrejection', (event) => {
    renderToast(event.reason?.message ?? 'Something went wrong.', 'error');
});