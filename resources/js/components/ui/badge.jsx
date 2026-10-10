import { cn } from '@/lib/utils';

const tones = {
    gray: 'bg-slate-100 text-slate-700 ring-slate-500/20',
    green: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    red: 'bg-red-50 text-red-700 ring-red-600/20',
    amber: 'bg-amber-50 text-amber-800 ring-amber-600/20',
    blue: 'bg-sky-50 text-sky-700 ring-sky-600/20',
    violet: 'bg-violet-50 text-violet-700 ring-violet-600/20',
};

export default function Badge({ tone = 'gray', dot = false, className, children }) {
    return (
        <span
            className={cn(
                'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap ring-1 ring-inset',
                tones[tone] ?? tones.gray,
                className,
            )}
        >
            {dot && <span className="size-1.5 rounded-full bg-current" aria-hidden="true" />}
            {children}
        </span>
    );
}
