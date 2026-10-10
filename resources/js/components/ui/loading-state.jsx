import Spinner from '@/components/ui/spinner';
import { cn } from '@/lib/utils';

/**
 * Placeholder rows while data loads.
 */
export function Skeleton({ rows = 4, className }) {
    return (
        <div className={cn('animate-pulse space-y-3 p-4 sm:p-6', className)} aria-hidden="true">
            {Array.from({ length: rows }, (_, index) => (
                <div key={index} className="h-10 rounded-lg bg-slate-100" />
            ))}
        </div>
    );
}

/**
 * Centred spinner with a message, for sections that are loading.
 */
export default function LoadingState({ label = 'Loading…', className }) {
    return (
        <div className={cn('flex justify-center px-4 py-10 text-primary-600', className)}>
            <Spinner label={label} />
        </div>
    );
}
