import { Dialog, DialogBackdrop, DialogPanel, DialogTitle } from '@headlessui/react';
import { X } from 'lucide-react';
import { cn } from '@/lib/utils';

const panels = {
    left: 'inset-y-0 left-0 h-full w-full max-w-xs data-closed:-translate-x-full',
    right: 'inset-y-0 right-0 h-full w-full max-w-sm data-closed:translate-x-full',
    bottom: 'inset-x-0 bottom-0 max-h-[85dvh] w-full rounded-t-2xl pb-safe data-closed:translate-y-full',
};

/**
 * Slide-in panel. `side="left"` is used for mobile navigation and
 * `side="bottom"` gives a mobile-friendly bottom sheet.
 */
export default function Drawer({ open, onClose, side = 'left', title, hideTitle = false, className, children }) {
    return (
        <Dialog open={open} onClose={onClose} className="relative z-50">
            <DialogBackdrop
                transition
                className="fixed inset-0 bg-slate-900/50 transition-opacity duration-300 ease-out data-closed:opacity-0"
            />
            <DialogPanel
                transition
                className={cn(
                    'fixed flex flex-col overflow-hidden bg-white shadow-xl transition duration-300 ease-out',
                    panels[side],
                    className,
                )}
            >
                <div className={cn('flex items-center justify-between gap-3 px-4 pt-3', hideTitle && 'absolute top-0 right-0 z-10')}>
                    <DialogTitle className={cn('text-base font-semibold text-slate-900', hideTitle && 'sr-only')}>
                        {title}
                    </DialogTitle>
                    <button
                        type="button"
                        onClick={onClose}
                        className="-mr-2 flex size-11 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-2 focus-visible:outline-primary-600"
                    >
                        <span className="sr-only">Close</span>
                        <X className="size-5" aria-hidden="true" />
                    </button>
                </div>
                <div className="min-h-0 flex-1 overflow-y-auto">{children}</div>
            </DialogPanel>
        </Dialog>
    );
}
