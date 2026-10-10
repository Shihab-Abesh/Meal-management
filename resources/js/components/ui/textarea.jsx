import { fieldClasses, invalidClasses } from '@/components/ui/input';
import { cn } from '@/lib/utils';

export default function Textarea({ invalid = false, rows = 3, className, ref, ...props }) {
    return (
        <textarea
            ref={ref}
            rows={rows}
            aria-invalid={invalid || undefined}
            className={cn(fieldClasses, 'px-3 py-2.5', invalid && invalidClasses, className)}
            {...props}
        />
    );
}
