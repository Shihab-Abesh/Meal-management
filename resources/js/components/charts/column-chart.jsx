import { useId, useState } from 'react';
import { formatNumber } from '@/lib/format';
import { cn } from '@/lib/utils';

/**
 * Round the axis maximum up to a clean number (0 / 50 / 100 …).
 */
function niceMax(value) {
    if (value <= 0) {
        return 4;
    }

    const magnitude = 10 ** Math.floor(Math.log10(value));
    const step = [1, 2, 2.5, 5, 10].find((candidate) => candidate * magnitude >= value / 4) * magnitude;

    return Math.ceil(value / step) * step;
}

/**
 * Column chart, optionally stacked, drawn with plain elements so it resizes
 * with its container. Columns are at most 24px wide with a 4px rounded cap;
 * stacked segments are separated by a 2px gap. Hover or arrow keys show a
 * tooltip with every series; the same values are in the table below the
 * chart, so the tooltip never gates them.
 *
 * data: [{ label, title?, ...values }]
 * series: [{ key, label, color }]   colors are CSS colors, in fixed order
 * labelIndexes: columns whose total is written on the cap (keep it sparing)
 */
export default function ColumnChart({ data, series, label, height = 192, formatValue = formatNumber, labelIndexes = [], className }) {
    const id = useId();
    const [active, setActive] = useState(null);
    const totals = data.map((item) => series.reduce((sum, entry) => sum + Number(item[entry.key] ?? 0), 0));
    const max = niceMax(Math.max(0, ...totals));
    const ticks = [max, max / 2, 0];
    const tickStep = Math.max(1, Math.ceil(data.length / 8));
    const last = data.length - 1;
    // Every nth label plus the last one, dropping a regular label that would crowd the last.
    const showTick = (index) => index === last || (index % tickStep === 0 && last - index > tickStep / 2);
    const activeItem = active === null ? null : data[active];
    const position = active === null ? 0 : ((active + 0.5) / data.length) * 100;

    function onKeyDown(event) {
        if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
            event.preventDefault();
            const delta = event.key === 'ArrowRight' ? 1 : -1;
            setActive((current) => Math.min(data.length - 1, Math.max(0, (current ?? data.length - 1) + (current === null ? 0 : delta))));
        } else if (event.key === 'Escape') {
            setActive(null);
        }
    }

    return (
        <figure className={cn('min-w-0', className)}>
            {series.length > 1 && (
                <figcaption className="mb-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-600">
                    {series.map((entry) => (
                        <span key={entry.key} className="inline-flex items-center gap-1.5">
                            <span className="size-2.5 rounded-sm" style={{ backgroundColor: entry.color }} aria-hidden="true" />
                            {entry.label}
                        </span>
                    ))}
                </figcaption>
            )}

            <div className="flex">
                {/* Y axis: three clean ticks. */}
                <div className="relative w-10 shrink-0" style={{ height }} aria-hidden="true">
                    {ticks.map((tick, index) => (
                        <span
                            key={tick}
                            className="absolute right-2 -translate-y-1/2 text-[11px] text-slate-400 tabular-nums"
                            style={{ top: `${(index / (ticks.length - 1)) * 100}%` }}
                        >
                            {formatNumber(tick)}
                        </span>
                    ))}
                </div>

                <div className="relative min-w-0 flex-1">
                    <div className="pointer-events-none absolute inset-x-0 top-0" style={{ height }} aria-hidden="true">
                        {ticks.map((tick, index) => (
                            <div
                                key={tick}
                                className={cn('absolute inset-x-0 h-px', tick === 0 ? 'bg-slate-300' : 'bg-slate-100')}
                                style={{ top: `${(index / (ticks.length - 1)) * 100}%` }}
                            />
                        ))}
                    </div>

                    <div
                        role="group"
                        tabIndex={0}
                        aria-label={`${label}. Use the arrow keys to read each value.`}
                        aria-describedby={activeItem ? `${id}-tip` : undefined}
                        onKeyDown={onKeyDown}
                        onBlur={() => setActive(null)}
                        onPointerLeave={() => setActive(null)}
                        className="relative flex items-end rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary-600"
                        style={{ height }}
                    >
                        {data.map((item, index) => {
                            const total = totals[index];
                            const visible = series.filter((entry) => Number(item[entry.key] ?? 0) > 0);

                            return (
                                <div
                                    key={item.label + index}
                                    onPointerEnter={() => setActive(index)}
                                    className={cn('relative flex h-full min-w-0 flex-1 items-end justify-center px-px', active === index && 'bg-slate-100/70')}
                                >
                                    {labelIndexes.includes(index) && total > 0 && (
                                        <span
                                            className="absolute left-1/2 -translate-x-1/2 -translate-y-full pb-1 text-[11px] font-semibold whitespace-nowrap text-slate-700"
                                            style={{ bottom: `${(total / max) * 100}%` }}
                                        >
                                            {formatValue(total)}
                                        </span>
                                    )}
                                    {total > 0 && (
                                        <div
                                            className="flex w-full max-w-6 flex-col-reverse gap-[2px]"
                                            style={{ height: `max(2px, ${(total / max) * 100}%)` }}
                                        >
                                            {visible.map((entry, position) => (
                                                <div
                                                    key={entry.key}
                                                    className={cn('min-h-px', position === visible.length - 1 && 'rounded-t-[4px]')}
                                                    style={{ flex: `${Number(item[entry.key])} 1 0`, backgroundColor: entry.color }}
                                                />
                                            ))}
                                        </div>
                                    )}
                                </div>
                            );
                        })}

                        {activeItem && (
                            <div
                                id={`${id}-tip`}
                                role="status"
                                className="pointer-events-none absolute bottom-full z-10 mb-2 min-w-32 rounded-lg bg-white px-3 py-2 text-xs shadow-lg ring-1 ring-slate-900/10"
                                style={{
                                    left: `${position}%`,
                                    transform: `translateX(${position < 20 ? '-10%' : position > 80 ? '-90%' : '-50%'})`,
                                }}
                            >
                                <p className="font-medium text-slate-500">{activeItem.title ?? activeItem.label}</p>
                                {series.map((entry) => (
                                    <p key={entry.key} className="mt-1 flex items-center gap-2">
                                        <span className="h-0.5 w-3 rounded-full" style={{ backgroundColor: entry.color }} aria-hidden="true" />
                                        <span className="font-semibold text-slate-900 tabular-nums">{formatValue(Number(activeItem[entry.key] ?? 0))}</span>
                                        <span className="text-slate-500">{entry.label}</span>
                                    </p>
                                ))}
                                {series.length > 1 && (
                                    <p className="mt-1 border-t border-slate-100 pt-1 text-slate-500">
                                        Total <span className="font-semibold text-slate-900 tabular-nums">{formatValue(totals[active])}</span>
                                    </p>
                                )}
                            </div>
                        )}
                    </div>

                    <div className="mt-1.5 flex" aria-hidden="true">
                        {data.map((item, index) => (
                            <span key={item.label + index} className="min-w-0 flex-1 text-center text-[11px] text-slate-400">
                                {showTick(index) ? item.label : ''}
                            </span>
                        ))}
                    </div>
                </div>
            </div>
        </figure>
    );
}
