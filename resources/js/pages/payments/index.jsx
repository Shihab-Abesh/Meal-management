import { Head } from '@inertiajs/react';
import { SearchX, Wallet } from 'lucide-react';
import AmountTiles from '@/components/billing/amount-tiles';
import Card from '@/components/ui/card';
import DataTable from '@/components/ui/data-table';
import DateInput from '@/components/ui/date-input';
import EmptyState from '@/components/ui/empty-state';
import FilterPanel, { FilterField } from '@/components/ui/filter-panel';
import PageHeader from '@/components/ui/page-header';
import Pagination from '@/components/ui/pagination';
import SearchInput from '@/components/ui/search-input';
import Select from '@/components/ui/select';
import { useApp } from '@/hooks/use-app';
import { useFilters } from '@/hooks/use-filters';
import { formatDate, formatNumber } from '@/lib/format';

const FILTER_KEYS = ['client', 'method'];

export default function PaymentsIndex({ payments, summary, filters, clients, methods }) {
    const { money } = useApp();
    const { values, set, setMany, reset, loading } = useFilters(route('payments.index'), filters);
    const activeFilters = FILTER_KEYS.filter((key) => values[key]).length;

    const columns = [
        {
            key: 'client',
            header: 'Client',
            primary: true,
            render: (payment) => (
                <span className="block min-w-0">
                    <span className="block truncate font-medium text-slate-900">{payment.client?.name}</span>
                    <span className="block text-xs text-slate-500">{payment.bill?.bill_number}</span>
                </span>
            ),
        },
        { key: 'payment_date', header: 'Date', render: (payment) => formatDate(payment.payment_date) },
        { key: 'method', header: 'Method', render: (payment) => payment.method_label },
        { key: 'reference', header: 'Reference', className: 'hidden lg:table-cell', render: (payment) => payment.reference },
        { key: 'received_by', header: 'Received by', className: 'hidden xl:table-cell', render: (payment) => payment.received_by },
        { key: 'amount', header: 'Amount', align: 'right', render: (payment) => <span className="font-semibold">{money(payment.amount)}</span> },
    ];

    return (
        <>
            <Head title="Payments" />

            <PageHeader title="Payments" description="Money received from clients. Record payments from a bill." />

            <AmountTiles
                className="mb-6"
                tiles={[
                    { key: 'total', label: 'Received', value: summary.amount, hint: `${formatNumber(summary.payments)} payments`, tone: 'good' },
                    ...summary.by_method.map((method) => ({ key: method.method, label: method.label, value: method.amount })),
                ]}
            />

            <Card>
                <div className="flex flex-col gap-3 border-b border-slate-100 p-4 sm:px-6 md:flex-row md:flex-wrap md:items-end">
                    <div className="grid grid-cols-2 gap-3 md:flex">
                        <FilterField label="From" htmlFor="filter-from">
                            <DateInput id="filter-from" value={values.from} max={values.to} onChange={(event) => event.target.value && setMany({ from: event.target.value })} />
                        </FilterField>
                        <FilterField label="To" htmlFor="filter-to">
                            <DateInput id="filter-to" value={values.to} min={values.from} onChange={(event) => event.target.value && setMany({ to: event.target.value })} />
                        </FilterField>
                    </div>
                    <div className="flex gap-2 md:contents">
                        <SearchInput
                            value={values.search}
                            onChange={(value) => set('search', value, { debounce: true })}
                            placeholder="Reference or bill…"
                            className="min-w-0 flex-1 md:max-w-xs"
                        />
                        <FilterPanel activeCount={activeFilters} onReset={() => reset(FILTER_KEYS)}>
                            <FilterField label="Client" htmlFor="filter-client">
                                <Select id="filter-client" placeholder="All clients" options={clients} value={values.client} onChange={(event) => set('client', event.target.value)} />
                            </FilterField>
                            <FilterField label="Method" htmlFor="filter-method">
                                <Select id="filter-method" placeholder="Any method" options={methods} value={values.method} onChange={(event) => set('method', event.target.value)} />
                            </FilterField>
                        </FilterPanel>
                    </div>
                </div>

                <DataTable
                    caption="Payments"
                    columns={columns}
                    rows={payments.data}
                    loading={loading}
                    rowHref={(payment) => route('bills.show', payment.bill.id)}
                    mobileCard={(payment) => (
                        <div className="space-y-1">
                            <div className="flex items-start justify-between gap-2">
                                <p className="min-w-0 truncate font-semibold text-slate-900">{payment.client?.name}</p>
                                <p className="shrink-0 font-semibold text-slate-900 tabular-nums">{money(payment.amount)}</p>
                            </div>
                            <p className="text-sm text-slate-600">
                                {formatDate(payment.payment_date)} · {payment.method_label}
                                {payment.reference && ` · ${payment.reference}`}
                            </p>
                            <p className="text-xs text-slate-500">{payment.bill?.bill_number}</p>
                        </div>
                    )}
                    empty={
                        activeFilters > 0 || values.search ? (
                            <EmptyState icon={SearchX} title="No payments match" />
                        ) : (
                            <EmptyState icon={Wallet} title="No payments in this period" description="Open a bill and choose Record payment when a client pays." />
                        )
                    }
                />
                <Pagination meta={payments.meta} links={payments.links} />
            </Card>
        </>
    );
}
