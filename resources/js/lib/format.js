const LOCALE = 'en-GB';

const numberFormatter = new Intl.NumberFormat('en-US', { maximumFractionDigits: 2 });

export function formatNumber(value) {
    return numberFormatter.format(Number(value ?? 0));
}

/**
 * formatCurrency(2640, '৳') -> "৳2,640"
 */
export function formatCurrency(value, symbol = '') {
    return `${symbol}${formatNumber(value)}`;
}

/**
 * Parse "YYYY-MM-DD" as a local calendar date (not UTC midnight), or an
 * ISO timestamp as-is.
 */
export function parseDate(value) {
    if (!value) {
        return null;
    }

    if (value instanceof Date) {
        return value;
    }

    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);

    if (match) {
        return new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
    }

    const date = new Date(value);

    return Number.isNaN(date.getTime()) ? null : date;
}

/**
 * "06 Oct 2026"
 */
export function formatDate(value) {
    const date = parseDate(value);

    return date ? date.toLocaleDateString(LOCALE, { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
}

/**
 * "Tuesday, 06 October 2026"
 */
export function formatLongDate(value) {
    const date = parseDate(value);

    return date
        ? date.toLocaleDateString(LOCALE, { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' })
        : '—';
}

/**
 * "14:30" or "14:30:00" -> "2:30 PM"
 */
export function formatTime(value) {
    const match = /^(\d{1,2}):(\d{2})/.exec(value ?? '');

    if (!match) {
        return '—';
    }

    const hours = Number(match[1]);
    const suffix = hours >= 12 ? 'PM' : 'AM';

    return `${hours % 12 || 12}:${match[2]} ${suffix}`;
}

/**
 * "just now", "5 min ago", "3 h ago", "Yesterday, 4:10 pm", "06 Oct, 9:15 am"
 */
export function formatRelative(value, now = new Date()) {
    const date = parseDate(value);

    if (!date) {
        return '—';
    }

    const seconds = Math.round((now - date) / 1000);

    if (seconds < 45) {
        return 'just now';
    }

    if (seconds < 3600) {
        return `${Math.round(seconds / 60)} min ago`;
    }

    const time = date.toLocaleTimeString(LOCALE, { hour: 'numeric', minute: '2-digit', hour12: true });
    const yesterday = new Date(now);
    yesterday.setDate(now.getDate() - 1);

    if (date.toDateString() === now.toDateString()) {
        return seconds < 6 * 3600 ? `${Math.round(seconds / 3600)} h ago` : `Today, ${time}`;
    }

    if (date.toDateString() === yesterday.toDateString()) {
        return `Yesterday, ${time}`;
    }

    const sameYear = date.getFullYear() === now.getFullYear();

    return `${date.toLocaleDateString(LOCALE, { day: '2-digit', month: 'short', ...(sameYear ? {} : { year: 'numeric' }) })}, ${time}`;
}

/**
 * "06 Oct 2026, 10:30 am"
 */
export function formatDateTime(value) {
    const date = parseDate(value);

    return date
        ? date.toLocaleString(LOCALE, {
              day: '2-digit',
              month: 'short',
              year: 'numeric',
              hour: 'numeric',
              minute: '2-digit',
              hour12: true,
          })
        : '—';
}
