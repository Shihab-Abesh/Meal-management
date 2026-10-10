import { CircleAlert, CircleCheck, Info } from 'lucide-react';
import { cn } from '@/lib/utils';

const tones = {
    success: { classes: 'bg-emerald-50 text-emerald-800 ring-emerald-600/20', icon: CircleCheck },
    error: { classes: 'bg-red-50 text-red-800 ring-red-600/20', icon: CircleAlert },
    info: { classes: 'bg-sky-50 text-sky-800 ring-sky-600/20', icon: Info },
};

export default function Alert({ tone = 'info', className, children }) {
    const { classes, icon: Icon } = tones[tone] ?? tones.info;

    return (
        <div
            role={tone === 'error' ? 'alert' : 'status'}
            className={cn('flex gap-3 rounded-lg px-4 py-3 text-sm ring-1 ring-inset', classes, className)}
        >
            <Icon className="mt-0.5 size-4 shrink-0" aria-hidden="true" />
            <div>{children}</div>
        </div>
    );
}
