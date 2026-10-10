import { NotebookPen, Plus } from 'lucide-react';
import { categoryDot } from '@/components/menu-chart/categories';
import { useApp } from '@/hooks/use-app';
import { cn } from '@/lib/utils';

const MAX_ITEMS = 5;

/**
 * One meal slot of the chart. A planned menu lists its dishes (with a
 * category dot), price and a note marker; an empty slot is a dashed "Add"
 * target. The whole cell opens the editor.
 */
export default function MenuCell({ menu, mealLabel, past = false, onOpen, compact = false, className }) {
    const { money } = useApp();

    if (!menu) {
        return (
            <button
                type="button"
                onClick={onOpen}
                aria-label={`Add ${mealLabel.toLowerCase()} menu`}
                className={cn(
                    'group flex w-full items-center justify-center gap-1.5 rounded-lg border border-dashed text-sm font-medium transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600',
                    compact ? 'min-h-11 px-3' : 'h-full min-h-28 px-2',
                    past
                        ? 'border-slate-200 text-slate-300 hover:border-slate-300 hover:text-slate-500'
                        : 'border-slate-300 text-slate-400 hover:border-primary-400 hover:bg-primary-50/60 hover:text-primary-700',
                    className,
                )}
            >
                <Plus className="size-4" aria-hidden="true" />
                {past ? 'No menu' : `Add ${compact ? mealLabel.toLowerCase() : ''}`.trim()}
            </button>
        );
    }

    const shown = menu.items.slice(0, MAX_ITEMS);
    const hidden = menu.items.length - shown.length;

    return (
        <button
            type="button"
            onClick={onOpen}
            aria-label={`${mealLabel}: ${menu.items.map((item) => item.name).join(', ')}. Edit`}
            className={cn(
                'group flex w-full flex-col rounded-lg bg-white p-2.5 text-left shadow-xs ring-1 ring-slate-200 transition hover:shadow-md hover:ring-primary-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600',
                !compact && 'h-full min-h-28',
                past && 'bg-slate-50/80',
                className,
            )}
        >
            <ul className={cn('min-w-0 flex-1 space-y-1', compact && 'grid grid-cols-2 gap-x-3 space-y-0 gap-y-1')}>
                {shown.map((item) => (
                    <li key={item.id} className="flex min-w-0 items-center gap-1.5 text-[13px] leading-5 text-slate-800">
                        <span className={cn('size-1.5 shrink-0 rounded-full', categoryDot(item.category))} aria-hidden="true" />
                        <span className="truncate">{item.name}</span>
                    </li>
                ))}
                {hidden > 0 && <li className="pl-3 text-xs text-slate-500">+{hidden} more</li>}
            </ul>
            {(menu.price || menu.notes) && (
                <span className="mt-2 flex items-center gap-2 border-t border-slate-100 pt-2">
                    {menu.price && <span className="rounded-md bg-slate-100 px-1.5 py-0.5 text-xs font-semibold text-slate-700 tabular-nums">{money(menu.price)}</span>}
                    {menu.notes && (
                        <span className="flex min-w-0 items-center gap-1 text-xs text-slate-500" title={menu.notes}>
                            <NotebookPen className="size-3.5 shrink-0" aria-hidden="true" />
                            <span className="truncate">{menu.notes}</span>
                        </span>
                    )}
                </span>
            )}
        </button>
    );
}
