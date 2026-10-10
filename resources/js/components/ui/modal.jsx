import { Dialog, DialogBackdrop, DialogPanel, DialogTitle, Description } from '@headlessui/react';
import { X } from 'lucide-react';
import { cn } from '@/lib/utils';

const widths = {
    sm: 'sm:max-w-md',
    md: 'sm:max-w-lg',
    lg: 'sm:max-w-2xl',
};

/**
 * Dialog that opens as a bottom sheet on phones and a centred panel from
 * the `sm` breakpoint. `footer` stays visible while the body scrolls.
 */
export default function Modal({ open, onClose, title, description, size = 'md', footer, children, className }) {
    return (
        <Dialog open={open} onClose={onClose} className="relative z-50">
            <DialogBackdrop
                transition
                className="fixed inset-0 bg-slate-900/50 transition-opacity duration-200 ease-out data-closed:opacity-0"
            />
            <div className="fixed inset-0 flex items-end justify-center sm:items-center sm:p-6">
                <DialogPanel
                    transition
                    className={cn(
                        'flex max-h-[90dvh] w-full flex-col overflow-hidden rounded-t-2xl bg-white shadow-xl transition duration-200 ease-out data-closed:translate-y-8 data-closed:opacity-0 sm:rounded-2xl sm:data-closed:translate-y-0 sm:data-closed:scale-95',
                        widths[size],
                        className,
                    )}
                >
                    <div className="flex items-start justify-between gap-3 border-b border-slate-100 px-4 py-3 sm:px-6 sm:py-4">
                        <div className="min-w-0 pt-1.5">
                            <DialogTitle className="text-base font-semibold text-slate-900">{title}</DialogTitle>
                            {description && <Description className="mt-1 text-sm text-slate-500">{description}</Description>}
                        </div>
                        <button
                            type="button"
                            onClick={onClose}
                            className="-mr-2 flex size-11 shrink-0 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-2 focus-visible:outline-primary-600"
                        >
                            <span className="sr-only">Close</span>
                            <X className="size-5" aria-hidden="true" />
                        </button>
                    </div>
                    <div className="min-h-0 flex-1 overflow-y-auto px-4 py-5 sm:px-6">{children}</div>
                    {footer && (
                        <div className="flex flex-col-reverse gap-2 border-t border-slate-100 px-4 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:flex-row sm:justify-end sm:px-6">
                            {footer}
                        </div>
                    )}
                </DialogPanel>
            </div>
        </Dialog>
    );
}
