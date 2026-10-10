import { Search, X } from 'lucide-react';
import Input from '@/components/ui/input';
import { cn } from '@/lib/utils';

/**
 * Search field with a clear button. The parent controls the value and
 * decides when to search (usually debounced through useFilters).
 */
export default function SearchInput({ value, onChange, placeholder = 'Search…', label = 'Search', className, ...props }) {
    return (
        <div className={cn('relative', className)}>
            <Search
                className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                aria-hidden="true"
            />
            <Input
                type="search"
                inputMode="search"
                enterKeyHint="search"
                aria-label={label}
                placeholder={placeholder}
                value={value}
                onChange={(event) => onChange(event.target.value)}
                className="pr-11 pl-9 [&::-webkit-search-cancel-button]:hidden"
                {...props}
            />
            {value && (
                <button
                    type="button"
                    onClick={() => onChange('')}
                    className="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-lg text-slate-400 hover:text-slate-600 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-primary-600"
                >
                    <span className="sr-only">Clear search</span>
                    <X className="size-4" aria-hidden="true" />
                </button>
            )}
        </div>
    );
}
