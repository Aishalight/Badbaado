/**
 * Registration form: keep the doctor-only and hospital-only fields in step
 * with the selected type.
 *
 * The server marks the other type's fields `prohibited`, so a value left
 * behind in a hidden field would fail validation on submit. Hiding is not
 * enough — the inputs are cleared too.
 */
export function initRegistrationTypeToggle() {
    const form = document.querySelector('[data-registration-form]');

    if (!form) return;

    const selectors = {
        doctor: '[data-registration-doctor]',
        hospital: '[data-registration-hospital]',
    };

    const sync = () => {
        const type = form.querySelector('input[name="type"]:checked')?.value;

        Object.entries(selectors).forEach(([key, selector]) => {
            const matches = key === type;

            form.querySelectorAll(selector).forEach((group) => {
                group.hidden = !matches;

                group.querySelectorAll('input, select').forEach((input) => {
                    input.disabled = !matches;
                    if (!matches) input.value = input.tagName === 'SELECT' ? '' : '';
                });
            });
        });
    };

    form.querySelectorAll('input[name="type"]').forEach((radio) => {
        radio.addEventListener('change', sync);
    });

    sync();
}