/**
 * Show/hide toggle for password fields.
 *
 * Each `.auth-password-wrap` is resolved to its own input, so a form can carry
 * several password fields without the toggles cross-wiring.
 */
export function initPasswordToggles() {
    document.querySelectorAll('.auth-password-wrap').forEach((wrap) => {
        const input = wrap.querySelector('input');
        const toggle = wrap.querySelector('.auth-password-toggle');

        if (!input || !toggle) return;

        toggle.addEventListener('click', () => {
            const reveal = input.type === 'password';

            input.type = reveal ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', String(reveal));
            toggle.setAttribute(
                'aria-label',
                reveal ? 'Hide password' : 'Show password',
            );
            toggle.setAttribute(
                'title',
                reveal ? 'Hide password' : 'Show password',
            );
            toggle.classList.toggle('is-visible', reveal);
        });
    });
}
