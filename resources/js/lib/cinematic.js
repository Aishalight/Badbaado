const reducedMotion = () =>
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

const scrollTargets = [];
let scrollQueued = false;

const runScroll = () => {
    scrollQueued = false;
    const y = window.scrollY;

    scrollTargets.forEach((fn) => fn(y));
};

const onScroll = (fn) => {
    scrollTargets.push(fn);
};

const requestScroll = () => {
    if (scrollQueued) {
        return;
    }

    scrollQueued = true;
    window.requestAnimationFrame(runScroll);
};

const initProgress = () => {
    const bar = document.querySelector('[data-progress]');

    if (!bar) {
        return;
    }

    onScroll(() => {
        const max = document.documentElement.scrollHeight - window.innerHeight;
        const ratio = max > 0 ? clamp(window.scrollY / max, 0, 1) : 0;
        bar.style.transform = `scaleX(${ratio})`;
    });
};

const initHeader = () => {
    const header = document.querySelector('[data-header]');

    if (!header) {
        return;
    }

    onScroll((y) => {
        header.classList.toggle('is-scrolled', y > 12);
    });
};

const initMobileMenu = () => {
    const toggle = document.querySelector('[data-menu-toggle]');
    const menu = document.querySelector('[data-mobile-menu]');

    if (!toggle || !menu) {
        return;
    }

    const close = () => {
        menu.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
    };

    toggle.addEventListener('click', () => {
        const open = menu.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', close));

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
        }
    });
};

const initCounters = (reduced) => {
    const counters = document.querySelectorAll('[data-counter]');

    if (!counters.length) {
        return;
    }

    const render = (el, value) => {
        el.textContent = Math.round(value).toLocaleString();
    };

    if (reduced || !('IntersectionObserver' in window)) {
        counters.forEach((el) => render(el, Number(el.dataset.counter || 0)));

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                const el = entry.target;
                const target = Number(el.dataset.counter || 0);
                const duration = 1500;
                const start = performance.now();

                const tick = (now) => {
                    const progress = clamp((now - start) / duration, 0, 1);
                    const eased = 1 - Math.pow(1 - progress, 4);
                    render(el, target * eased);

                    if (progress < 1) {
                        window.requestAnimationFrame(tick);
                    }
                };

                window.requestAnimationFrame(tick);
                observer.unobserve(el);
            });
        },
        { threshold: 0.4 }
    );

    counters.forEach((el) => observer.observe(el));
};

const initTilt = (reduced) => {
    if (reduced || !window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        return;
    }

    document.querySelectorAll('[data-tilt]').forEach((el) => {
        const strength = Number(el.dataset.tilt || 7);

        const reset = () => {
            el.classList.remove('is-tilting');
            el.style.setProperty('--rx', '0deg');
            el.style.setProperty('--ry', '0deg');
        };

        el.addEventListener('pointermove', (event) => {
            const rect = el.getBoundingClientRect();
            const px = (event.clientX - rect.left) / rect.width - 0.5;
            const py = (event.clientY - rect.top) / rect.height - 0.5;

            el.classList.add('is-tilting');
            el.style.setProperty('--ry', `${(px * strength * 2).toFixed(2)}deg`);
            el.style.setProperty('--rx', `${(-py * strength * 2).toFixed(2)}deg`);
        });

        el.addEventListener('pointerleave', reset);
        el.addEventListener('blur', reset);
    });
};

const initSpotlight = (reduced) => {
    if (reduced || !window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        return;
    }

    document.querySelectorAll('[data-spotlight]').forEach((el) => {
        el.addEventListener('pointermove', (event) => {
            const rect = el.getBoundingClientRect();
            el.style.setProperty('--mx', `${(((event.clientX - rect.left) / rect.width) * 100).toFixed(1)}%`);
            el.style.setProperty('--my', `${(((event.clientY - rect.top) / rect.height) * 100).toFixed(1)}%`);
        });
    });
};

const initMagnetic = (reduced) => {
    if (reduced || !window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        return;
    }

    document.querySelectorAll('[data-magnetic]').forEach((el) => {
        const pull = Number(el.dataset.magnetic || 14);

        el.addEventListener('pointermove', (event) => {
            const rect = el.getBoundingClientRect();
            const x = event.clientX - (rect.left + rect.width / 2);
            const y = event.clientY - (rect.top + rect.height / 2);

            el.style.transform = `translate3d(${(x / rect.width) * pull}px, ${(y / rect.height) * pull}px, 0)`;
        });

        el.addEventListener('pointerleave', () => {
            el.style.transform = '';
        });
    });
};

const initParallax = (reduced) => {
    const layers = document.querySelectorAll('[data-parallax]');

    if (!layers.length || reduced) {
        return;
    }

    const active = new Set();

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        active.add(entry.target);
                    } else {
                        active.delete(entry.target);
                    }
                });
            },
            { rootMargin: '20% 0px 20% 0px' }
        );

        layers.forEach((layer) => observer.observe(layer));
    } else {
        layers.forEach((layer) => active.add(layer));
    }

    onScroll(() => {
        const mid = window.innerHeight / 2;

        active.forEach((layer) => {
            const speed = Number(layer.dataset.parallax || 0.1);
            const rect = layer.getBoundingClientRect();
            const offset = (rect.top + rect.height / 2 - mid) * speed;

            layer.style.transform = `translate3d(0, ${offset.toFixed(1)}px, 0)`;
        });
    });
};

const initScrollTop = (reduced) => {
    const button = document.querySelector('[data-scroll-top]');

    if (!button) {
        return;
    }

    const toggle = () => button.classList.toggle('is-visible', window.scrollY > 600);

    onScroll(toggle);
    toggle();

    button.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: reduced ? 'auto' : 'smooth' });
    });
};

const initScene = (reduced) => {
    const scenes = document.querySelectorAll('[data-scene]');

    if (!scenes.length) {
        return;
    }

    scenes.forEach((scene) => {
        const steps = Array.from(scene.querySelectorAll('[data-scene-step]'));
        const panels = Array.from(scene.querySelectorAll('[data-scene-panel]'));

        if (!steps.length) {
            return;
        }

        if (reduced) {
            steps.forEach((step) => step.classList.add('is-active'));
            panels.forEach((panel) => panel.classList.add('is-active'));

            return;
        }

        const activate = (index) => {
            steps.forEach((step, i) => step.classList.toggle('is-active', i === index));
            panels.forEach((panel, i) => panel.classList.toggle('is-active', i === index));
        };

        activate(0);

        onScroll(() => {
            const anchor = window.innerHeight * 0.45;
            let activeIndex = 0;
            let closest = Infinity;

            steps.forEach((step, i) => {
                const rect = step.getBoundingClientRect();
                const distance = Math.abs(rect.top + rect.height / 2 - anchor);

                if (distance < closest) {
                    closest = distance;
                    activeIndex = i;
                }
            });

            activate(activeIndex);
        });
    });
};

const THEME_KEY = 'badbaado-theme';

export const initThemeToggle = () => {
    const toggles = document.querySelectorAll('[data-theme-toggle]');

    if (!toggles.length) {
        return;
    }

    const syncFlag = () => {
        document.documentElement.classList.toggle(
            'theme-light',
            (() => {
                try {
                    return localStorage.getItem(THEME_KEY) === 'light';
                } catch (error) {
                    return false;
                }
            })()
        );
    };

    const apply = (light) => {
        document.documentElement.classList.toggle('theme-light', light);

        try {
            localStorage.setItem(THEME_KEY, light ? 'light' : 'dark');
        } catch (error) {
            /* Storage is unavailable: theme still flips for this session */
        }

        const meta = document.querySelector('meta[name="theme-color"]');

        if (meta) {
            meta.setAttribute('content', light ? '#e9f1fa' : '#040B15');
        }

        window.dispatchEvent(new CustomEvent('badbaado:theme-change', { detail: { light } }));
        document.documentElement.classList.add('theme-ready');
    };

    toggles.forEach((toggle) =>
        toggle.addEventListener('click', () => {
            apply(!document.documentElement.classList.contains('theme-light'));
            syncFlag();
        })
    );

    window.addEventListener('storage', (event) => {
        if (event.key === THEME_KEY) {
            syncFlag();
        }
    });

    document.documentElement.classList.add('theme-ready');
};

export const initCinematic = () => {
    const reduced = reducedMotion();

    initProgress();
    initHeader();
    initMobileMenu();
    initCounters(reduced);
    initTilt(reduced);
    initSpotlight(reduced);
    initMagnetic(reduced);
    initParallax(reduced);
    initScene(reduced);
    initScrollTop(reduced);
    initThemeToggle();

    window.addEventListener('scroll', requestScroll, { passive: true });
    window.addEventListener('resize', requestScroll, { passive: true });

    document.documentElement.classList.add('m-ready');
    requestScroll();
};
