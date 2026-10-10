import { formatNumber } from '@/lib/format';

/**
 * Ranked horizontal bars (e.g. revenue by client): label and value above a
 * thin bar with a 4px rounded end. Every bar carries its value and share,
 * so nothing depends on hover. One series, so no legend.
 *
 * items: [{ label, value }] sorted largest first
 */
export default function BarList({ items, formatValue = formatNumber, label, color = 'var(--color-series-1)' }) {
    const max = Math.max(0, ...items.map((item) => Number(item.value)));
    const total = items.reduce((sum, item) => sum + Number(item.value), 0);

    return (
        <ul className="space-y-3" aria-label={label}>
            {items.map((item) => {
                const value = Number(item.value);
                const share = total > 0 ? Math.round((value / total) * 100) : 0;

                return (
                    <li key={item.label}>
                        <div className="flex items-baseline justify-between gap-3 text-sm">
                            <span className="min-w-0 truncate text-slate-700">{item.label}</span>
                            <span className="shrink-0 tabular-nums">
                                <span className="font-semibold text-slate-900">{formatValue(value)}</span>
                                <span className="ml-1.5 text-xs text-slate-500">{share}%</span>
                            </span>
                        </div>
                        <div className="mt-1 h-2.5 w-full" aria-hidden="true">
                            {value > 0 && (
                                <div className="h-full rounded-r-[4px]" style={{ width: `max(2px, ${(value / max) * 100}%)`, backgroundColor: color }} />
                            )}
                        </div>
                    </li>
                );
            })}
        </ul>
    );
}
