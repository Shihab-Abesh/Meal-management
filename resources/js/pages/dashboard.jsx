import { Head, Link } from '@inertiajs/react';
import { ArrowRight, ClipboardCheck, Truck } from 'lucide-react';
import ColumnChart from '@/components/charts/column-chart';
import MealTypeBadges from '@/components/clients/meal-type-badges';
import StatCard from '@/components/dashboard/stat-card';
import Avatar from '@/components/ui/avatar';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import DataTable from '@/components/ui/data-table';
import EmptyState from '@/components/ui/empty-state';
import PageHeader from '@/components/ui/page-header';
import StatusBadge from '@/components/ui/status-badge';
import { useApp } from '@/hooks/use-app';
import { useAuth } from '@/hooks/use-auth';
import { formatDate, formatLongDate, formatNumber, formatTime } from '@/lib/format';
import { cn } from '@/lib/utils';

// Full class names so Tailwind can see them.
const gridColumns = {
    3: 'sm:grid-cols-3',
    4: 'sm:grid-cols-2 xl:grid-cols-4',
    5: 'sm:grid-cols-3 xl:grid-cols-5',
};

const deliveryColumns = [
    { key: 'client', header: 'Client', primary: true },
    { key: 'meal_type', header: 'Meal', render: (order) => <MealTypeBadges types={[order.meal_type]} /> },
    { key: 'quantity', header: 'Qty', align: 'right', className: 'md:w-20', render: (order) => formatNumber(order.quantity) },
    { key: 'rider', header: 'Rider', className: 'hidden xl:table-cell', render: (order) => order.rider ?? <span className="text-slate-400">Not assigned</span> },
    { key: 'status', header: 'Status', render: (order) => <StatusBadge status={order.status} label={order.status_label} /> },
];

const MEAL_SERIES = [
    { key: 'lunch', label: 'Lunch', color: 'var(--color-series-1)' },
    { key: 'dinner', label: 'Dinner', color: 'var(--color-series-2)' },
];

function greeting() {
    const hour = new Date().getHours();

    if (hour < 12) {
        return 'Good morning';
    }

    return hour < 17 ? 'Good afternoon' : 'Good evening';
}

export default function Dashboard({ stats, today, todaysOrders, clientToday, trend }) {
    const { user } = useAuth();
    const firstName = user?.name?.split(' ')[0] ?? '';

    return (
        <>
            <Head title="Dashboard" />

            <PageHeader title={`${greeting()}, ${firstName}`} description={formatLongDate(today)} />

            <section
                aria-label="Today at a glance"
                className={cn('grid grid-cols-2 gap-3 sm:gap-4', gridColumns[stats.length] ?? 'sm:grid-cols-3')}
            >
                {stats.map((stat, index) => (
                    <StatCard
                        key={stat.key}
                        stat={stat}
                        // An odd card out on the 2-column phone grid spans the full width.
                        className={index === stats.length - 1 && stats.length % 2 === 1 ? 'col-span-2 sm:col-span-1' : ''}
                    />
                ))}
            </section>

            <div className="mt-6">
                {user?.is_vendor && <VendorPanels orders={todaysOrders} trend={trend} />}
                {user?.role === 'client_admin' && <ClientPanel meals={clientToday ?? []} />}
                {user?.role === 'rider' && (
                    <Card>
                        <CardHeader title="Today's deliveries" description="Deliveries assigned to you" />
                        <EmptyState
                            icon={Truck}
                            title="Ready to ride?"
                            description="Open your delivery list to see addresses and mark each delivery."
                            action={
                                <Button as={Link} href={route('rider.deliveries.index')} icon={Truck}>
                                    Open today's deliveries
                                </Button>
                            }
                        />
                    </Card>
                )}
            </div>
        </>
    );
}

function ClientPanel({ meals }) {
    return (
        <Card>
            <CardHeader
                title="Today's meals"
                description="Meal counts and delivery progress for your office"
                actions={
                    <Button as={Link} href={route('meals.index')} size="sm" icon={ClipboardCheck}>
                        Open meal sheet
                    </Button>
                }
            />
            <ul className="divide-y divide-slate-100">
                {meals.map((meal) => (
                    <li key={meal.meal_type} className="flex flex-wrap items-center justify-between gap-3 px-4 py-4 sm:px-6">
                        <div className="min-w-0">
                            <p className="flex items-center gap-2">
                                <MealTypeBadges types={[meal.meal_type]} />
                                <span className="text-lg font-bold text-slate-900 tabular-nums">{formatNumber(meal.count)}</span>
                                <span className="text-sm text-slate-500">meals</span>
                            </p>
                            <p className="mt-1 text-xs text-slate-500">
                                {meal.closed ? 'Closed at' : 'Open until'} {formatTime(meal.cutoff)}
                            </p>
                        </div>
                        {meal.order_status ? (
                            <StatusBadge status={meal.order_status} label={meal.order_status_label} />
                        ) : (
                            <span className="text-sm text-slate-400">No order yet</span>
                        )}
                    </li>
                ))}
            </ul>
        </Card>
    );
}

function VendorPanels({ orders, trend }) {
    const { name, logoUrl } = useApp();
    const { can } = useAuth();

    return (
        <div className="grid items-start gap-6 lg:grid-cols-3">
            {orders && (
                <Card className="min-w-0 lg:col-span-2">
                    <CardHeader
                        title="Today's deliveries"
                        description="Orders going out today and their riders"
                        actions={
                            <Link
                                href={can('deliveries.manage') ? route('deliveries.index') : route('orders.index')}
                                className="text-sm font-semibold text-primary-700 hover:text-primary-800"
                            >
                                {can('deliveries.manage') ? 'Delivery board' : 'All orders'}
                            </Link>
                        }
                    />
                    <DataTable
                        caption="Today's deliveries"
                        columns={deliveryColumns}
                        rows={orders}
                        rowHref={(order) => route('orders.show', order.id)}
                        empty={
                            <EmptyState
                                icon={Truck}
                                title="No deliveries yet today"
                                description="Confirmed meal orders will be listed here with their rider and status."
                            />
                        }
                    />
                </Card>
            )}

            <div className={cn('min-w-0 space-y-6', !orders && 'lg:col-span-3')}>
                {trend && (
                    <Card>
                        <CardHeader title="Meals, last 14 days" description="Confirmed lunch and dinner per day" />
                        <CardBody>
                            <ColumnChart
                                data={trend.map((day) => ({ ...day, title: formatDate(day.date) }))}
                                series={MEAL_SERIES}
                                label="Confirmed meals per day, last 14 days"
                                height={140}
                            />
                        </CardBody>
                    </Card>
                )}

            <Card>
                <CardHeader title="Your business" />
                <CardBody>
                    <div className="flex items-center gap-4">
                        <Avatar name={name} src={logoUrl} size="lg" rounded="xl" className="ring-1 ring-slate-200" />
                        <div className="min-w-0">
                            <p className="truncate font-semibold text-slate-900">{name}</p>
                            <p className="text-sm text-slate-500">Vendor profile</p>
                        </div>
                    </div>
                    <Link
                        href={route('vendor-profile.show')}
                        className="mt-5 inline-flex min-h-11 items-center gap-1.5 text-sm font-semibold text-primary-700 hover:text-primary-800"
                    >
                        View business details
                        <ArrowRight className="size-4" aria-hidden="true" />
                    </Link>
                </CardBody>
            </Card>
            </div>
        </div>
    );
}
