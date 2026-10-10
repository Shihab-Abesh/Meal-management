import { Head, Link } from '@inertiajs/react';
import { History, X } from 'lucide-react';
import Badge from '@/components/ui/badge';
import Card from '@/components/ui/card';
import DataTable from '@/components/ui/data-table';
import DateInput from '@/components/ui/date-input';
import EmptyState from '@/components/ui/empty-state';
import FilterPanel, { FilterField } from '@/components/ui/filter-panel';
import PageHeader from '@/components/ui/page-header';
import Pagination from '@/components/ui/pagination';
import SearchInput from '@/components/ui/search-input';
import Select from '@/components/ui/select';
import StatusBadge from '@/components/ui/status-badge';
import { useApp } from '@/hooks/use-app';
import { useFilters } from '@/hooks/use-filters';
import { formatDate, formatNumber } from '@/lib/format';

const FILTER_KEYS = ['client', 'meal_type', 'status'];

export default function MealHistory({ entries, summary, filters, employee, clients, mealTypes, statuses }) {
    const { money } = useApp();
    const { values, set, setMany, reset, loading } = useFilters(route('meals.history'), filters);
    const showAmounts = summary.amount !== null;
    const activeFilters = FILTER_KEYS.filter((key) => values[key]).length;

    const columns = [
        {
            key: 'employee',
            header: 'Employee',
            primary: true,
            render: (entry) => (
                <span className="block min-w-0">
                    <span className="block truncate font-medium text-slate-900">{entry.employee.name}</span>
                    <span className="block text-xs text-slate-500">{entry.employee.code}</span>
                </span>
            ),
        },
        { key: 'meal_date', header: 'Date', render: (entry) => formatDate(entry.meal_date) },
        clients && { key: 'client', header: 'Client', className: 'hidden lg:table-cell', render: (entry) => entry.client?.name },
        {
            key: 'meal_type',
            header: 'Meal',
            render: (entry) => <Badge tone={entry.meal_type === 'lunch' ? 'amber' : 'violet'}>{entry.meal_type_label}</Badge>,
        },
        { key: 'status', header: 'Status', render: (entry) => <StatusBadge status={entry.status} label={entry.status_label} /> },
        showAmounts && { key: 'unit_price', header: 'Price', align: 'right', render: (entry) => money(entry.unit_price) },
        { key: 'confirmed_by', header: 'By', className: 'hidden xl:table-cell' },
    ].filter(Boolean);

    return (
        <>
            <Head title="Meal history" />

            <PageHeader title="Meal history" description="Every meal recorded, including changes." />

            <Card className="mb-4">
                <div className="flex flex-col gap-3 p-4 sm:px-6 md:flex-row md:flex-wrap md:items-end">
                    <div className="grid grid-cols-2 gap-2 md:flex md:gap-3">
                        <FilterField label="From" htmlFor="filter-from">
                            <DateInput id="filter-from" value={values.from} max={values.to} onChange={(event) => set('from', event.target.value)} />
                        </FilterField>
                        <FilterField label="To" htmlFor="filter-to">
                            <DateInput id="filter-to" value={values.to} min={values.from} onChange={(event) => set('to', event.target.value)} />
                        </FilterField>
                    </div>
                    <div className="flex gap-2 md:contents">
                        <SearchInput
                            value={values.search}
                            onChange={(value) => set('search', value, { debounce: true })}
                            placeholder="Employee name or ID…"
                            label="Search employee"
                            className="min-w-0 flex-1 md:max-w-xs"
                        />
                        <FilterPanel activeCount={activeFilters} onReset={() => reset(FILTER_KEYS)}>
                            {clients && (
                                <FilterField label="Client" htmlFor="filter-client">
                                    <Select
                                        id="filter-client"
                                        placeholder="All clients"
                                        options={clients}
                                        value={values.client}
                                        onChange={(event) => set('client', event.target.value)}
                                    />
                                </FilterField>
                            )}
                            <FilterField label="Meal" htmlFor="filter-meal">
                                <Select
                                    id="filter-meal"
                                    placeholder="All meals"
                                    options={mealTypes}
                                    value={values.meal_type}
                                    onChange={(event) => set('meal_type', event.target.value)}
                                />
                            </FilterField>
                            <FilterField label="Status" htmlFor="filter-status">
                                <Select
                                    id="filter-status"
                                    placeholder="Any status"
                                    options={statuses}
                                    value={values.status}
                                    onChange={(event) => set('status', event.target.value)}
                                />
                            </FilterField>
                        </FilterPanel>
                    </div>
                </div>
                {employee && (
                    <div className="flex items-center gap-2 border-t border-slate-100 px-4 py-3 text-sm sm:px-6">
                        <span className="text-slate-500">Employee:</span>
                        <Link href={route('employees.show', employee.id)} className="font-medium text-primary-700">
                            {employee.name} ({employee.code})
                        </Link>
                        <button
                            type="button"
                            onClick={() => setMany({ employee: '' })}
                            className="ml-auto flex size-9 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                        >
                            <span className="sr-only">Show all employees</span>
                            <X className="size-4" aria-hidden="true" />
                        </button>
                    </div>
                )}
            </Card>

            <dl className="mb-4 grid grid-cols-2 gap-3 sm:max-w-md">
                <Card className="px-4 py-3">
                    <dt className="text-xs text-slate-500">Confirmed meals</dt>
                    <dd className="text-xl font-bold text-slate-900 tabular-nums">{formatNumber(summary.meals)}</dd>
                </Card>
                {showAmounts && (
                    <Card className="px-4 py-3">
                        <dt className="text-xs text-slate-500">Amount</dt>
                        <dd className="text-xl font-bold text-slate-900 tabular-nums">{money(summary.amount)}</dd>
                    </Card>
                )}
            </dl>

            <Card>
                <DataTable
                    caption="Meal history"
                    columns={columns}
                    rows={entries.data}
                    loading={loading}
                    mobileCard={(entry) => (
                        <div className="flex items-start justify-between gap-3">
                            <div className="min-w-0">
                                <p className="truncate font-medium text-slate-900">{entry.employee.name}</p>
                                <p className="truncate text-sm text-slate-500">
                                    {[formatDate(entry.meal_date), entry.meal_type_label, entry.client?.name].filter(Boolean).join(' · ')}
                                </p>
                            </div>
                            <div className="flex shrink-0 flex-col items-end gap-1">
                                <StatusBadge status={entry.status} label={entry.status_label} />
                                {showAmounts && <span className="text-sm font-medium text-slate-700 tabular-nums">{money(entry.unit_price)}</span>}
                            </div>
                        </div>
                    )}
                    empty={<EmptyState icon={History} title="No meals found" description="Try a different date range or clear the filters." />}
                />
                <Pagination meta={entries.meta} links={entries.links} />
            </Card>
        </>
    );
}
