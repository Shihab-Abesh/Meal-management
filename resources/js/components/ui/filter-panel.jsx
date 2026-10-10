import { SlidersHorizontal } from 'lucide-react';
import { useState } from 'react';
import Button from '@/components/ui/button';
import Drawer from '@/components/ui/drawer';
import { useMediaQuery } from '@/hooks/use-media-query';

/**
 * Filter controls: shown inline from the `md` breakpoint, and behind a
 * "Filters" button that opens a bottom sheet on phones. Children are the
 * filter fields; they apply immediately when changed.
 */
export default function FilterPanel({ activeCount = 0, onReset, children }) {
    const wide = useMediaQuery('(min-width: 768px)');
    const [open, setOpen] = useState(false);

    if (wide) {
        return (
            <div className="flex flex-wrap items-end gap-3">
                {children}
                {activeCount > 0 && onReset && (
                    <Button variant="ghost" onClick={onReset}>
                        Clear filters
                    </Button>
                )}
            </div>
        );
    }

    return (
        <>
            <Button variant="secondary" icon={SlidersHorizontal} onClick={() => setOpen(true)} className="shrink-0">
                Filters
                {activeCount > 0 && (
                    <span className="ml-0.5 inline-flex size-5 items-center justify-center rounded-full bg-primary-600 text-xs text-white">
                        {activeCount}
                    </span>
                )}
            </Button>
            <Drawer open={open} onClose={() => setOpen(false)} side="bottom" title="Filters">
                <div className="space-y-4 px-4 pt-2 pb-4">{children}</div>
                <div className="flex gap-2 border-t border-slate-100 px-4 py-3">
                    {onReset && (
                        <Button
                            variant="secondary"
                            className="flex-1"
                            onClick={() => {
                                onReset();
                                setOpen(false);
                            }}
                        >
                            Clear
                        </Button>
                    )}
                    <Button className="flex-1" onClick={() => setOpen(false)}>
                        Show results
                    </Button>
                </div>
            </Drawer>
        </>
    );
}

/**
 * A labelled filter control that fits both the inline bar and the sheet.
 */
export function FilterField({ label, htmlFor, children }) {
    return (
        <div className="w-full space-y-1.5 md:w-44">
            <label htmlFor={htmlFor} className="block text-xs font-medium text-slate-500">
                {label}
            </label>
            {children}
        </div>
    );
}
