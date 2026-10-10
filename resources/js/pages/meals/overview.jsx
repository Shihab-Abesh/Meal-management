import { Head, Link, router } from '@inertiajs/react';
import { Building2, ChevronRight, Lock } from 'lucide-react';
import Card from '@/components/ui/card';
import DateNavigator from '@/components/ui/date-navigator';
import EmptyState from '@/components/ui/empty-state';
import PageHeader from '@/components/ui/page-header';
import { formatLongDate, formatNumber, formatTime } from '@/lib/format';
import { cn } from '@/lib/utils';

/**
 * Vendor view of a day: every active client with its meal counts. Tap a
 * client to open its meal sheet.
 */
export default function MealsOverview({ date, today, maxDate, clients }) {
    const totals = { lunch: 0, dinner: 0 };

    for (const client of clients) {
        for (const meal of client.meals) {
            totals[meal.meal_type] += meal.count;
        }
    }

    return (
        <>
            <Head title="Daily meals" />

            <PageHeader title="Daily meals" description={formatLongDate(date)} />

            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <DateNavigator
                    value={date}
                    today={today}
                    max={maxDate}
                    onChange={(value) => router.get(route('meals.index'), { date: value }, { preserveScroll: true })}
                />
                <dl className="grid grid-cols-3 gap-2 text-center sm:flex sm:gap-6 sm:text-left">
                    <Total label="Lunch" value={totals.lunch} />
                    <Total label="Dinner" value={totals.dinner} />
                    <Total label="Total" value={totals.lunch + totals.dinner} strong />
                </dl>
            </div>

            {clients.length === 0 ? (
                <Card>
                    <EmptyState
                        icon={Building2}
                        title="No active clients"
                        description="Add a client and its employees to start recording meals."
                        action={
                            <Link href={route('clients.create')} className="text-sm font-semibold text-primary-700">
                                Add a client
                            </Link>
                        }
                    />
                </Card>
            ) : (
                <Card>
                    <ul className="divide-y divide-slate-100">
                        {clients.map((client) => (
                            <li key={client.id}>
                                <Link
                                    href={route('meals.index', { client: client.id, date })}
                                    className="flex items-center gap-3 px-4 py-4 hover:bg-slate-50 sm:px-6"
                                >
                                    <div className="min-w-0 flex-1">
                                        <p className="truncate font-semibold text-slate-900">{client.name}</p>
                                        <p className="text-sm text-slate-500">{formatNumber(client.employees)} active employees</p>
                                    </div>
                                    <div className="flex shrink-0 gap-2">
                                        {client.meals.map((meal) => (
                                            <div
                                                key={meal.meal_type}
                                                className={cn(
                                                    'w-20 rounded-lg px-2 py-1.5 text-center ring-1 ring-inset sm:w-24',
                                                    meal.count > 0 ? 'bg-primary-50 ring-primary-200' : 'bg-white ring-slate-200',
                                                )}
                                            >
                                                <p className="flex items-center justify-center gap-1 text-[11px] font-medium text-slate-500 uppercase">
                                                    {meal.closed && <Lock className="size-3" aria-label="Closed" />}
                                                    {meal.label}
                                                </p>
                                                <p className="text-lg font-bold text-slate-900 tabular-nums">{formatNumber(meal.count)}</p>
                                                <p className="sr-only">Cutoff {formatTime(meal.cutoff)}</p>
                                            </div>
                                        ))}
                                    </div>
                                    <ChevronRight className="size-5 shrink-0 text-slate-300" aria-hidden="true" />
                                </Link>
                            </li>
                        ))}
                    </ul>
                </Card>
            )}
        </>
    );
}

function Total({ label, value, strong = false }) {
    return (
        <div className="rounded-lg bg-white px-3 py-2 ring-1 ring-slate-200 sm:bg-transparent sm:p-0 sm:ring-0">
            <dt className="text-xs text-slate-500">{label}</dt>
            <dd className={cn('text-xl font-bold tabular-nums', strong ? 'text-primary-700' : 'text-slate-900')}>{formatNumber(value)}</dd>
        </div>
    );
}
