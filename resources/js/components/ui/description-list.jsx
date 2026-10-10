import { cn } from '@/lib/utils';

/**
 * Read-only label/value pairs for detail pages.
 * items: [{ label, value, wide? }]; empty values render as an em dash.
 */
export default function DescriptionList({ items, className }) {
    return (
        <dl className={cn('grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2', className)}>
            {items.map((item) => (
                <div key={item.label} className={cn('min-w-0', item.wide && 'sm:col-span-2')}>
                    <dt className="text-xs font-medium tracking-wide text-slate-500 uppercase">{item.label}</dt>
                    <dd className="mt-1 text-sm break-words text-slate-900">{item.value || '—'}</dd>
                </div>
            ))}
        </dl>
    );
}
