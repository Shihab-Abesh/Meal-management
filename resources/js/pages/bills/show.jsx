import { Head, Link, router, usePage } from '@inertiajs/react';
import { CalendarRange, Printer, RefreshCw, Trash2, Wallet } from 'lucide-react';
import { useState } from 'react';
import AmountTiles from '@/components/billing/amount-tiles';
import BillLinesTable from '@/components/billing/bill-lines-table';
import PaymentDialog from '@/components/billing/payment-dialog';
import Alert from '@/components/ui/alert';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import ConfirmDialog from '@/components/ui/confirm-dialog';
import DescriptionList from '@/components/ui/description-list';
import EmptyState from '@/components/ui/empty-state';
import PageHeader from '@/components/ui/page-header';
import StatusBadge from '@/components/ui/status-badge';
import { useApp } from '@/hooks/use-app';
import { formatDate, formatDateTime, formatNumber } from '@/lib/format';

export default function ShowBill({ bill, payments, outOfDate, paymentMethods, today, can }) {
    const { money, name: vendorName } = useApp();
    const { errors = {}, auth } = usePage().props;
    const isVendor = auth?.user?.is_vendor;
    const [payOpen, setPayOpen] = useState(false);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [removing, setRemoving] = useState(null);
    const [busy, setBusy] = useState(null);
    const hasDue = Number(bill.due_amount) > 0;

    function recalculate() {
        router.post(route('bills.recalculate', bill.id), {}, { preserveScroll: true, onStart: () => setBusy('recalculate'), onFinish: () => setBusy(null) });
    }

    function destroy() {
        router.delete(route('bills.destroy', bill.id), { onStart: () => setBusy('delete'), onFinish: () => setBusy(null) });
    }

    function removePayment() {
        router.delete(route('payments.destroy', removing.id), {
            preserveScroll: true,
            onStart: () => setBusy('remove'),
            onFinish: () => {
                setBusy(null);
                setRemoving(null);
            },
        });
    }

    return (
        <>
            <Head title={`Bill ${bill.bill_number}`} />

            <PageHeader
                title={bill.client?.name}
                description={
                    <span className="inline-flex flex-wrap items-center gap-2">
                        <StatusBadge status={bill.status} label={bill.status_label} />
                        <span>
                            {bill.bill_number} · {bill.period_label}
                        </span>
                    </span>
                }
                back={isVendor ? { href: route('bills.index'), label: 'Bills' } : { href: route('billing.index'), label: 'Billing' }}
                actions={
                    <div className="flex w-full flex-wrap gap-2 sm:w-auto">
                        {can.pay && hasDue && (
                            <Button icon={Wallet} onClick={() => setPayOpen(true)} className="flex-1 sm:flex-none">
                                Record payment
                            </Button>
                        )}
                        <Button variant="secondary" icon={Printer} onClick={() => window.print()} className="flex-1 sm:flex-none">
                            Print
                        </Button>
                    </div>
                }
            />

            {(errors.bill || errors.amount) && (
                <Alert tone="error" className="mb-6">
                    {errors.bill ?? errors.amount}
                </Alert>
            )}

            {outOfDate && (
                <Alert tone="info" className="mb-6 print:hidden">
                    <span className="block">Meals in this period changed after the bill was made.</span>
                    {can.update && (
                        <Button size="sm" variant="secondary" icon={RefreshCw} loading={busy === 'recalculate'} onClick={recalculate} className="mt-2">
                            Recalculate now
                        </Button>
                    )}
                </Alert>
            )}

            <AmountTiles
                className="mb-6"
                tiles={[
                    { key: 'total', label: 'Total', value: bill.total_amount, hint: `${formatNumber(bill.total_meals)} meals` },
                    { key: 'paid', label: 'Paid', value: bill.paid_amount, tone: 'good' },
                    {
                        key: 'due',
                        label: 'Due',
                        value: bill.due_amount,
                        tone: bill.status === 'overdue' ? 'bad' : hasDue ? 'warn' : 'default',
                        hint: hasDue ? `Pay by ${formatDate(bill.due_date)}` : 'Fully paid',
                    },
                ]}
            />

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="min-w-0 space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader title="Meals billed" description={`${formatDate(bill.period_start)} – ${formatDate(bill.period_end)}`} />
                        <BillLinesTable lines={bill.lines} totalMeals={bill.total_meals} totalAmount={bill.total_amount} />
                        <div className="border-t border-slate-100 px-4 py-3 sm:px-6 print:hidden">
                            <Link
                                href={route('billing.statement', { client: bill.client?.id, month: bill.period_start.slice(0, 7) })}
                                className="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-primary-700 hover:text-primary-800"
                            >
                                <CalendarRange className="size-4" aria-hidden="true" />
                                Day-by-day statement
                            </Link>
                        </div>
                    </Card>

                    <Card>
                        <CardHeader title="Payments" />
                        {payments.length === 0 ? (
                            <EmptyState icon={Wallet} title="No payments yet" description={hasDue ? 'Payments recorded against this bill appear here.' : undefined} />
                        ) : (
                            <ul className="divide-y divide-slate-100">
                                {payments.map((payment) => (
                                    <li key={payment.id} className="flex items-center justify-between gap-3 px-4 py-3 sm:px-6">
                                        <div className="min-w-0">
                                            <p className="text-sm font-medium text-slate-900">
                                                {money(payment.amount)} · {payment.method_label}
                                            </p>
                                            <p className="truncate text-xs text-slate-500">
                                                {[formatDate(payment.payment_date), payment.reference, payment.received_by && `by ${payment.received_by}`, payment.note]
                                                    .filter(Boolean)
                                                    .join(' · ')}
                                            </p>
                                        </div>
                                        {can.pay && (
                                            <Button variant="ghost" size="sm" className="shrink-0 text-red-600 hover:text-red-700 print:hidden" onClick={() => setRemoving(payment)}>
                                                Remove
                                            </Button>
                                        )}
                                    </li>
                                ))}
                            </ul>
                        )}
                    </Card>
                </div>

                <div className="min-w-0 space-y-6">
                    <Card>
                        <CardHeader title="Bill" />
                        <CardBody>
                            <DescriptionList
                                className="sm:grid-cols-1"
                                items={[
                                    { label: 'From', value: vendorName },
                                    { label: 'Billed to', value: [bill.client?.name, bill.client_details.address].filter(Boolean).join(', ') },
                                    { label: 'Contact', value: [bill.client_details.contact_person, bill.client_details.phone].filter(Boolean).join(' · ') },
                                    { label: 'Issued', value: formatDate(bill.issue_date) },
                                    { label: 'Due date', value: formatDate(bill.due_date) },
                                    { label: 'Note', value: bill.notes },
                                ]}
                            />
                            {bill.created_by && (
                                <p className="mt-5 text-xs text-slate-500">
                                    Created by {bill.created_by} · {formatDateTime(bill.created_at)}
                                </p>
                            )}
                        </CardBody>
                    </Card>

                    {(can.update || can.delete) && (
                        <Card className="print:hidden">
                            <CardBody className="flex flex-col gap-2">
                                {can.update && (
                                    <Button variant="secondary" icon={RefreshCw} loading={busy === 'recalculate'} onClick={recalculate}>
                                        Recalculate from meals
                                    </Button>
                                )}
                                {can.delete && (
                                    <Button variant="ghost" icon={Trash2} className="text-red-600 hover:text-red-700" onClick={() => setDeleteOpen(true)}>
                                        Delete bill
                                    </Button>
                                )}
                            </CardBody>
                        </Card>
                    )}
                </div>
            </div>

            {can.pay && hasDue && (
                <PaymentDialog key={bill.due_amount} open={payOpen} onClose={() => setPayOpen(false)} bill={bill} methods={paymentMethods} today={today} />
            )}

            <ConfirmDialog
                open={deleteOpen}
                onClose={() => setDeleteOpen(false)}
                onConfirm={destroy}
                processing={busy === 'delete'}
                title={`Delete bill ${bill.bill_number}?`}
                description="The meals in this period can then be billed again."
                confirmLabel="Delete bill"
            />

            <ConfirmDialog
                open={removing !== null}
                onClose={() => setRemoving(null)}
                onConfirm={removePayment}
                processing={busy === 'remove'}
                title="Remove this payment?"
                description={removing ? `${money(removing.amount)} (${removing.method_label}, ${formatDate(removing.payment_date)}) will be owed again. The removal is kept in the activity log.` : ''}
                confirmLabel="Remove payment"
            />
        </>
    );
}
