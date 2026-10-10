import { Head } from '@inertiajs/react';
import { ClipboardList, SearchX } from 'lucide-react';
import MealTypeBadges from '@/components/clients/meal-type-badges';
import Card from '@/components/ui/card';
import DataTable from '@/components/ui/data-table';
import DateNavigator from '@/components/ui/date-navigator';
import EmptyState from '@/components/ui/empty-state';
import FilterPanel, { FilterField } from '@/components/ui/filter-panel';
import PageHeader from '@/components/ui/page-header';
import Pagination from '@/components/ui/pagination';
import SearchInput from '@/components/ui/search-input';
import Select from '@/components/ui/select';
import StatusBadge from '@/components/ui/status-badge';
import { useApp } from '@/hooks/use-app';
import { useFilters } from '@/hooks/use-filters';
import { formatLongDate, formatNumber } from '@/lib/format';
import { cn } from '@/lib/utils';

const FILTER_KEYS = ['client', 'meal_type', 'status'];

export default function OrdersIndex({ orders, filters, statusCounts, clients, mealTypes, statuses, today }) {
    const { money } = useApp();
    const { values, set, reset, loading } = useFilters(route('orders.index'), filters);
    const activeFilters = FILTER_KEYS.filter((key) => values[key]).length;
    const showAmounts = orders.data.some((order) => order.amount !== null);
    const totalOrders = Object.values(statusCounts).reduce((sum, count) => sum + count, 0);

    const columns = [
        {
            key: 'order_number',
            header: 'Order',
            primary: true,
            render: (order) => (
                <span className="block min-w-0">
                    <span className="block truncate font-medium text-slate-900">{order.client?.name}</span>
                    <span className="block text-xs text-slate-500">{order.order_number}</span>
                </span>
            ),
        },
        { key: 'meal_type', header: 'Meal', render: (order) => <MealTypeBadges types={[order.meal_type]} /> },
        { key: 'quantity', header: 'Qty', align: 'right', render: (order) => formatNumber(order.quantity) },
        showAmounts && { key: 'amount', header: 'Amount', align: 'right', className: 'hidden lg:table-cell', render: (order) => money(order.amount) },
        { key: 'rider', header: 'Rider', className: 'hidden xl:table-cell', render: (order) => order.rider?.name ?? <span className="text-slate-400">Not assigned</span> },
        { key: 'status', header: 'Status', render: (order) => <StatusBadge status={order.status} label={order.status_label} /> },
    ].filter(Boolean);

    return (
        <>
            <Head title="Orders" />

            <PageHeader title="Orders" description="Created automatically when meals are confirmed." />

            <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <DateNavigator value={values.date} today={today} onChange={(date) => set('date', date)} />
                <p className="text-sm text-slate-500">
                    {formatLongDate(values.date)} · {formatNumber(totalOrders)} orders
                </p>
            </div>

            {totalOrders > 0 && (
                <div className="mb-4 flex flex-wrap gap-2" role="group" aria-label="Filter by status">
                    {[{ value: '', label: 'All', count: totalOrders }, ...statuses.filter((status) => statusCounts[status.value]).map((status) => ({ ...status, count: statusCounts[status.value] }))].map((status) => (
                        <button
                            key={status.value || 'all'}
                            type="button"
                            aria-pressed={values.status === status.value}
                            onClick={() => set('status', status.value)}
                            className={cn(
                                'inline-flex min-h-10 items-center gap-2 rounded-full px-3.5 text-sm font-medium ring-1 ring-inset',
                                values.status === status.value ? 'bg-primary-600 text-white ring-primary-600' : 'bg-white text-slate-700 ring-slate-300 hover:bg-slate-50',
                            )}
                        >
                            {status.label}
                            <span className="tabular-nums opacity-80">{status.count}</span>
                        </button>
                    ))}
                </div>
            )}

            <Card>
                <div className="flex gap-2 border-b border-slate-100 p-4 sm:px-6 md:items-end">
                    <SearchInput
                        value={values.search}
                        onChange={(value) => set('search', value, { debounce: true })}
                        placeholder="Order number…"
                        className="min-w-0 flex-1 md:max-w-xs"
                    />
                    <FilterPanel activeCount={activeFilters} onReset={() => reset(FILTER_KEYS)}>
                        <FilterField label="Client" htmlFor="filter-client">
                            <Select id="filter-client" placeholder="All clients" options={clients} value={values.client} onChange={(event) => set('client', event.target.value)} />
                        </FilterField>
                        <FilterField label="Meal" htmlFor="filter-meal">
                            <Select id="filter-meal" placeholder="All meals" options={mealTypes} value={values.meal_type} onChange={(event) => set('meal_type', event.target.value)} />
                        </FilterField>
                        <FilterField label="Status" htmlFor="filter-status">
                            <Select id="filter-status" placeholder="Any status" options={statuses} value={values.status} onChange={(event) => set('status', event.target.value)} />
                        </FilterField>
                    </FilterPanel>
                </div>

                <DataTable
                    caption="Orders"
                    columns={columns}
                    rows={orders.data}
                    loading={loading}
                    rowHref={(order) => route('orders.show', order.id)}
                    mobileCard={(order) => (
                        <div className="space-y-1.5">
                            <div className="flex items-start justify-between gap-2">
                                <p className="min-w-0 truncate font-semibold text-slate-900">{order.client?.name}</p>
                                <StatusBadge status={order.status} label={order.status_label} />
                            </div>
                            <p className="text-sm text-slate-600">
                                {order.meal_type_label} · {formatNumber(order.quantity)} meals
                                {order.amount !== null && ` · ${money(order.amount)}`}
                            </p>
                            <p className="text-xs text-slate-500">
                                {order.order_number} · {order.rider ? `Rider: ${order.rider.name}` : 'No rider yet'}
                            </p>
                        </div>
                    )}
                    empty={
                        activeFilters > 0 || values.search ? (
                            <EmptyState icon={SearchX} title="No orders match" />
                        ) : (
                            <EmptyState
                                icon={ClipboardList}
                                title="No orders for this day"
                                description="Orders appear here as soon as meals are confirmed on the meal sheet."
                            />
                        )
                    }
                />
                <Pagination meta={orders.meta} links={orders.links} />
            </Card>
        </>
    );
}
