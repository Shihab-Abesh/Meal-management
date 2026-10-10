import { Head, Link } from '@inertiajs/react';
import { FilePlus2, FileText, SearchX } from 'lucide-react';
import AmountTiles from '@/components/billing/amount-tiles';
import Button from '@/components/ui/button';
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

const FILTER_KEYS = ['client', 'status', 'month'];

export default function BillsIndex({ bills, totals, filters, clients, statuses, can }) {
    const { money } = useApp();
    const { values, set, reset, loading } = useFilters(route('bills.index'), filters);
    const activeFilters = FILTER_KEYS.filter((key) => values[key]).length;

    const columns = [
        {
            key: 'bill',
            header: 'Bill',
            primary: true,
            render: (bill) => (
                <span className="block min-w-0">
                    <span className="block truncate font-medium text-slate-900">{bill.client?.name}</span>
                    <span className="block text-xs text-slate-500">{bill.bill_number}</span>
                </span>
            ),
        },
        { key: 'period', header: 'Period', render: (bill) => bill.period_label },
        { key: 'meals', header: 'Meals', align: 'right', className: 'hidden xl:table-cell', render: (bill) => formatNumber(bill.total_meals) },
        { key: 'total', header: 'Total', align: 'right', render: (bill) => money(bill.total_amount) },
        { key: 'paid', header: 'Paid', align: 'right', className: 'hidden lg:table-cell', render: (bill) => money(bill.paid_amount) },
        { key: 'due', header: 'Due', align: 'right', render: (bill) => <span className="font-semibold">{money(bill.due_amount)}</span> },
        { key: 'status', header: 'Status', render: (bill) => <StatusBadge status={bill.status} label={bill.status_label} /> },
    ];

    return (
        <>
            <Head title="Bills" />

            <PageHeader
                title="Bills"
                description="Billed from confirmed meals at each client's own price."
                actions={
                    can.create && (
                        <Button as={Link} href={route('bills.create')} icon={FilePlus2} className="w-full sm:w-auto">
                            Generate bill
                        </Button>
                    )
                }
            />

            <AmountTiles
                className="mb-6"
                tiles={[
                    { key: 'billed', label: 'Billed', value: totals.billed, hint: `${formatNumber(totals.bills)} bills` },
                    { key: 'paid', label: 'Paid', value: totals.paid, tone: 'good' },
                    { key: 'due', label: 'Due', value: totals.due, tone: Number(totals.due) > 0 ? 'warn' : 'default' },
                ]}
            />

            <Card>
                <div className="flex gap-2 border-b border-slate-100 p-4 sm:px-6 md:items-end">
                    <SearchInput
                        value={values.search}
                        onChange={(value) => set('search', value, { debounce: true })}
                        placeholder="Bill number or client…"
                        className="min-w-0 flex-1 md:max-w-xs"
                    />
                    <FilterPanel activeCount={activeFilters} onReset={() => reset(FILTER_KEYS)}>
                        <FilterField label="Client" htmlFor="filter-client">
                            <Select id="filter-client" placeholder="All clients" options={clients} value={values.client} onChange={(event) => set('client', event.target.value)} />
                        </FilterField>
                        <FilterField label="Status" htmlFor="filter-status">
                            <Select id="filter-status" placeholder="Any status" options={statuses} value={values.status} onChange={(event) => set('status', event.target.value)} />
                        </FilterField>
                        <FilterField label="Month" htmlFor="filter-month">
                            <DateInput id="filter-month" type="month" value={values.month} onChange={(event) => set('month', event.target.value)} />
                        </FilterField>
                    </FilterPanel>
                </div>

                <DataTable
                    caption="Bills"
                    columns={columns}
                    rows={bills.data}
                    loading={loading}
                    rowHref={(bill) => route('bills.show', bill.id)}
                    mobileCard={(bill) => (
                        <div className="space-y-1.5">
                            <div className="flex items-start justify-between gap-2">
                                <p className="min-w-0 truncate font-semibold text-slate-900">{bill.client?.name}</p>
                                <StatusBadge status={bill.status} label={bill.status_label} />
                            </div>
                            <p className="text-sm text-slate-600">
                                {bill.period_label} · {formatNumber(bill.total_meals)} meals
                            </p>
                            <p className="flex flex-wrap gap-x-3 text-sm">
                                <span className="text-slate-500">Total {money(bill.total_amount)}</span>
                                <span className="font-semibold text-slate-900">Due {money(bill.due_amount)}</span>
                            </p>
                            <p className="text-xs text-slate-500">
                                {bill.bill_number} · due by {formatDate(bill.due_date)}
                            </p>
                        </div>
                    )}
                    empty={
                        activeFilters > 0 || values.search ? (
                            <EmptyState icon={SearchX} title="No bills match" description="Try another search or clear the filters." />
                        ) : (
                            <EmptyState
                                icon={FileText}
                                title="No bills yet"
                                description="Generate a bill for a client and period; it is calculated from the confirmed meals."
                                action={
                                    can.create && (
                                        <Button as={Link} href={route('bills.create')} icon={FilePlus2}>
                                            Generate bill
                                        </Button>
                                    )
                                }
                            />
                        )
                    }
                />
                <Pagination meta={bills.meta} links={bills.links} />
            </Card>
        </>
    );
}
