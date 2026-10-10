import { Head, Link } from '@inertiajs/react';
import Card from '@/components/ui/card';
import DateNavigator from '@/components/ui/date-navigator';
import PageHeader from '@/components/ui/page-header';
import Select from '@/components/ui/select';
import { useFilters } from '@/hooks/use-filters';
import { formatNumber } from '@/lib/format';
import { cn } from '@/lib/utils';

const WEEKDAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

function pad(number) {
    return String(number).padStart(2, '0');
}

export default function MealCalendar({ month, today, days, totals, client, clients }) {
    const { values, set, setMany, loading } = useFilters(route('meals.calendar'), { month, client });
    const [year, monthNumber] = month.split('-').map(Number);
    const firstWeekday = new Date(year, monthNumber - 1, 1).getDay();
    const daysInMonth = new Date(year, monthNumber, 0).getDate();
    const cells = [...Array(firstWeekday).fill(null), ...Array.from({ length: daysInMonth }, (_, index) => index + 1)];

    function dayUrl(date) {
        if (!clients) {
            return route('meals.index', { date });
        }

        return route('meals.index', values.client ? { client: values.client, date } : { date });
    }

    return (
        <>
            <Head title="Meal calendar" />

            <PageHeader title="Meal calendar" description="Confirmed meals per day. Tap a day to open it." />

            <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <DateNavigator mode="month" value={values.month} today={today} label="Month" onChange={(value) => set('month', value)} />
                {clients && (
                    <Select
                        aria-label="Client"
                        placeholder="All clients"
                        options={clients}
                        value={values.client}
                        onChange={(event) => setMany({ client: event.target.value })}
                        className="sm:w-64"
                    />
                )}
            </div>

            <dl className="mb-4 grid grid-cols-3 gap-3">
                {[
                    ['Lunch', totals.lunch],
                    ['Dinner', totals.dinner],
                    ['Total', totals.lunch + totals.dinner],
                ].map(([label, value]) => (
                    <Card key={label} className="px-4 py-3">
                        <dt className="text-xs text-slate-500">{label} this month</dt>
                        <dd className="text-xl font-bold text-slate-900 tabular-nums">{formatNumber(value)}</dd>
                    </Card>
                ))}
            </dl>

            <Card className={cn('overflow-hidden transition-opacity', loading && 'opacity-60')}>
                <div className="grid grid-cols-7 border-b border-slate-100 bg-slate-50 text-center text-xs font-semibold text-slate-500">
                    {WEEKDAYS.map((day) => (
                        <div key={day} className="py-2">
                            <span className="sm:hidden">{day[0]}</span>
                            <span className="max-sm:hidden">{day}</span>
                        </div>
                    ))}
                </div>
                <div className="grid grid-cols-7">
                    {cells.map((day, index) => {
                        if (day === null) {
                            return <div key={`blank-${index}`} className="min-h-16 border-r border-b border-slate-100 bg-slate-50/50 sm:min-h-24" />;
                        }

                        const date = `${month}-${pad(day)}`;
                        const counts = days[date];
                        const total = counts ? counts.lunch + counts.dinner : 0;

                        return (
                            <Link
                                key={date}
                                href={dayUrl(date)}
                                aria-label={`${date}: ${total} meals`}
                                className={cn(
                                    'flex min-h-16 flex-col border-r border-b border-slate-100 p-1.5 hover:bg-primary-50/60 sm:min-h-24 sm:p-2',
                                    date === today && 'bg-primary-50',
                                )}
                            >
                                <span className={cn('text-xs font-semibold', date === today ? 'text-primary-700' : 'text-slate-600')}>{day}</span>
                                {total > 0 && (
                                    <>
                                        <span className="mt-auto text-center text-sm font-bold text-slate-900 tabular-nums sm:hidden">{total}</span>
                                        <span className="mt-auto hidden space-y-0.5 text-xs sm:block">
                                            {counts.lunch > 0 && <span className="block rounded bg-amber-50 px-1.5 py-0.5 text-amber-800">L {counts.lunch}</span>}
                                            {counts.dinner > 0 && <span className="block rounded bg-violet-50 px-1.5 py-0.5 text-violet-800">D {counts.dinner}</span>}
                                        </span>
                                    </>
                                )}
                            </Link>
                        );
                    })}
                </div>
            </Card>
        </>
    );
}
