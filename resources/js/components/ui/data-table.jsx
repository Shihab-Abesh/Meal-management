import { Link } from '@inertiajs/react';
import { ArrowDown, ArrowUp, ArrowUpDown, ChevronRight } from 'lucide-react';
import EmptyState from '@/components/ui/empty-state';
import { cn } from '@/lib/utils';

function cellValue(column, row) {
    const value = column.render ? column.render(row) : row[column.key];

    return value === null || value === undefined || value === '' ? '—' : value;
}

const alignClasses = { right: 'text-right', center: 'text-center' };

/**
 * Responsive table: a real <table> from the `md` breakpoint up, and a
 * stacked card per row on phones so nothing scrolls sideways.
 *
 * columns: [{ key, header, render?(row), className?, align?, primary?, sortable?, hideOnMobile? }]
 *   The `primary` column becomes the card title on mobile.
 * sort / onSort: current { key, direction } and a handler for sortable headers.
 * rowHref(row): makes mobile cards tappable and adds a link affordance.
 * actions(row): trailing per-row controls (e.g. an ActionMenu).
 * mobileCard(row): replaces the default card body on phones.
 * loading: dims the rows while new data is fetched.
 */
export default function DataTable({
    columns,
    rows,
    rowKey = 'id',
    empty,
    caption,
    sort,
    onSort,
    rowHref,
    actions,
    mobileCard,
    loading = false,
}) {
    if (!rows?.length) {
        return empty ?? <EmptyState title="Nothing to show yet" />;
    }

    const primary = columns.find((column) => column.primary) ?? columns[0];
    const secondary = columns.filter((column) => column !== primary && !column.hideOnMobile);

    return (
        <div aria-busy={loading || undefined} className={cn('transition-opacity', loading && 'pointer-events-none opacity-60')}>
            {/* Columns should be sized to fit; the scroll container only keeps an
                unexpectedly wide value from widening the whole page. */}
            <div className="hidden overflow-x-auto md:block">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    {caption && <caption className="sr-only">{caption}</caption>}
                    <thead>
                        <tr>
                            {columns.map((column) => (
                                <th
                                    key={column.key}
                                    scope="col"
                                    aria-sort={
                                        sort?.key === column.key
                                            ? sort.direction === 'asc'
                                                ? 'ascending'
                                                : 'descending'
                                            : undefined
                                    }
                                    className={cn(
                                        'px-4 py-3 text-left text-xs font-semibold tracking-wide whitespace-nowrap text-slate-500 uppercase first:pl-6 last:pr-6',
                                        alignClasses[column.align],
                                        column.className,
                                    )}
                                >
                                    {column.sortable && onSort ? (
                                        <SortButton column={column} sort={sort} onSort={onSort} />
                                    ) : (
                                        column.header
                                    )}
                                </th>
                            ))}
                            {actions && (
                                <th scope="col" className="w-16 pr-6">
                                    <span className="sr-only">Actions</span>
                                </th>
                            )}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {rows.map((row) => (
                            <tr key={row[rowKey]} className="hover:bg-slate-50">
                                {columns.map((column) => (
                                    <td
                                        key={column.key}
                                        className={cn(
                                            'px-4 py-3 text-slate-700 first:pl-6 last:pr-6',
                                            alignClasses[column.align],
                                            column.className,
                                        )}
                                    >
                                        {column === primary && rowHref ? (
                                            <Link
                                                href={rowHref(row)}
                                                className="font-medium text-slate-900 hover:text-primary-700"
                                            >
                                                {cellValue(column, row)}
                                            </Link>
                                        ) : (
                                            cellValue(column, row)
                                        )}
                                    </td>
                                ))}
                                {actions && <td className="py-1 pr-4 text-right">{actions(row)}</td>}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <ul className="divide-y divide-slate-100 md:hidden">
                {rows.map((row) => (
                    <li key={row[rowKey]} className="flex items-start gap-2 py-1 pr-2 pl-4">
                        <MobileCardBody href={rowHref?.(row)}>
                            {mobileCard ? (
                                mobileCard(row)
                            ) : (
                                <>
                                    <div className="font-medium text-slate-900">{cellValue(primary, row)}</div>
                                    {secondary.length > 0 && (
                                        <dl className="mt-2 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                            {secondary.map((column) => (
                                                <div key={column.key} className="min-w-0">
                                                    <dt className="text-xs text-slate-500">{column.header}</dt>
                                                    <dd className="truncate text-slate-700">{cellValue(column, row)}</dd>
                                                </div>
                                            ))}
                                        </dl>
                                    )}
                                </>
                            )}
                        </MobileCardBody>
                        {actions && <div className="shrink-0 pt-2">{actions(row)}</div>}
                    </li>
                ))}
            </ul>
        </div>
    );
}

function MobileCardBody({ href, children }) {
    if (!href) {
        return <div className="min-w-0 flex-1 py-3">{children}</div>;
    }

    return (
        <Link href={href} className="flex min-w-0 flex-1 items-center gap-2 py-3 active:bg-slate-50">
            <div className="min-w-0 flex-1">{children}</div>
            <ChevronRight className="size-5 shrink-0 text-slate-300" aria-hidden="true" />
        </Link>
    );
}

function SortButton({ column, sort, onSort }) {
    const active = sort?.key === column.key;
    const Icon = active ? (sort.direction === 'asc' ? ArrowUp : ArrowDown) : ArrowUpDown;

    return (
        <button
            type="button"
            onClick={() => onSort(column.key)}
            className={cn(
                'inline-flex items-center gap-1 rounded uppercase hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-primary-600',
                active && 'text-slate-900',
            )}
        >
            {column.header}
            <Icon className={cn('size-3.5', !active && 'text-slate-300')} aria-hidden="true" />
        </button>
    );
}
