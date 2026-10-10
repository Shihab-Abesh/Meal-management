import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Bike, Clock, MapPin, Pencil, Phone, UserMinus } from 'lucide-react';
import { useState } from 'react';
import AssignRiderDialog from '@/components/orders/assign-rider-dialog';
import OrderStatusActions from '@/components/orders/order-status-actions';
import Alert from '@/components/ui/alert';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import DescriptionList from '@/components/ui/description-list';
import EmptyState from '@/components/ui/empty-state';
import FormField from '@/components/ui/form-field';
import Input from '@/components/ui/input';
import Modal from '@/components/ui/modal';
import PageHeader from '@/components/ui/page-header';
import StatusBadge from '@/components/ui/status-badge';
import Textarea from '@/components/ui/textarea';
import { useApp } from '@/hooks/use-app';
import { formatDate, formatDateTime, formatNumber, formatTime } from '@/lib/format';
import { cn } from '@/lib/utils';

const LIFECYCLE = ['pending', 'confirmed', 'preparing', 'ready', 'assigned', 'picked_up', 'on_the_way', 'delivered'];
const STEP_LABELS = {
    pending: 'Pending',
    confirmed: 'Confirmed',
    preparing: 'Preparing',
    ready: 'Ready',
    assigned: 'Assigned',
    picked_up: 'Picked up',
    on_the_way: 'On the way',
    delivered: 'Delivered',
};

export default function ShowOrder({ order, timeline, riders, can }) {
    const { money } = useApp();
    const { errors = {} } = usePage().props;
    const [assignOpen, setAssignOpen] = useState(false);
    const [detailsOpen, setDetailsOpen] = useState(false);
    const [unassigning, setUnassigning] = useState(false);
    const currentStep = LIFECYCLE.indexOf(order.status);

    function unassign() {
        router.delete(route('orders.unassign', order.id), {
            preserveScroll: true,
            onStart: () => setUnassigning(true),
            onFinish: () => setUnassigning(false),
        });
    }

    return (
        <>
            <Head title={`Order ${order.order_number}`} />

            <PageHeader
                title={order.client?.name}
                description={
                    <span className="inline-flex flex-wrap items-center gap-2">
                        <StatusBadge status={order.status} label={order.status_label} />
                        <span>
                            {order.order_number} · {order.meal_type_label} · {formatDate(order.order_date)}
                        </span>
                    </span>
                }
                back={{ href: route('orders.index', { date: order.order_date }), label: 'Orders' }}
            />

            {(errors.status || errors.rider_id) && (
                <Alert tone="error" className="mb-6">
                    {errors.status ?? errors.rider_id}
                </Alert>
            )}

            {order.status === 'cancelled' && (
                <Alert tone="error" className="mb-6">
                    This order was cancelled{order.cancel_reason ? `: ${order.cancel_reason}` : '.'}
                </Alert>
            )}

            {order.status !== 'cancelled' && (
                <Card className="mb-6">
                    <CardBody>
                        <ol className="grid grid-cols-4 gap-2 sm:grid-cols-8" aria-label="Order progress">
                            {LIFECYCLE.map((step, index) => (
                                <li key={step} className="flex flex-col items-center gap-1.5 text-center">
                                    <span
                                        className={cn(
                                            'h-1.5 w-full rounded-full',
                                            index <= currentStep ? 'bg-primary-600' : 'bg-slate-200',
                                        )}
                                        aria-hidden="true"
                                    />
                                    <span className={cn('text-[11px] leading-tight sm:text-xs', index === currentStep ? 'font-semibold text-slate-900' : 'text-slate-500')}>
                                        {STEP_LABELS[step]}
                                        {index === currentStep && <span className="sr-only"> (current)</span>}
                                    </span>
                                </li>
                            ))}
                        </ol>
                        <OrderStatusActions
                            order={order}
                            canManageOrders={can.manageOrders}
                            canManageDeliveries={can.manageDeliveries}
                            className="mt-5 flex flex-wrap gap-2"
                        />
                    </CardBody>
                </Card>
            )}

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader title="Order" />
                        <CardBody>
                            <DescriptionList
                                items={[
                                    { label: 'Client', value: order.client?.name },
                                    { label: 'Date', value: formatDate(order.order_date) },
                                    { label: 'Meal', value: order.meal_type_label },
                                    { label: 'Quantity', value: `${formatNumber(order.quantity)} meals` },
                                    order.amount !== null && { label: 'Price per meal', value: money(order.unit_price) },
                                    order.amount !== null && { label: 'Amount', value: money(order.amount) },
                                    { label: 'Notes', value: order.notes, wide: true },
                                ].filter(Boolean)}
                            />
                        </CardBody>
                    </Card>

                    <Card>
                        <CardHeader title="Menu items" description="Each meal includes these items." />
                        {order.items.length === 0 ? (
                            <EmptyState title="No menu for this day" description="Add a daily menu and the items appear here." />
                        ) : (
                            <ul className="divide-y divide-slate-100">
                                {order.items.map((item) => (
                                    <li key={item.id} className="flex justify-between gap-3 px-4 py-3 text-sm sm:px-6">
                                        <span className="font-medium text-slate-900">{item.name}</span>
                                        <span className="text-slate-500 tabular-nums">× {formatNumber(item.quantity)}</span>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </Card>
                </div>

                <div className="space-y-6">
                    <Card>
                        <CardHeader
                            title="Delivery"
                            actions={
                                can.manageOrders &&
                                order.status !== 'cancelled' &&
                                order.status !== 'delivered' && (
                                    <Button variant="ghost" size="sm" icon={Pencil} onClick={() => setDetailsOpen(true)}>
                                        Edit
                                    </Button>
                                )
                            }
                        />
                        <CardBody className="space-y-4 text-sm">
                            <p className="flex gap-2 text-slate-700">
                                <MapPin className="mt-0.5 size-4 shrink-0 text-slate-400" aria-hidden="true" />
                                {order.delivery_address}
                            </p>
                            {order.delivery_time && (
                                <p className="flex gap-2 text-slate-700">
                                    <Clock className="mt-0.5 size-4 shrink-0 text-slate-400" aria-hidden="true" />
                                    Deliver by {formatTime(order.delivery_time)}
                                </p>
                            )}
                            <p className="flex gap-2 text-slate-700">
                                <Phone className="mt-0.5 size-4 shrink-0 text-slate-400" aria-hidden="true" />
                                {order.client_contact.contact_person} ·{' '}
                                <a href={`tel:${order.client_contact.phone}`} className="font-medium text-primary-700">
                                    {order.client_contact.phone}
                                </a>
                            </p>
                            {order.client_contact.special_instructions && (
                                <Alert tone="info">{order.client_contact.special_instructions}</Alert>
                            )}

                            <div className="border-t border-slate-100 pt-4">
                                <p className="text-xs font-medium tracking-wide text-slate-500 uppercase">Rider</p>
                                {order.rider ? (
                                    <div className="mt-1 flex items-center justify-between gap-2">
                                        <div>
                                            <p className="font-medium text-slate-900">{order.rider.name}</p>
                                            <a href={`tel:${order.rider.phone}`} className="text-primary-700">
                                                {order.rider.phone}
                                            </a>
                                        </div>
                                        {can.manageDeliveries && order.status === 'assigned' && (
                                            <Button variant="ghost" size="sm" icon={UserMinus} loading={unassigning} onClick={unassign}>
                                                Unassign
                                            </Button>
                                        )}
                                    </div>
                                ) : (
                                    <p className="mt-1 text-slate-500">Not assigned yet</p>
                                )}
                                {riders && order.can_assign && (
                                    <Button variant="secondary" icon={Bike} className="mt-3 w-full" onClick={() => setAssignOpen(true)}>
                                        {order.rider ? 'Change rider' : 'Assign rider'}
                                    </Button>
                                )}
                            </div>

                            {order.delivery && (
                                <dl className="space-y-1 border-t border-slate-100 pt-4 text-xs text-slate-500">
                                    {[
                                        ['Assigned', order.delivery.assigned_at],
                                        ['Picked up', order.delivery.picked_up_at],
                                        ['On the way', order.delivery.on_the_way_at],
                                        ['Delivered', order.delivery.delivered_at],
                                    ]
                                        .filter(([, value]) => value)
                                        .map(([label, value]) => (
                                            <div key={label} className="flex justify-between gap-2">
                                                <dt>{label}</dt>
                                                <dd className="text-slate-700">{formatDateTime(value)}</dd>
                                            </div>
                                        ))}
                                </dl>
                            )}
                        </CardBody>
                    </Card>

                    <Card>
                        <CardHeader title="History" />
                        <ol className="divide-y divide-slate-100">
                            {timeline.map((entry) => (
                                <li key={entry.id} className="px-4 py-3 sm:px-6">
                                    <p className="text-sm text-slate-800">{entry.description}</p>
                                    <p className="mt-0.5 text-xs text-slate-500">
                                        {entry.user ?? 'System'} · {formatDateTime(entry.created_at)}
                                    </p>
                                </li>
                            ))}
                        </ol>
                    </Card>

                    <Link href={route('clients.show', order.client.id)} className="block text-center text-sm font-medium text-primary-700">
                        View client
                    </Link>
                </div>
            </div>

            {riders && (
                <AssignRiderDialog key={`${order.id}-${order.rider?.id ?? 'none'}`} open={assignOpen} onClose={() => setAssignOpen(false)} order={order} riders={riders} />
            )}
            <DeliveryDetailsDialog key={order.delivery_address + order.delivery_time} open={detailsOpen} onClose={() => setDetailsOpen(false)} order={order} />
        </>
    );
}

function DeliveryDetailsDialog({ open, onClose, order }) {
    const { data, setData, put, processing, errors } = useForm({
        delivery_address: order.delivery_address ?? '',
        delivery_time: order.delivery_time ?? '',
        notes: order.notes ?? '',
    });
    const formId = 'order-details-form';

    return (
        <Modal
            open={open}
            onClose={onClose}
            title="Delivery details"
            footer={
                <>
                    <Button variant="secondary" onClick={onClose} className="w-full sm:w-auto">
                        Cancel
                    </Button>
                    <Button type="submit" form={formId} loading={processing} className="w-full sm:w-auto">
                        Save
                    </Button>
                </>
            }
        >
            <form
                id={formId}
                noValidate
                className="space-y-5"
                onSubmit={(event) => {
                    event.preventDefault();
                    put(route('orders.update', order.id), { preserveScroll: true, onSuccess: onClose });
                }}
            >
                <FormField name="delivery_address" label="Delivery address" error={errors.delivery_address} required>
                    <Textarea rows={2} value={data.delivery_address} onChange={(event) => setData('delivery_address', event.target.value)} />
                </FormField>
                <FormField name="delivery_time_edit" label="Deliver by" error={errors.delivery_time}>
                    <Input type="time" value={data.delivery_time} onChange={(event) => setData('delivery_time', event.target.value)} />
                </FormField>
                <FormField name="order_notes" label="Notes" error={errors.notes}>
                    <Textarea rows={3} value={data.notes} onChange={(event) => setData('notes', event.target.value)} />
                </FormField>
            </form>
        </Modal>
    );
}
