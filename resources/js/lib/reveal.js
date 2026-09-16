export const initReveal = () => {
    const els = document.querySelectorAll('.reveal');
    if (!els.length) return;

    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const show = (el) => {
        el.classList.add('is-visible');
    };

    if (reduced || !('IntersectionObserver' in window)) {
        els.forEach(show);
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    show(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -8% 0px' }
    );

    els.forEach((el) => observer.observe(el));
};