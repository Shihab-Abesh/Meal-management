import { Head } from '@inertiajs/react';
import { BarChart3, Building2, FileDown, FileSpreadsheet } from 'lucide-react';
import BarList from '@/components/charts/bar-list';
import ColumnChart from '@/components/charts/column-chart';
import ReportTable, { useReportFormat } from '@/components/reports/report-table';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import DateInput from '@/components/ui/date-input';
import DateNavigator, { shiftMonth } from '@/components/ui/date-navigator';
import EmptyState from '@/components/ui/empty-state';
import PageHeader from '@/components/ui/page-header';
import Select from '@/components/ui/select';
import { useApp } from '@/hooks/use-app';
import { useFilters } from '@/hooks/use-filters';
import { formatDate } from '@/lib/format';
import { cn } from '@/lib/utils';

const MEAL_SERIES = [
    { key: 'lunch', label: 'Lunch', color: 'var(--color-series-1)' },
    { key: 'dinner', label: 'Dinner', color: 'var(--color-series-2)' },
];

const RANGE_TYPES = ['client', 'employees', 'menu', 'riders'];

function monthBounds(month) {
    const [year, number] = month.split('-').map(Number);
    const last = new Date(year, number, 0).getDate();

    return { from: `${month}-01`, to: `${month}-${String(last).padStart(2, '0')}` };
}

/**
 * One-click reports: choose the report and its date, month or range; the
 * figures, charts and table update straight away and can be downloaded.
 */
export default function Reports({ report, params, types, clients, today }) {
    const { money } = useApp();
    const format = useReportFormat();
    const { values, set, setMany, loading } = useFilters(route('reports.index'), params);
    const isVendor = clients !== null;
    const thisMonth = today.slice(0, 7);
    const query = Object.fromEntries(Object.entries(values).filter(([, value]) => value));

    return (
        <>
            <Head title="Reports" />

            <PageHeader
                title="Reports"
                description={report ? `${report.title} · ${report.subtitle}` : 'Choose a report.'}
                actions={
                    report && (
                        <div className="flex w-full gap-2 sm:w-auto">
                            <Button as="a" href={route('reports.export', { ...query, format: 'csv' })} variant="secondary" icon={FileSpreadsheet} className="flex-1 sm:flex-none">
                                Excel (CSV)
                            </Button>
                            <Button as="a" href={route('reports.export', { ...query, format: 'pdf' })} variant="secondary" icon={FileDown} className="flex-1 sm:flex-none">
                                PDF
                            </Button>
                        </div>
                    )
                }
            />

            <div className="mb-4 flex flex-wrap gap-2" role="tablist" aria-label="Report">
                {types.map((type) => (
                    <button
                        key={type.value}
                        type="button"
                        role="tab"
                        aria-selected={values.type === type.value}
                        onClick={() => set('type', type.value)}
                        className={cn(
                            'inline-flex min-h-10 items-center rounded-full px-4 text-sm font-medium ring-1 ring-inset',
                            values.type === type.value ? 'bg-primary-600 text-white ring-primary-600' : 'bg-white text-slate-700 ring-slate-300 hover:bg-slate-50',
                        )}
                    >
                        {type.label}
                    </button>
                ))}
            </div>

            <div className="mb-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
                {values.type === 'daily' && <DateNavigator value={values.date} today={today} onChange={(date) => set('date', date)} />}
                {values.type === 'monthly' && <DateNavigator mode="month" label="Month" value={values.month} today={today} onChange={(month) => set('month', month)} />}
                {RANGE_TYPES.includes(values.type) && (
                    <>
                        {isVendor && ['client', 'employees'].includes(values.type) && (
                            <Select
                                aria-label="Client"
                                placeholder={values.type === 'client' ? 'Choose a client' : 'All clients'}
                                options={clients}
                                value={values.client}
                                onChange={(event) => set('client', event.target.value)}
                                className="sm:w-60"
                            />
                        )}
                        <div className="grid grid-cols-2 gap-2 sm:flex">
                            <DateInput aria-label="From" value={values.from} max={values.to} onChange={(event) => event.target.value && set('from', event.target.value)} className="sm:w-40" />
                            <DateInput aria-label="To" value={values.to} min={values.from} onChange={(event) => event.target.value && set('to', event.target.value)} className="sm:w-40" />
                        </div>
                        <div className="flex gap-2">
                            <Button variant="secondary" size="sm" onClick={() => setMany({ from: `${thisMonth}-01`, to: today })}>
                                This month
                            </Button>
                            <Button variant="secondary" size="sm" onClick={() => setMany(monthBounds(shiftMonth(thisMonth, -1)))}>
                                Last month
                            </Button>
                        </div>
                    </>
                )}
            </div>

            <div className={cn('space-y-6 transition-opacity', loading && 'opacity-60')} aria-busy={loading || undefined}>
                {!report ? (
                    <Card>
                        <EmptyState icon={Building2} title="Choose a client" description="The client report covers one client. Pick one above." />
                    </Card>
                ) : (
                    <>
                        <section
                            aria-label="Key figures"
                            className={cn('grid grid-cols-2 gap-3 sm:gap-4', report.summary.length % 3 === 0 ? 'lg:grid-cols-3' : 'lg:grid-cols-4')}
                        >
                            {report.summary.map((figure) => (
                                <Card key={figure.key} className="p-4 sm:p-5">
                                    <p className="text-sm font-medium text-slate-500">{figure.label}</p>
                                    <p className="mt-1 truncate text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">{format(figure.value, figure.format)}</p>
                                    {figure.hint && <p className="mt-1 text-xs text-slate-500">{figure.hint}</p>}
                                </Card>
                            ))}
                        </section>

                        <Charts report={report} money={money} />

                        <Card>
                            <CardHeader title={report.title} description={report.subtitle} />
                            {report.rows.length === 0 ? (
                                <EmptyState icon={BarChart3} title="No data for this period" description="Try another date or a wider range." />
                            ) : (
                                <ReportTable columns={report.columns} rows={report.rows} totals={report.totals} caption={`${report.title}, ${report.subtitle}`} />
                            )}
                        </Card>
                    </>
                )}
            </div>
        </>
    );
}

function Charts({ report, money }) {
    const { charts } = report;
    const daily = charts.daily?.map((day) => ({ ...day, title: formatDate(day.date) }));
    const hasDailyMeals = daily?.some((day) => day.lunch + day.dinner > 0);

    if (report.type === 'monthly') {
        const months = charts.months.map((month) => ({
            ...month,
            title: new Date(`${month.month}-01T00:00:00`).toLocaleDateString('en-GB', { month: 'long', year: 'numeric' }),
        }));

        return (
            <div className="grid gap-6 lg:grid-cols-2">
                <Card className="min-w-0">
                    <CardHeader title="Daily meals" description="Lunch and dinner each day of the month" />
                    <CardBody>
                        <ColumnChart data={daily} series={MEAL_SERIES} label={`Meals per day, ${report.subtitle}`} />
                    </CardBody>
                </Card>
                <Card className="min-w-0">
                    <CardHeader title="Monthly trend" description="Meals per month, last 12 months" />
                    <CardBody>
                        <ColumnChart
                            data={months}
                            series={[{ key: 'meals', label: 'Meals', color: 'var(--color-series-1)' }]}
                            label="Meals per month, last 12 months"
                            labelIndexes={[months.length - 1]}
                        />
                    </CardBody>
                </Card>
                {charts.clients.length > 0 && (
                    <Card className="min-w-0 lg:col-span-2">
                        <CardHeader
                            title={charts.clientsFormat === 'currency' ? 'Revenue by client' : 'Meals by client'}
                            description="Share of the month's total"
                        />
                        <CardBody>
                            <BarList
                                items={charts.clients}
                                formatValue={charts.clientsFormat === 'currency' ? money : undefined}
                                label={charts.clientsFormat === 'currency' ? 'Revenue by client' : 'Meals by client'}
                            />
                        </CardBody>
                    </Card>
                )}
            </div>
        );
    }

    if (report.type === 'client' && hasDailyMeals) {
        return (
            <Card className="min-w-0">
                <CardHeader title="Daily meals" description={report.subtitle} />
                <CardBody>
                    <ColumnChart data={daily} series={MEAL_SERIES} label={`Meals per day, ${report.subtitle}`} />
                </CardBody>
            </Card>
        );
    }

    return null;
}
