import { Bell, CircleCheckBig, ClipboardPlus, FilePenLine, ReceiptText, XCircle } from 'lucide-react';
import { cn } from '@/lib/utils';

const kinds = {
    order_placed: { icon: ClipboardPlus, classes: 'bg-emerald-50 text-emerald-600' },
    order_changed: { icon: FilePenLine, classes: 'bg-amber-50 text-amber-600' },
    order_cancelled: { icon: XCircle, classes: 'bg-red-50 text-red-600' },
    delivery_completed: { icon: CircleCheckBig, classes: 'bg-sky-50 text-sky-600' },
    bills_overdue: { icon: ReceiptText, classes: 'bg-red-50 text-red-600' },
};

/**
 * The round icon in front of a notification; shape and colour say what
 * kind of news it is, the title says the rest.
 */
export default function NotificationIcon({ kind, className }) {
    const { icon: Icon, classes } = kinds[kind] ?? { icon: Bell, classes: 'bg-slate-100 text-slate-500' };

    return (
        <span className={cn('flex size-9 shrink-0 items-center justify-center rounded-full', classes, className)}>
            <Icon className="size-4.5" aria-hidden="true" />
        </span>
    );
}
