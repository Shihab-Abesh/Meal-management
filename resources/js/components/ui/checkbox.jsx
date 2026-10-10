import { cn } from '@/lib/utils';

export default function Checkbox({ label, description, id, className, ...props }) {
    return (
        <label htmlFor={id} className={cn('flex min-h-11 cursor-pointer items-start gap-3 py-1', className)}>
            <input
                id={id}
                type="checkbox"
                className="mt-0.5 size-5 shrink-0 cursor-pointer accent-primary-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600"
                {...props}
            />
            <span className="text-sm">
                <span className="font-medium text-slate-700">{label}</span>
                {description && <span className="block text-slate-500">{description}</span>}
            </span>
        </label>
    );
}
