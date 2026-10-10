/**
 * Save / cancel bar for long forms. Sticky so the actions stay within thumb
 * reach on phones; it sits above the mobile tab bar.
 */
export default function FormActions({ children }) {
    return (
        <div className="sticky bottom-[calc(3.75rem+env(safe-area-inset-bottom))] z-20 -mx-4 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur sm:mx-0 sm:rounded-xl sm:border sm:shadow-sm lg:bottom-4">
            <div className="flex gap-2 sm:justify-end [&>*]:flex-1 sm:[&>*]:flex-none">{children}</div>
        </div>
    );
}
