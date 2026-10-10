import { Check } from 'lucide-react';
import { cn } from '@/lib/utils';

/**
 * Large tappable choices. With `multiple`, `value` is an array; otherwise a
 * single value. options: [{ value, label }]
 */
export default function ChoiceChips({ options, value, onChange, multiple = false, invalid = false, id, className, ...props }) {
    const selected = (option) => (multiple ? value.includes(option.value) : value === option.value);

    function toggle(option) {
        if (!multiple) {
            onChange(option.value);

            return;
        }

        onChange(selected(option) ? value.filter((item) => item !== option.value) : [...value, option.value]);
    }

    return (
        <div
            id={id}
            role="group"
            aria-invalid={invalid || undefined}
            aria-describedby={props['aria-describedby']}
            className={cn('flex flex-wrap gap-2', className)}
        >
            {options.map((option) => {
                const isSelected = selected(option);

                return (
                    <button
                        key={option.value}
                        type="button"
                        role={multiple ? 'checkbox' : 'radio'}
                        aria-checked={isSelected}
                        onClick={() => toggle(option)}
                        className={cn(
                            'inline-flex min-h-11 items-center gap-2 rounded-lg px-4 text-sm font-medium ring-1 transition-colors ring-inset focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600',
                            isSelected
                                ? 'bg-primary-50 text-primary-800 ring-primary-600'
                                : 'bg-white text-slate-700 ring-slate-300 hover:bg-slate-50',
                            invalid && !isSelected && 'ring-red-400',
                        )}
                    >
                        <span
                            className={cn(
                                'flex size-5 items-center justify-center rounded-md ring-1 ring-inset',
                                isSelected ? 'bg-primary-600 text-white ring-primary-600' : 'ring-slate-300',
                            )}
                            aria-hidden="true"
                        >
                            {isSelected && <Check className="size-3.5" strokeWidth={3} />}
                        </span>
                        {option.label}
                    </button>
                );
            })}
        </div>
    );
}
