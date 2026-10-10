import { useApp } from '@/hooks/use-app';
import { formatDate, formatNumber } from '@/lib/format';
import { cn } from '@/lib/utils';

export function useReportFormat() {
    const { money } = useApp();

    return (value, format) => {
        if (value === null || value === undefined || value === '') {
            return '—';
        }

        switch (format) {
            case 'currency':
                return money(value);
            case 'number':
                return formatNumber(value);
            case 'date':
                return formatDate(value);
            default:
                return value;
        }
    };
}

/**
 * A report's rows: a table with a totals row on wide screens, and one card
 * per row plus a totals card on phones. Wide reports switch to the table
 * later so nothing scrolls sideways.
 */
export default function ReportTable({ columns, rows, totals, caption }) {
    const format = useReportFormat();
    const tableFrom = columns.length > 7 ? 'xl' : columns.length > 5 ? 'lg' : 'md';
    const [primary, ...rest] = columns;

    return (
        <>
            <div className={cn('hidden', { md: 'md:block', lg: 'lg:block', xl: 'xl:block' }[tableFrom])}>
                <table className="min-w-full text-sm">
                    <caption className="sr-only">{caption}</caption>
                    <thead>
                        <tr>
                            {columns.map((column) => (
                                <th
                                    key={column.key}
                                    scope="col"
                                    className={cn(
                                        'px-3 py-3 text-xs font-semibold tracking-wide whitespace-nowrap text-slate-500 uppercase first:pl-6 last:pr-6',
                                        column.align === 'right' ? 'text-right' : 'text-left',
                                    )}
                                >
                                    {column.label}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {rows.map((row, index) => (
                            <tr key={index} className="hover:bg-slate-50">
                                {columns.map((column) => (
                                    <td
                                        key={column.key}
                                        className={cn(
                                            'px-3 py-2.5 text-slate-700 first:pl-6 last:pr-6',
                                            column.align === 'right' && 'text-right tabular-nums',
                                            column === primary && 'font-medium text-slate-900',
                                        )}
                                    >
                                        {format(row[column.key], column.format)}
                                    </td>
                                ))}
                            </tr>
                        ))}
                    </tbody>
                    {totals && (
                        <tfoot>
                            <tr className="border-t-2 border-slate-200 font-semibold text-slate-900">
                                {columns.map((column) => (
                                    <td
                                        key={column.key}
                                        className={cn('px-3 py-3 first:pl-6 last:pr-6', column.align === 'right' && 'text-right tabular-nums')}
                                    >
                                        {column.key in totals ? format(totals[column.key], column.format) : ''}
                                    </td>
                                ))}
                            </tr>
                        </tfoot>
                    )}
                </table>
            </div>

            <ul className={cn('divide-y divide-slate-100', { md: 'md:hidden', lg: 'lg:hidden', xl: 'xl:hidden' }[tableFrom])}>
                {rows.map((row, index) => (
                    <li key={index} className="px-4 py-3">
                        <p className="font-medium text-slate-900">{format(row[primary.key], primary.format)}</p>
                        <dl className="mt-1.5 grid grid-cols-3 gap-x-3 gap-y-1.5 text-sm">
                            {rest.map((column) => (
                                <div key={column.key} className="min-w-0">
                                    <dt className="truncate text-xs text-slate-500">{column.label}</dt>
                                    <dd className="truncate text-slate-800 tabular-nums">{format(row[column.key], column.format)}</dd>
                                </div>
                            ))}
                        </dl>
                    </li>
                ))}
                {totals && (
                    <li className="bg-slate-50 px-4 py-3">
                        <p className="font-semibold text-slate-900">Total</p>
                        <dl className="mt-1.5 grid grid-cols-3 gap-x-3 gap-y-1.5 text-sm">
                            {rest
                                .filter((column) => column.key in totals)
                                .map((column) => (
                                    <div key={column.key} className="min-w-0">
                                        <dt className="truncate text-xs text-slate-500">{column.label}</dt>
                                        <dd className="truncate font-semibold text-slate-900 tabular-nums">{format(totals[column.key], column.format)}</dd>
                                    </div>
                                ))}
                        </dl>
                    </li>
                )}
            </ul>
        </>
    );
}
