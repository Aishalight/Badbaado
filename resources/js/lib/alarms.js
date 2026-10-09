/**
 * Client-side clinical alarms.
 *
 * Polls for unread notifications newer than the highest id this browser has
 * already seen and escalates them the way the platform calendar escalates an
 * event: an OS notification, a tone, and a badge on the tab icon.
 *
 * Browser constraints, stated plainly so the behaviour is not misread:
 *  - Every channel here needs at least one open tab. Nothing here can wake a
 *    browser that is fully closed; that needs a service worker and a push
 *    service, which this build deliberately does not ship.
 *  - `Notification.permission` is per device, so the on/off preference is kept
 *    in localStorage next to it rather than on the user record, where it would
 *    claim to be enabled on a phone that was never asked for permission.
 */

import { api } from './api';

const ENABLED_KEY = 'badbaado.alarms.enabled';
const SEEN_KEY = 'badbaado.alarms.seen';
const POLL_MS = 5000;
const TITLE_IDLE_MS = 60000;
const CRITICAL_LOOP_MS = 6000;
const MAX_ALARM_MS = 5 * 60 * 1000;

const readStore = (key, fallback) => {
    try {
        const value = window.localStorage.getItem(key);

        return value === null ? fallback : JSON.parse(value);
    } catch {
        return fallback;
    }
};

const writeStore = (key, value) => {
    try {
        window.localStorage.setItem(key, JSON.stringify(value));
    } catch {
        // Private browsing can refuse writes; alarms still work for this page.
    }
};

let audioContext = null;
let criticalTimer = null;
let titleTimer = null;
let baseTitle = document.title;
let activeCritical = null;
let listeners = [];

const emit = (event, detail) => listeners.forEach((fn) => fn(event, detail));

export const isSupported = () => typeof window.Notification !== 'undefined';

export const permission = () => (isSupported() ? window.Notification.permission : 'unsupported');

export const isEnabled = () => readStore(ENABLED_KEY, false);

/**
 * Seeks OS permission. Must be called from a user gesture, otherwise browsers
 * reject the request outright.
 */
export async function enable() {
    if (! isSupported()) return 'unsupported';

    if (window.Notification.permission === 'default') {
        const result = await window.Notification.requestPermission();

        if (result !== 'granted') return result;
    }

    writeStore(ENABLED_KEY, true);

    return window.Notification.permission;
}

export function disable() {
    writeStore(ENABLED_KEY, false);
    silence();
}

export function onChange(handler) {
    listeners.push(handler);
}

const unlockAudio = () => {
    if (audioContext) {
        if (audioContext.state === 'suspended') audioContext.resume();

        return;
    }

    const Ctor = window.AudioContext ?? window.webkitAudioContext;

    if (! Ctor) return;

    audioContext = new Ctor();

    if (audioContext.state === 'suspended') audioContext.resume();
};

const tone = (frequency, startAt, duration, { gain = 0.16, type = 'sine' } = {}) => {
    if (! audioContext) return;

    const oscillator = audioContext.createOscillator();
    const envelope = audioContext.createGain();

    oscillator.type = type;
    oscillator.frequency.value = frequency;

    // A short ramp in and out keeps the tone from clicking at either edge.
    envelope.gain.setValueAtTime(0.0001, startAt);
    envelope.gain.exponentialRampToValueAtTime(gain, startAt + 0.015);
    envelope.gain.exponentialRampToValueAtTime(0.0001, startAt + duration);

    oscillator.connect(envelope).connect(audioContext.destination);
    oscillator.start(startAt);
    oscillator.stop(startAt + duration + 0.02);
};

/** Info and Announcement stay silent so routine traffic cannot drown an alarm. */
function play(severity) {
    unlockAudio();

    if (! audioContext) return;

    const at = audioContext.currentTime + 0.01;

    if (severity === 'warning') {
        // Single soft two-note chime.
        tone(880, at, 0.28, { gain: 0.09 });
        tone(1174, at + 0.2, 0.42, { gain: 0.09 });

        return;
    }

    if (severity !== 'critical') return;

    const ring = () => {
        const start = audioContext.currentTime + 0.01;

        tone(1046, start, 0.16, { gain: 0.2, type: 'triangle' });
        tone(784, start + 0.19, 0.16, { gain: 0.2, type: 'triangle' });
        tone(1046, start + 0.38, 0.3, { gain: 0.2, type: 'triangle' });
    };

    ring();
    criticalTimer = window.setInterval(ring, CRITICAL_LOOP_MS);
    window.setTimeout(silence, MAX_ALARM_MS);
}

function vibrate(severity) {
    if (! navigator.vibrate) return;

    if (severity === 'critical') {
        navigator.vibrate([160, 90, 160, 90, 320]);
    } else if (severity === 'warning') {
        navigator.vibrate(120);
    }
}

function badge(count) {
    let link = document.querySelector('link[rel~="icon"]');

    if (! link) {
        link = document.createElement('link');
        link.rel = 'icon';
        document.head.appendChild(link);
    }

    if (count < 1) {
        link.removeAttribute('data-alarm');
        link.href = '/favicon.ico';

        return;
    }

    const size = 64;
    const canvas = document.createElement('canvas');
    canvas.width = size;
    canvas.height = size;

    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#dc2626';
    ctx.beginPath();
    ctx.arc(size / 2, size / 2, size / 2, 0, Math.PI * 2);
    ctx.fill();

    ctx.fillStyle = '#ffffff';
    ctx.font = `700 ${count > 9 ? 30 : 38}px system-ui, sans-serif`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(count > 99 ? '99+' : String(count), size / 2, size / 2 + 2);

    link.dataset.alarm = 'true';
    link.href = canvas.toDataURL('image/png');
}

function flashTitle(text) {
    if (titleTimer) return;

    let on = false;

    titleTimer = window.setInterval(() => {
        on = ! on;
        document.title = on ? '[!] ' + text : baseTitle;
    }, 1000);

    // Whatever else happens, never leave a clinician staring at a flashing tab
    // forever.
    window.setTimeout(() => {
        clearInterval(titleTimer);
        titleTimer = null;
        document.title = baseTitle;
    }, TITLE_IDLE_MS);
}

export function silence() {
    if (criticalTimer) {
        clearInterval(criticalTimer);
        criticalTimer = null;
    }

    if (titleTimer) {
        clearInterval(titleTimer);
        titleTimer = null;
        document.title = baseTitle;
    }

    activeCritical = null;
}

/**
 * Stops the repeating critical alarm, which is what acknowledging the
 * notification on the notifications page amounts to.
 */
export function acknowledge() {
    if (! activeCritical) return;

    silence();
    badge(0);
    emit('acknowledged', { id: activeCritical });
}

/**
 * Plays one round of the critical alarm so a clinician can confirm they can
 * actually hear it before relying on it.
 */
export function preview(severity = 'critical') {
    unlockAudio();
    play(severity);
    vibrate(severity);

    if (isSupported() && window.Notification.permission === 'granted') {
        const native = new window.Notification('Test alarm from BADBAADO', {
            body: severity === 'critical'
                ? 'This is how a rejected referral will reach you.'
                : 'This is how a warning will reach you.',
            tag: 'badbaado-alarm-test',
            silent: true,
        });

        native.addEventListener('click', () => native.close());
    }

    // A preview must never leave the loop running or the tab title flashing.
    window.setTimeout(silence, CRITICAL_LOOP_MS);
}

function raise(notification) {
    const { severity, title, body, interrupting, id } = notification;

    if (severity === 'critical') {
        activeCritical = id;
        play(severity);
        vibrate(severity);
        flashTitle(title);
    } else if (severity === 'warning') {
        play(severity);
        vibrate(severity);
    }

    if (interrupting && isSupported() && window.Notification.permission === 'granted') {
        const native = new window.Notification(title, {
            body,
            tag: `badbaado-${id}`,
            requireInteraction: interrupting,
            silent: true, // The Web Audio tone above is the sound on purpose.
        });

        native.addEventListener('click', () => {
            window.focus();
            window.location.href = notification.referral_id
                ? `/referrals/${notification.referral_id}`
                : '/notifications';
            native.close();
        });
    }

    emit('alarm', notification);
}

let polling = null;

async function poll() {
    if (! isEnabled()) return;

    let after = readStore(SEEN_KEY, 0);

    try {
        const result = await api.get(`/notifications/pending?after=${after}`);
        const rows = result?.data ?? [];

        rows.forEach((notification) => {
            after = Math.max(after, notification.id);
            raise(notification);
        });

        if (rows.length) {
            writeStore(SEEN_KEY, after);
            badge(after);
        }
    } catch {
        // A failed poll is not worth surfacing; the next tick retries silently.
    }
}

export function start() {
    if (polling) return;

    baseTitle = document.title;

    poll();
    polling = window.setInterval(poll, POLL_MS);

    // An alarm that arrives while the tab is hidden still has to be seen when
    // the user comes back, so re-check on focus rather than trusting the timer.
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') poll();
    });
}

export function stop() {
    if (polling) {
        clearInterval(polling);
        polling = null;
    }

    silence();
}
