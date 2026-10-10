import { Head, Link, router, useForm } from '@inertiajs/react';
import { BellOff, BellRing, CheckCheck, Circle, CircleDot, Trash2, Volume2 } from 'lucide-react';
import { useState } from 'react';
import NotificationIcon from '@/components/notifications/notification-icon';
import Alert from '@/components/ui/alert';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import ConfirmDialog from '@/components/ui/confirm-dialog';
import EmptyState from '@/components/ui/empty-state';
import PageHeader from '@/components/ui/page-header';
import Pagination from '@/components/ui/pagination';
import Toggle from '@/components/ui/toggle';
import { useFilters } from '@/hooks/use-filters';
import { formatLongDate, formatRelative, parseDate } from '@/lib/format';
import { desktopEnabled, desktopSupported, enableDesktop, playChime, setSoundEnabled, soundEnabled } from '@/lib/notification-alerts';
import { cn } from '@/lib/utils';

const CATEGORY_LABELS = { orders: 'Orders', deliveries: 'Deliveries', billing: 'Billing' };

function dayLabel(iso) {
    const date = parseDate(iso);
    const today = new Date();
    const yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);

    if (date.toDateString() === today.toDateString()) {
        return 'Today';
    }

    return date.toDateString() === yesterday.toDateString() ? 'Yesterday' : formatLongDate(date);
}

/**
 * Every notification of the signed-in user, newest first and grouped by
 * day, with read state, filters and the alerts they want.
 */
export default function Notifications({ notifications, filters, counts, preferences, categories }) {
    const { values, set, loading } = useFilters(route('notifications.index'), filters);
    const [clearOpen, setClearOpen] = useState(false);
    const [busy, setBusy] = useState(null);

    const groups = notifications.data.reduce((result, item) => {
        const label = dayLabel(item.created_at);
        (result[label] ??= []).push(item);

        return result;
    }, {});

    function markAll() {
        router.post(route('notifications.read-all'), {}, { preserveScroll: true, onStart: () => setBusy('all'), onFinish: () => setBusy(null) });
    }

    function toggleRead(item) {
        router.patch(route('notifications.update', item.id), { read: !item.read }, { preserveScroll: true, onStart: () => setBusy(item.id), onFinish: () => setBusy(null) });
    }

    function clearRead() {
        router.delete(route('notifications.clear-read'), {
            preserveScroll: true,
            onStart: () => setBusy('clear'),
            onFinish: () => {
                setBusy(null);
                setClearOpen(false);
            },
        });
    }

    return (
        <>
            <Head title="Notifications" />

            <PageHeader
                title="Notifications"
                description="Client orders, deliveries and billing alerts for you."
                actions={
                    <div className="flex w-full gap-2 sm:w-auto">
                        <Button variant="secondary" icon={CheckCheck} loading={busy === 'all'} disabled={counts.unread === 0} onClick={markAll} className="flex-1 sm:flex-none">
                            Mark all as read
                        </Button>
                        <Button variant="ghost" icon={Trash2} disabled={counts.all === counts.unread} onClick={() => setClearOpen(true)} className="flex-1 text-red-600 hover:text-red-700 sm:flex-none">
                            Clear read
                        </Button>
                    </div>
                }
            />

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="min-w-0 lg:col-span-2">
                    <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                        <div className="flex gap-2" role="group" aria-label="Read state">
                        {[
                            { value: 'all', label: 'All', count: counts.all },
                            { value: 'unread', label: 'Unread', count: counts.unread },
                        ].map((tab) => (
                            <button
                                key={tab.value}
                                type="button"
                                aria-pressed={values.show === tab.value}
                                onClick={() => set('show', tab.value)}
                                className={cn(
                                    'inline-flex min-h-10 items-center gap-2 rounded-full px-4 text-sm font-medium ring-1 ring-inset',
                                    values.show === tab.value ? 'bg-primary-600 text-white ring-primary-600' : 'bg-white text-slate-700 ring-slate-300 hover:bg-slate-50',
                                )}
                            >
                                {tab.label}
                                <span className="tabular-nums opacity-80">{tab.count}</span>
                            </button>
                        ))}
                        </div>
                        {categories.length > 1 && (
                            <div className="flex flex-wrap items-center gap-1" role="group" aria-label="Category">
                                <span className="mx-1 hidden h-6 w-px bg-slate-200 sm:block" aria-hidden="true" />
                                {[{ value: '', label: 'Everything' }, ...categories.map((category) => ({ value: category.value, label: CATEGORY_LABELS[category.value] }))].map((category) => (
                                    <button
                                        key={category.value || 'everything'}
                                        type="button"
                                        aria-pressed={values.category === category.value}
                                        onClick={() => set('category', category.value)}
                                        className={cn(
                                            'inline-flex min-h-10 items-center rounded-full px-3.5 text-sm font-medium',
                                            values.category === category.value ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100',
                                        )}
                                    >
                                        {category.label}
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>

                    <div className={cn('space-y-6 transition-opacity', loading && 'opacity-60')} aria-busy={loading || undefined}>
                        {notifications.data.length === 0 ? (
                            <Card>
                                <EmptyState
                                    icon={BellOff}
                                    title={values.show === 'unread' ? 'You are all caught up' : 'No notifications yet'}
                                    description={
                                        values.show === 'unread'
                                            ? 'Nothing new since you last looked.'
                                            : 'When a client places or changes an order, you will be notified here and by the bell at the top.'
                                    }
                                />
                            </Card>
                        ) : (
                            Object.entries(groups).map(([label, items]) => (
                                <section key={label} aria-labelledby={`day-${label}`}>
                                    <h2 id={`day-${label}`} className="mb-2 text-xs font-semibold tracking-wide text-slate-500 uppercase">
                                        {label}
                                    </h2>
                                    <Card as="ul" className="divide-y divide-slate-100 overflow-hidden">
                                        {items.map((item) => (
                                            <li key={item.id} className={cn('flex items-start gap-1 pr-2', !item.read && 'bg-primary-50/40')}>
                                                <Link href={route('notifications.open', item.id)} className="flex min-w-0 flex-1 gap-3 py-3.5 pl-4 hover:bg-slate-50/60 sm:pl-5">
                                                    <NotificationIcon kind={item.kind} />
                                                    <span className="min-w-0 flex-1">
                                                        <span className={cn('block text-sm', item.read ? 'text-slate-700' : 'font-semibold text-slate-900')}>{item.title}</span>
                                                        <span className="mt-0.5 block text-sm text-slate-500">{item.body}</span>
                                                        <time dateTime={item.created_at} className="mt-1 block text-xs text-slate-400">
                                                            {formatRelative(item.created_at)}
                                                        </time>
                                                    </span>
                                                </Link>
                                                <button
                                                    type="button"
                                                    onClick={() => toggleRead(item)}
                                                    disabled={busy === item.id}
                                                    title={item.read ? 'Mark as unread' : 'Mark as read'}
                                                    className="mt-2 flex size-11 shrink-0 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-2 focus-visible:outline-primary-600 disabled:opacity-50"
                                                >
                                                    <span className="sr-only">{item.read ? 'Mark as unread' : 'Mark as read'}</span>
                                                    {item.read ? <Circle className="size-4" aria-hidden="true" /> : <CircleDot className="size-4 text-primary-600" aria-hidden="true" />}
                                                </button>
                                            </li>
                                        ))}
                                    </Card>
                                </section>
                            ))
                        )}
                        {notifications.meta.last_page > 1 && (
                            <Card>
                                <Pagination meta={notifications.meta} links={notifications.links} className="border-t-0" />
                            </Card>
                        )}
                    </div>
                </div>

                <div className="min-w-0 space-y-6">
                    <AlertPreferences preferences={preferences} categories={categories} />
                    <DeviceSettings />
                </div>
            </div>

            <ConfirmDialog
                open={clearOpen}
                onClose={() => setClearOpen(false)}
                onConfirm={clearRead}
                processing={busy === 'clear'}
                title="Clear read notifications?"
                description="Notifications you have already read are removed from this list. Unread ones stay."
                confirmLabel="Clear read"
            />
        </>
    );
}

function AlertPreferences({ preferences, categories }) {
    const form = useForm(Object.fromEntries(categories.map((category) => [category.value, preferences[category.value]])));

    if (categories.length === 0) {
        return null;
    }

    return (
        <Card
            as="form"
            onSubmit={(event) => {
                event.preventDefault();
                form.put(route('notifications.preferences'), { preserveScroll: true, onSuccess: () => form.setDefaults() });
            }}
        >
            <CardHeader title="Notify me about" description="Applies to your account on every device." />
            <CardBody className="divide-y divide-slate-100 py-2">
                {categories.map((category) => (
                    <Toggle
                        key={category.value}
                        className="py-2"
                        label={category.label}
                        description={category.description}
                        checked={form.data[category.value]}
                        onChange={(value) => form.setData(category.value, value)}
                    />
                ))}
            </CardBody>
            <div className="flex justify-end border-t border-slate-100 px-4 py-3 sm:px-6">
                <Button type="submit" size="sm" loading={form.processing} disabled={!form.isDirty}>
                    Save
                </Button>
            </div>
        </Card>
    );
}

/**
 * Sound and desktop alerts belong to this browser, not the account: the
 * kitchen tablet can chime while a phone stays quiet.
 */
function DeviceSettings() {
    const [sound, setSound] = useState(soundEnabled);
    const [desktop, setDesktop] = useState(desktopEnabled);
    const [permission, setPermission] = useState(desktopSupported() ? Notification.permission : 'unsupported');

    async function toggleDesktop(enabled) {
        const result = await enableDesktop(enabled);
        setPermission(desktopSupported() ? Notification.permission : 'unsupported');
        setDesktop(enabled && result === 'granted');
    }

    return (
        <Card>
            <CardHeader title="On this device" description="How new notifications get your attention here." />
            <CardBody className="space-y-3 py-2">
                <div className="divide-y divide-slate-100">
                    <Toggle
                        className="py-2"
                        label="Sound"
                        description="A short chime when something new arrives."
                        checked={sound}
                        onChange={(value) => {
                            setSoundEnabled(value);
                            setSound(value);

                            if (value) {
                                playChime();
                            }
                        }}
                    />
                    <Toggle
                        className="py-2"
                        label="Desktop alerts"
                        description="Pop-ups from your browser while this tab is in the background."
                        checked={desktop}
                        disabled={permission === 'unsupported' || permission === 'denied'}
                        onChange={toggleDesktop}
                    />
                </div>
                {permission === 'denied' && (
                    <Alert tone="info">Your browser blocks notifications for this site. Allow them in the browser's site settings, then switch this on.</Alert>
                )}
                {permission === 'unsupported' && <Alert tone="info">This browser does not support desktop alerts.</Alert>}
                <div className="flex flex-wrap gap-2 pb-2">
                    <Button variant="secondary" size="sm" icon={Volume2} onClick={playChime} disabled={!sound}>
                        Test sound
                    </Button>
                    <Button
                        variant="secondary"
                        size="sm"
                        icon={BellRing}
                        disabled={!desktop}
                        onClick={() => new Notification('Notifications are on', { body: 'New client orders will appear like this.' })}
                    >
                        Test alert
                    </Button>
                </div>
            </CardBody>
        </Card>
    );
}
