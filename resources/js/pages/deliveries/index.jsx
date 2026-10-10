import { Head, Link, usePage } from '@inertiajs/react';
import { Bike, Clock, MapPin, Truck } from 'lucide-react';
import { useState } from 'react';
import MealTypeBadges from '@/components/clients/meal-type-badges';
import AssignRiderDialog from '@/components/orders/assign-rider-dialog';
import OrderStatusActions from '@/components/orders/order-status-actions';
import Alert from '@/components/ui/alert';
import Button from '@/components/ui/button';
import Card from '@/components/ui/card';
import DateNavigator from '@/components/ui/date-navigator';
import EmptyState from '@/components/ui/empty-state';
import PageHeader from '@/components/ui/page-header';
import StatusBadge from '@/components/ui/status-badge';
import { useAuth } from '@/hooks/use-auth';
import { useFilters } from '@/hooks/use-filters';
import { formatLongDate, formatNumber, formatTime } from '@/lib/format';
import { cn } from '@/lib/utils';

const TABS = [
    { value: 'to_assign', label: 'To assign' },
    { value: 'assigned', label: 'Assigned' },
    { value: 'on_the_way', label: 'On the way' },
    { value: 'delivered', label: 'Delivered' },
    { value: 'all', label: 'All' },
];

export default function DeliveriesIndex({ date, today, tab, counts, orders, riders }) {
    const { can } = useAuth();
    const { errors = {} } = usePage().props;
    const { values, set, setMany, loading } = useFilters(route('deliveries.index'), { date, tab });
    const [assigning, setAssigning] = useState(null);
    const [assignOpen, setAssignOpen] = useState(false);

    return (
        <>
            <Head title="Deliveries" />

            <PageHeader title="Deliveries" description={formatLongDate(values.date)} />

            <div className="mb-4">
                <DateNavigator value={values.date} today={today} onChange={(value) => setMany({ date: value })} />
            </div>

            <div className="mb-4 flex flex-wrap gap-2" role="tablist" aria-label="Delivery status">
                {TABS.map((item) => (
                    <button
                        key={item.value}
                        type="button"
                        role="tab"
                        aria-selected={values.tab === item.value}
                        onClick={() => set('tab', item.value)}
                        className={cn(
                            'inline-flex min-h-10 shrink-0 items-center gap-2 rounded-full px-3.5 text-sm font-medium ring-1 ring-inset',
                            values.tab === item.value ? 'bg-primary-600 text-white ring-primary-600' : 'bg-white text-slate-700 ring-slate-300 hover:bg-slate-50',
                        )}
                    >
                        {item.label}
                        <span className="tabular-nums opacity-80">{counts[item.value] ?? 0}</span>
                    </button>
                ))}
            </div>

            {(errors.status || errors.rider_id) && (
                <Alert tone="error" className="mb-4">
                    {errors.status ?? errors.rider_id}
                </Alert>
            )}

            <div className={cn('transition-opacity', loading && 'opacity-60')} aria-busy={loading || undefined}>
                {orders.length === 0 ? (
                    <Card>
                        <EmptyState
                            icon={Truck}
                            title={counts.all === 0 ? 'No orders for this day' : 'Nothing in this list'}
                            description={counts.all === 0 ? 'Confirmed meals become orders that show up here.' : 'Try another tab.'}
                        />
                    </Card>
                ) : (
                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        {orders.map((order) => (
                            <Card key={order.id} className="flex flex-col">
                                <div className="flex items-start justify-between gap-2 px-4 pt-4">
                                    <div className="min-w-0">
                                        <Link href={route('orders.show', order.id)} className="block truncate font-semibold text-slate-900 hover:text-primary-700">
                                            {order.client?.name}
                                        </Link>
                                        <p className="mt-0.5 flex flex-wrap items-center gap-2 text-sm text-slate-600">
                                            <MealTypeBadges types={[order.meal_type]} />
                                            <span className="font-semibold tabular-nums">{formatNumber(order.quantity)} meals</span>
                                        </p>
                                    </div>
                                    <StatusBadge status={order.status} label={order.status_label} />
                                </div>
                                <div className="space-y-1.5 px-4 py-3 text-sm text-slate-600">
                                    <p className="flex gap-2">
                                        <MapPin className="mt-0.5 size-4 shrink-0 text-slate-400" aria-hidden="true" />
                                        <span className="line-clamp-2">{order.delivery_address}</span>
                                    </p>
                                    {order.delivery_time && (
                                        <p className="flex gap-2">
                                            <Clock className="mt-0.5 size-4 shrink-0 text-slate-400" aria-hidden="true" />
                                            By {formatTime(order.delivery_time)}
                                        </p>
                                    )}
                                    <p className="flex gap-2">
                                        <Bike className="mt-0.5 size-4 shrink-0 text-slate-400" aria-hidden="true" />
                                        {order.rider ? <span className="font-medium text-slate-900">{order.rider.name}</span> : <span className="text-amber-700">No rider yet</span>}
                                    </p>
                                </div>
                                <div className="mt-auto flex flex-wrap gap-2 border-t border-slate-100 px-4 py-3">
                                    {order.can_assign && (
                                        <Button
                                            size="sm"
                                            variant={order.rider ? 'secondary' : 'primary'}
                                            icon={Bike}
                                            onClick={() => {
                                                setAssigning(order);
                                                setAssignOpen(true);
                                            }}
                                        >
                                            {order.rider ? 'Change rider' : 'Assign rider'}
                                        </Button>
                                    )}
                                    <OrderStatusActions
                                        order={order}
                                        canManageOrders={can('orders.manage')}
                                        canManageDeliveries
                                        size="sm"
                                        className="contents"
                                    />
                                </div>
                            </Card>
                        ))}
                    </div>
                )}
            </div>

            {assigning && (
                <AssignRiderDialog
                    key={`${assigning.id}-${assigning.rider?.id ?? 'none'}`}
                    open={assignOpen}
                    onClose={() => setAssignOpen(false)}
                    order={assigning}
                    riders={riders}
                />
            )}
        </>
    );
}
