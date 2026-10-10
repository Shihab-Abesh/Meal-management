import { router, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { playChime, showDesktopNotification, soundEnabled } from '@/lib/notification-alerts';
import { showToast } from '@/lib/toast';

const VISIBLE_DELAY = 20_000;
// Browsers slow down background timers anyway; this still catches new orders for desktop alerts.
const HIDDEN_DELAY = 60_000;
const MAX_ANNOUNCED = 3;
const TITLE_BADGE = /^\(\d+\+?\)\s/;

function xsrfToken() {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);

    return match ? decodeURIComponent(match[1]) : '';
}

/**
 * The signed-in vendor user's notifications, kept current while the app is
 * open: polls the feed, announces new arrivals (toast, chime, desktop
 * notification in the background) and shows the unread count in the tab
 * title. Lives in the persistent layout, so it survives page visits.
 */
export function useNotificationFeed() {
    const page = usePage();
    const { notifications } = page.props;
    const { component } = page;
    const enabled = Boolean(notifications);
    const [unread, setUnread] = useState(notifications?.unread ?? 0);
    const [items, setItems] = useState([]);
    const [loaded, setLoaded] = useState(false);
    const known = useRef(null);
    const stopped = useRef(false);

    // Every page response brings a fresh count from the server (e.g. after
    // opening a notification), even when it equals the previous page's.
    useEffect(() => {
        if (enabled) {
            setUnread(notifications.unread);
        }
    }, [enabled, notifications]);

    const refresh = useCallback(async () => {
        if (!enabled || stopped.current) {
            return;
        }

        try {
            const response = await fetch(route('notifications.feed'), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            // Signed out or session expired: stop quietly, the next page visit sorts it out.
            if (response.redirected || [401, 403, 419].includes(response.status)) {
                stopped.current = true;

                return;
            }

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            setUnread(data.unread);
            setItems(data.items);
            setLoaded(true);

            if (known.current === null) {
                // First load: what is already there is not news.
                known.current = new Set(data.items.map((item) => item.id));

                return;
            }

            const arrivals = data.items.filter((item) => !known.current.has(item.id) && !item.read);
            data.items.forEach((item) => known.current.add(item.id));
            announce(arrivals);
        } catch {
            // Offline for a moment: the next poll tries again.
        }
    }, [enabled]);

    // Poll faster while the tab is visible, slower in the background.
    useEffect(() => {
        if (!enabled) {
            return undefined;
        }

        let timer;
        const schedule = () => {
            timer = setTimeout(async () => {
                await refresh();
                schedule();
            }, document.hidden ? HIDDEN_DELAY : VISIBLE_DELAY);
        };
        const onVisibility = () => {
            if (!document.hidden) {
                clearTimeout(timer);
                refresh().then(schedule);
            }
        };

        refresh().then(schedule);
        document.addEventListener('visibilitychange', onVisibility);

        return () => {
            clearTimeout(timer);
            document.removeEventListener('visibilitychange', onVisibility);
        };
    }, [enabled, refresh]);

    useTitleBadge(enabled ? unread : 0);

    const markAllRead = useCallback(async () => {
        setUnread(0);
        setItems((current) => current.map((item) => ({ ...item, read: true })));

        try {
            await fetch(route('notifications.read-all'), {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-XSRF-TOKEN': xsrfToken() },
                credentials: 'same-origin',
            });
        } finally {
            // Keep the notifications page in step when it is open.
            if (component === 'notifications/index') {
                router.reload({ only: ['notifications', 'counts'] });
            }
        }
    }, [component]);

    return { enabled, unread, items, loaded, refresh, markAllRead };
}

function announce(arrivals) {
    if (arrivals.length === 0) {
        return;
    }

    arrivals.slice(0, MAX_ANNOUNCED).forEach((item) => {
        const href = route('notifications.open', item.id);

        showToast({ type: 'notification', title: item.title, message: item.body, href });

        if (document.hidden) {
            showDesktopNotification({ id: item.id, title: item.title, body: item.body, href });
        }
    });

    if (soundEnabled()) {
        playChime();
    }
}

/**
 * "(3) Orders · ABC Catering" in the browser tab while there are unread
 * notifications. Re-applied whenever a page changes the title.
 */
function useTitleBadge(count) {
    useEffect(() => {
        const apply = () => {
            const base = document.title.replace(TITLE_BADGE, '');
            const next = count > 0 ? `(${count > 99 ? '99+' : count}) ${base}` : base;

            if (document.title !== next) {
                document.title = next;
            }
        };

        apply();
        const observer = new MutationObserver(apply);
        observer.observe(document.head, { childList: true, subtree: true, characterData: true });

        return () => {
            observer.disconnect();
            document.title = document.title.replace(TITLE_BADGE, '');
        };
    }, [count]);
}
