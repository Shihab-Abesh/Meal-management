/**
 * How new notifications announce themselves on this device: a short chime
 * and/or a desktop notification while the tab is in the background.
 * Choices are kept per browser, so a kitchen tablet can chime while the
 * owner's phone stays quiet.
 */
const SOUND_KEY = 'notifications.sound';
const DESKTOP_KEY = 'notifications.desktop';

function read(key, fallback) {
    try {
        const value = window.localStorage.getItem(key);

        return value === null ? fallback : value === 'on';
    } catch {
        return fallback;
    }
}

function write(key, enabled) {
    try {
        window.localStorage.setItem(key, enabled ? 'on' : 'off');
    } catch {
        // Private mode or blocked storage: the choice lasts for this page only.
    }
}

export const soundEnabled = () => read(SOUND_KEY, true);
export const setSoundEnabled = (enabled) => write(SOUND_KEY, enabled);

export function desktopSupported() {
    return typeof window !== 'undefined' && 'Notification' in window;
}

export const desktopEnabled = () => desktopSupported() && Notification.permission === 'granted' && read(DESKTOP_KEY, false);

/**
 * Ask the browser for permission (it must follow a click) and remember the choice.
 * Resolves to "granted", "denied" or "default".
 */
export async function enableDesktop(enabled) {
    if (!enabled || !desktopSupported()) {
        write(DESKTOP_KEY, false);

        return desktopSupported() ? Notification.permission : 'denied';
    }

    const permission = Notification.permission === 'default' ? await Notification.requestPermission() : Notification.permission;
    write(DESKTOP_KEY, permission === 'granted');

    return permission;
}

let audioContext = null;

/**
 * Two soft rising notes, generated on the fly (no audio file). Browsers only
 * allow sound after the person has interacted with the page.
 */
export function playChime() {
    try {
        audioContext ??= new (window.AudioContext || window.webkitAudioContext)();

        if (audioContext.state === 'suspended') {
            audioContext.resume();
        }

        const start = audioContext.currentTime;

        [659.25, 987.77].forEach((frequency, index) => {
            const oscillator = audioContext.createOscillator();
            const gain = audioContext.createGain();
            const at = start + index * 0.14;

            oscillator.type = 'sine';
            oscillator.frequency.value = frequency;
            gain.gain.setValueAtTime(0.0001, at);
            gain.gain.exponentialRampToValueAtTime(0.18, at + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, at + 0.35);
            oscillator.connect(gain).connect(audioContext.destination);
            oscillator.start(at);
            oscillator.stop(at + 0.4);
        });
    } catch {
        // No audio support: the toast is enough.
    }
}

/**
 * A system notification for a background tab; clicking it focuses the app
 * and opens what the notification is about.
 */
export function showDesktopNotification({ id, title, body, href }) {
    if (!desktopEnabled()) {
        return;
    }

    try {
        const notification = new Notification(title, { body, tag: id });
        notification.onclick = () => {
            window.focus();
            window.location.href = href;
            notification.close();
        };
    } catch {
        // Some mobile browsers only allow notifications from a service worker.
    }
}
