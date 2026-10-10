import { CloseButton, Popover, PopoverButton, PopoverPanel } from '@headlessui/react';
import { Link } from '@inertiajs/react';
import { Bell, BellOff, CheckCheck } from 'lucide-react';
import NotificationIcon from '@/components/notifications/notification-icon';
import Spinner from '@/components/ui/spinner';
import { useNotificationFeed } from '@/hooks/use-notification-feed';
import { formatRelative } from '@/lib/format';
import { cn } from '@/lib/utils';

/**
 * The bell in the top bar: unread badge, newest notifications, mark all as
 * read, and the way to the full list. Opening a notification marks it read
 * and goes to the order or bill it is about.
 */
export default function NotificationBell() {
    const { enabled, unread, items, loaded, refresh, markAllRead } = useNotificationFeed();

    if (!enabled) {
        return null;
    }

    const badge = unread > 99 ? '99+' : unread;

    return (
        <Popover className="relative">
            <PopoverButton
                onClick={refresh}
                className="relative flex size-11 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-primary-600 data-open:bg-slate-100"
                aria-label={unread ? `Notifications, ${unread} unread` : 'Notifications'}
            >
                {/* Keyed on the count so the bell swings again whenever new ones arrive. */}
                <Bell key={unread} className={cn('size-6', unread > 0 && 'origin-top animate-[bell-ring_1s_ease-in-out_1]')} aria-hidden="true" />
                {unread > 0 && (
                    <span className="absolute top-1.5 right-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-bold text-white ring-2 ring-white tabular-nums">
                        {badge}
                    </span>
                )}
            </PopoverButton>

            <PopoverPanel
                transition
                anchor="bottom end"
                className="z-50 flex max-h-[min(32rem,calc(100dvh-5rem))] w-[min(24rem,calc(100vw-1.5rem))] flex-col overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-slate-900/10 transition duration-150 ease-out [--anchor-gap:8px] [--anchor-padding:12px] data-closed:-translate-y-1 data-closed:opacity-0"
            >
                <div className="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3">
                    <div>
                        <h2 className="text-sm font-semibold text-slate-900">Notifications</h2>
                        <p className="text-xs text-slate-500">{unread ? `${unread} unread` : 'You are all caught up'}</p>
                    </div>
                    {unread > 0 && (
                        <button
                            type="button"
                            onClick={markAllRead}
                            className="inline-flex min-h-9 items-center gap-1.5 rounded-lg px-2 text-xs font-semibold text-primary-700 hover:bg-primary-50"
                        >
                            <CheckCheck className="size-4" aria-hidden="true" />
                            Mark all as read
                        </button>
                    )}
                </div>

                <div className="min-h-0 flex-1 overflow-y-auto">
                    {!loaded ? (
                        <div className="flex justify-center py-10 text-primary-600">
                            <Spinner label="Loading notifications" />
                        </div>
                    ) : items.length === 0 ? (
                        <div className="flex flex-col items-center px-6 py-10 text-center">
                            <span className="flex size-11 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                <BellOff className="size-5" aria-hidden="true" />
                            </span>
                            <p className="mt-3 text-sm font-medium text-slate-900">No notifications yet</p>
                            <p className="mt-1 text-xs text-slate-500">New client orders and deliveries will show up here.</p>
                        </div>
                    ) : (
                        <ul className="divide-y divide-slate-100">
                            {items.map((item) => (
                                <li key={item.id}>
                                    <CloseButton
                                        as={Link}
                                        href={route('notifications.open', item.id)}
                                        className={cn('flex gap-3 px-4 py-3 text-left hover:bg-slate-50', !item.read && 'bg-primary-50/40')}
                                    >
                                        <NotificationIcon kind={item.kind} />
                                        <span className="min-w-0 flex-1">
                                            <span className={cn('block text-sm', item.read ? 'text-slate-700' : 'font-semibold text-slate-900')}>{item.title}</span>
                                            <span className="mt-0.5 block text-xs text-slate-500">{item.body}</span>
                                            <span className="mt-1 block text-[11px] font-medium text-slate-400">{formatRelative(item.created_at)}</span>
                                        </span>
                                        {!item.read && <span className="mt-1.5 size-2 shrink-0 rounded-full bg-primary-600" aria-label="Unread" />}
                                    </CloseButton>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>

                <CloseButton
                    as={Link}
                    href={route('notifications.index')}
                    className="block border-t border-slate-100 px-4 py-3 text-center text-sm font-semibold text-primary-700 hover:bg-slate-50"
                >
                    View all notifications
                </CloseButton>
            </PopoverPanel>
        </Popover>
    );
}
