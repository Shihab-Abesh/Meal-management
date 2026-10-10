import { Link, usePage } from '@inertiajs/react';
import { Bell, CircleAlert, CircleCheck, Info, X } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { cn } from '@/lib/utils';

const DURATION = 4000;
// Notifications stay a little longer: they are news, not confirmations.
const NOTIFICATION_DURATION = 8000;

const tones = {
    success: { icon: CircleCheck, classes: 'text-emerald-600' },
    error: { icon: CircleAlert, classes: 'text-red-600' },
    info: { icon: Info, classes: 'text-sky-600' },
    notification: { icon: Bell, classes: 'text-primary-600' },
};

/**
 * Shows the `toast` flash data sent by the server (see Controller::toast())
 * and toasts raised in the browser with showToast() (lib/toast.js), e.g. a
 * new notification. Every response carries a new flash object, so the same
 * message can be shown again on the next save.
 */
export default function Toaster() {
    const { flash } = usePage();
    const [toasts, setToasts] = useState([]);
    const nextId = useRef(0);
    const timers = useRef(new Map());

    const dismiss = useCallback((id) => {
        clearTimeout(timers.current.get(id));
        timers.current.delete(id);
        setToasts((current) => current.filter((toast) => toast.id !== id));
    }, []);

    const add = useCallback(
        (toast) => {
            const id = ++nextId.current;
            const type = toast.type ?? 'success';

            setToasts((current) => [...current.slice(-2), { ...toast, id, type }]);
            timers.current.set(
                id,
                setTimeout(() => dismiss(id), type === 'notification' ? NOTIFICATION_DURATION : DURATION),
            );
        },
        [dismiss],
    );

    useEffect(() => {
        if (flash?.toast?.message) {
            add(flash.toast);
        }
    }, [flash, add]);

    useEffect(() => {
        const listener = (event) => add(event.detail);
        window.addEventListener('app:toast', listener);

        return () => window.removeEventListener('app:toast', listener);
    }, [add]);

    useEffect(() => {
        const pending = timers.current;

        return () => pending.forEach((timer) => clearTimeout(timer));
    }, []);

    return (
        <div
            aria-live="polite"
            className="pointer-events-none fixed inset-x-0 top-0 z-[60] flex flex-col items-center gap-2 px-4 pt-4 sm:items-end sm:px-6 print:hidden"
        >
            {toasts.map((toast) => {
                const tone = tones[toast.type] ?? tones.info;
                const Icon = tone.icon;

                return (
                    <div
                        key={toast.id}
                        role={toast.type === 'error' ? 'alert' : 'status'}
                        className="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl bg-white p-4 shadow-lg ring-1 ring-slate-900/10"
                    >
                        <Icon className={cn('mt-0.5 size-5 shrink-0', tone.classes)} aria-hidden="true" />
                        <div className="min-w-0 flex-1">
                            {toast.title && <p className="text-sm font-semibold text-slate-900">{toast.title}</p>}
                            <p className={cn('text-sm', toast.title ? 'text-slate-600' : 'font-medium text-slate-800')}>{toast.message}</p>
                            {toast.href && (
                                <Link
                                    href={toast.href}
                                    onClick={() => dismiss(toast.id)}
                                    className="mt-1.5 inline-flex text-sm font-semibold text-primary-700 hover:text-primary-800"
                                >
                                    View
                                </Link>
                            )}
                        </div>
                        <button
                            type="button"
                            onClick={() => dismiss(toast.id)}
                            className="-m-1.5 flex size-8 shrink-0 items-center justify-center rounded-md text-slate-400 hover:text-slate-600"
                        >
                            <span className="sr-only">Dismiss</span>
                            <X className="size-4" aria-hidden="true" />
                        </button>
                    </div>
                );
            })}
        </div>
    );
}
