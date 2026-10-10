import { cn } from '@/lib/utils';

// text-base on mobile stops iOS Safari zooming into focused fields.
export const fieldClasses =
    'block w-full rounded-lg border-0 bg-white text-base text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 focus:outline-none disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500 sm:text-sm';

export const invalidClasses = 'ring-red-400 focus:ring-red-500';

export default function Input({ invalid = false, className, ref, ...props }) {
    return (
        <input
            ref={ref}
            aria-invalid={invalid || undefined}
            className={cn(fieldClasses, 'h-11 px-3', invalid && invalidClasses, className)}
            {...props}
        />
    );
}
