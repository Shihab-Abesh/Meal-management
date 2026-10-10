import { Head, Link } from '@inertiajs/react';
import { CalendarRange, FileText, Wallet } from 'lucide-react';
import AmountTiles from '@/components/billing/amount-tiles';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import DataTable from '@/components/ui/data-table';
import EmptyState from '@/components/ui/empty-state';
import PageHeader from '@/components/ui/page-header';
import Pagination from '@/components/ui/pagination';
import StatusBadge from '@/components/ui/status-badge';
import { useApp } from '@/hooks/use-app';
import { formatDate, formatNumber } from '@/lib/format';

/**
 * Billing for a client account: what is owed, bills, payments and meals
 * that will appear on the next bill.
 */
export default function ClientBilling({ client, balance, unbilled, bills, payments, month }) {
    const { money } = useApp();

    const columns = [
        {
            key: 'period',
            header: 'Bill',
            primary: true,
            render: (bill) => (
                <span className="block min-w-0">
                    <span className="block font-medium text-slate-900">{bill.period_label}</span>
                    <span className="block text-xs text-slate-500">{bill.bill_number}</span>
                </span>
            ),
        },
        { key: 'meals', header: 'Meals', align: 'right', className: 'hidden xl:table-cell', render: (bill) => formatNumber(bill.total_meals) },
        { key: 'total', header: 'Total', align: 'right', render: (bill) => money(bill.total_amount) },
        { key: 'due', header: 'Due', align: 'right', render: (bill) => <span className="font-semibold">{money(bill.due_amount)}</span> },
        { key: 'due_date', header: 'Pay by', className: 'hidden xl:table-cell', render: (bill) => formatDate(bill.due_date) },
        { key: 'status', header: 'Status', render: (bill) => <StatusBadge status={bill.status} label={bill.status_label} /> },
    ];

    return (
        <>
            <Head title="Billing" />

            <PageHeader
                title="Billing"
                description={client.name}
                actions={
                    <Button as={Link} href={route('billing.statement', { month })} variant="secondary" icon={CalendarRange} className="w-full sm:w-auto">
                        Monthly statement
                    </Button>
                }
            />

            <AmountTiles
                className="mb-6"
                tiles={[
                    {
                        key: 'due',
                        label: 'Current balance',
                        value: balance.due,
                        tone: Number(balance.overdue) > 0 ? 'bad' : Number(balance.due) > 0 ? 'warn' : 'good',
                        hint: Number(balance.overdue) > 0 ? `${money(balance.overdue)} overdue` : Number(balance.due) > 0 ? 'Still to pay' : 'Nothing owed',
                    },
                    { key: 'paid', label: 'Paid', value: balance.paid, tone: 'good', hint: 'All time' },
                    { key: 'billed', label: 'Billed', value: balance.billed, hint: 'All time' },
                    {
                        key: 'unbilled',
                        label: 'Not billed yet',
                        value: unbilled.amount,
                        hint: `${formatNumber(unbilled.meals)} meals${unbilled.from ? ` since ${formatDate(unbilled.from)}` : ''}`,
                    },
                ]}
            />

            <div className="grid gap-6 lg:grid-cols-3">
                <Card className="min-w-0 lg:col-span-2">
                    <CardHeader title="Bills" />
                    <DataTable
                        caption="Bills"
                        columns={columns}
                        rows={bills.data}
                        rowHref={(bill) => route('bills.show', bill.id)}
                        mobileCard={(bill) => (
                            <div className="space-y-1">
                                <div className="flex items-start justify-between gap-2">
                                    <p className="font-semibold text-slate-900">{bill.period_label}</p>
                                    <StatusBadge status={bill.status} label={bill.status_label} />
                                </div>
                                <p className="text-sm text-slate-600">
                                    {formatNumber(bill.total_meals)} meals · Total {money(bill.total_amount)}
                                </p>
                                <p className="text-sm">
                                    <span className="font-semibold text-slate-900">Due {money(bill.due_amount)}</span>
                                    <span className="text-slate-500"> · pay by {formatDate(bill.due_date)}</span>
                                </p>
                            </div>
                        )}
                        empty={<EmptyState icon={FileText} title="No bills yet" description="Your bills will appear here when the vendor issues them." />}
                    />
                    <Pagination meta={bills.meta} links={bills.links} />
                </Card>

                <Card className="min-w-0">
                    <CardHeader title="Payment history" />
                    {payments.length === 0 ? (
                        <CardBody>
                            <EmptyState icon={Wallet} title="No payments yet" className="py-4" />
                        </CardBody>
                    ) : (
                        <ul className="divide-y divide-slate-100">
                            {payments.map((payment) => (
                                <li key={payment.id} className="flex items-center justify-between gap-3 px-4 py-3 sm:px-6">
                                    <div className="min-w-0">
                                        <p className="text-sm font-medium text-slate-900">{formatDate(payment.payment_date)}</p>
                                        <p className="truncate text-xs text-slate-500">
                                            {[payment.method_label, payment.reference, payment.bill?.bill_number].filter(Boolean).join(' · ')}
                                        </p>
                                    </div>
                                    <span className="shrink-0 text-sm font-semibold text-emerald-700 tabular-nums">{money(payment.amount)}</span>
                                </li>
                            ))}
                        </ul>
                    )}
                </Card>
            </div>
        </>
    );
}
