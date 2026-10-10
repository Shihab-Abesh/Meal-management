import { Head, Link } from '@inertiajs/react';
import { Building2, CalendarX, Printer } from 'lucide-react';
import AmountTiles from '@/components/billing/amount-tiles';
import Button from '@/components/ui/button';
import Card, { CardHeader } from '@/components/ui/card';
import DateNavigator from '@/components/ui/date-navigator';
import EmptyState from '@/components/ui/empty-state';
import PageHeader from '@/components/ui/page-header';
import Select from '@/components/ui/select';
import StatusBadge from '@/components/ui/status-badge';
import { useApp } from '@/hooks/use-app';
import { useFilters } from '@/hooks/use-filters';
import { formatDate, formatNumber, parseDate } from '@/lib/format';

// "Thu 01": the month is already in the heading.
function dayLabel(date) {
    return parseDate(date).toLocaleDateString('en-GB', { weekday: 'short', day: '2-digit' });
}

function monthLabel(month) {
    const [year, number] = month.split('-').map(Number);

    return new Date(year, number - 1, 1).toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
}

/**
 * Monthly statement: meals and amount for every day, the bills covering
 * the month and payments made in it.
 */
export default function Statement({ client, month, today, statement, balance, clients }) {
    const { money } = useApp();
    const { values, set, loading } = useFilters(route('billing.statement'), { client: client ? String(client.id) : '', month });
    const isVendor = clients !== null;

    return (
        <>
            <Head title={client ? `Statement · ${client.name}` : 'Statement'} />

            <PageHeader
                title="Monthly statement"
                description={client ? `${client.name} · ${monthLabel(month)}` : 'Meals, bills and payments for one client and month.'}
                back={isVendor ? null : { href: route('billing.index'), label: 'Billing' }}
                actions={
                    client && (
                        <Button variant="secondary" icon={Printer} onClick={() => window.print()} className="w-full sm:w-auto">
                            Print
                        </Button>
                    )
                }
            />

            <div className="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center print:hidden">
                {isVendor && (
                    <Select
                        aria-label="Client"
                        placeholder="Choose a client"
                        options={clients}
                        value={values.client}
                        onChange={(event) => set('client', event.target.value)}
                        className="sm:w-64"
                    />
                )}
                <DateNavigator mode="month" label="Month" value={values.month} today={today} onChange={(value) => set('month', value)} />
            </div>

            {!client ? (
                <Card>
                    <EmptyState icon={Building2} title="Choose a client" description="Pick a client to see their statement for the month." />
                </Card>
            ) : (
                <div className={loading ? 'opacity-60 transition-opacity' : 'transition-opacity'} aria-busy={loading || undefined}>
                    <AmountTiles
                        className="mb-6"
                        tiles={[
                            {
                                key: 'meals',
                                label: 'Meals this month',
                                value: formatNumber(statement.totals.meals),
                                format: 'number',
                                hint: `${formatNumber(statement.totals.lunch)} lunch · ${formatNumber(statement.totals.dinner)} dinner`,
                            },
                            { key: 'amount', label: 'Amount this month', value: statement.totals.amount },
                            {
                                key: 'due',
                                label: 'Current balance',
                                value: balance.due,
                                tone: Number(balance.overdue) > 0 ? 'bad' : Number(balance.due) > 0 ? 'warn' : 'good',
                                hint: 'All unpaid bills',
                            },
                        ]}
                    />

                    <div className="grid gap-6 lg:grid-cols-3">
                        <Card className="min-w-0 lg:col-span-2">
                            <CardHeader title="Day by day" description={monthLabel(month)} />
                            {statement.days.length === 0 ? (
                                <EmptyState icon={CalendarX} title="No meals this month" />
                            ) : (
                                <table className="min-w-full text-sm">
                                    <thead>
                                        <tr className="text-left text-xs font-semibold tracking-wide text-slate-500 uppercase">
                                            <th scope="col" className="py-3 pr-2 pl-4 sm:pl-6">Day</th>
                                            <th scope="col" className="px-2 py-3 text-right sm:px-4">Lunch</th>
                                            <th scope="col" className="px-2 py-3 text-right sm:px-4">Dinner</th>
                                            <th scope="col" className="hidden px-4 py-3 text-right sm:table-cell">Meals</th>
                                            <th scope="col" className="py-3 pr-4 pl-2 text-right sm:pr-6">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100">
                                        {statement.days.map((day) => (
                                            <tr key={day.date}>
                                                <td className="py-2.5 pr-2 pl-4 whitespace-nowrap sm:pl-6">{dayLabel(day.date)}</td>
                                                <td className="px-2 py-2.5 text-right tabular-nums sm:px-4">{day.lunch || '–'}</td>
                                                <td className="px-2 py-2.5 text-right tabular-nums sm:px-4">{day.dinner || '–'}</td>
                                                <td className="hidden px-4 py-2.5 text-right tabular-nums sm:table-cell">{day.meals}</td>
                                                <td className="py-2.5 pr-4 pl-2 text-right tabular-nums sm:pr-6">{money(day.amount)}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                    <tfoot>
                                        <tr className="border-t border-slate-200 font-semibold text-slate-900">
                                            <td className="py-3 pr-2 pl-4 sm:pl-6">Total</td>
                                            <td className="px-2 py-3 text-right tabular-nums sm:px-4">{formatNumber(statement.totals.lunch)}</td>
                                            <td className="px-2 py-3 text-right tabular-nums sm:px-4">{formatNumber(statement.totals.dinner)}</td>
                                            <td className="hidden px-4 py-3 text-right tabular-nums sm:table-cell">{formatNumber(statement.totals.meals)}</td>
                                            <td className="py-3 pr-4 pl-2 text-right tabular-nums sm:pr-6">{money(statement.totals.amount)}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            )}
                        </Card>

                        <div className="min-w-0 space-y-6">
                            <Card>
                                <CardHeader title="Bills" />
                                {statement.bills.length === 0 ? (
                                    <p className="px-4 py-4 text-sm text-slate-500 sm:px-6">Not billed yet.</p>
                                ) : (
                                    <ul className="divide-y divide-slate-100">
                                        {statement.bills.map((bill) => (
                                            <li key={bill.id}>
                                                <Link href={route('bills.show', bill.id)} className="flex items-center justify-between gap-3 px-4 py-3 hover:bg-slate-50 sm:px-6">
                                                    <div className="min-w-0">
                                                        <p className="text-sm font-medium text-slate-900">{bill.bill_number}</p>
                                                        <p className="text-xs text-slate-500">
                                                            {bill.period_label} · due {money(bill.due_amount)}
                                                        </p>
                                                    </div>
                                                    <StatusBadge status={bill.status} label={bill.status_label} />
                                                </Link>
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </Card>

                            <Card>
                                <CardHeader title="Payments this month" />
                                {statement.payments.length === 0 ? (
                                    <p className="px-4 py-4 text-sm text-slate-500 sm:px-6">No payments this month.</p>
                                ) : (
                                    <ul className="divide-y divide-slate-100">
                                        {statement.payments.map((payment) => (
                                            <li key={payment.id} className="flex items-center justify-between gap-3 px-4 py-3 sm:px-6">
                                                <div className="min-w-0">
                                                    <p className="text-sm text-slate-900">{formatDate(payment.payment_date)}</p>
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
                    </div>
                </div>
            )}
        </>
    );
}
