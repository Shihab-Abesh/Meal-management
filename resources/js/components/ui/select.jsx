import { ChevronDown } from 'lucide-react';
import { fieldClasses, invalidClasses } from '@/components/ui/input';
import { cn } from '@/lib/utils';

/**
 * Native select (best experience on mobile). Pass `options` as
 * [{ value, label }] or render <option> children yourself.
 */
export default function Select({ options, placeholder, invalid = false, className, children, ref, ...props }) {
    return (
        <div className="relative">
            <select
                ref={ref}
                aria-invalid={invalid || undefined}
                className={cn(fieldClasses, 'h-11 appearance-none pr-10 pl-3', invalid && invalidClasses, className)}
                {...props}
            >
                {placeholder && <option value="">{placeholder}</option>}
                {options
                    ? options.map((option) => (
                          <option key={option.value} value={option.value}>
                              {option.label}
                          </option>
                      ))
                    : children}
            </select>
            <ChevronDown
                className="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-slate-400"
                aria-hidden="true"
            />
        </div>
    );
}
