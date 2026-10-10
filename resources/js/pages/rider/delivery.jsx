import { Head, usePage } from '@inertiajs/react';
import { Clock, Map, MapPin, Phone, UtensilsCrossed } from 'lucide-react';
import MealTypeBadges from '@/components/clients/meal-type-badges';
import RiderStepButton from '@/components/rider/rider-step-button';
import Alert from '@/components/ui/alert';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import PageHeader from '@/components/ui/page-header';
import StatusBadge from '@/components/ui/status-badge';
import { formatDate, formatDateTime, formatNumber, formatTime } from '@/lib/format';
import { cn } from '@/lib/utils';

const STEPS = [
    { value: 'assigned', label: 'Assigned' },
    { value: 'picked_up', label: 'Picked up' },
    { value: 'on_the_way', label: 'On the way' },
    { value: 'delivered', label: 'Delivered' },
];

/**
 * One delivery: address, contact, what to carry and the next step, with the
 * step button kept within thumb reach.
 */
export default function RiderDelivery({ delivery }) {
    const { errors = {} } = usePage().props;
    const current = STEPS.findIndex((step) => step.value === delivery.status);
    const mapsUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(delivery.delivery_address)}`;

    return (
        <>
            <Head title={delivery.client} />

            <PageHeader
                title={delivery.client}
                description={
                    <span className="inline-flex flex-wrap items-center gap-2">
                        <StatusBadge status={delivery.status} label={delivery.status_label} />
                        <span>
                            {delivery.order_number} · {formatDate(delivery.order_date)}
                        </span>
                    </span>
                }
                back={{ href: route('rider.deliveries.index'), label: "Today's deliveries" }}
            />

            {errors.status && (
                <Alert tone="error" className="mb-4">
                    {errors.status}
                </Alert>
            )}

            {delivery.status === 'cancelled' ? (
                <Alert tone="error" className="mb-4">
                    This delivery was cancelled. You do not need to take it.
                </Alert>
            ) : (
                <ol className="mb-4 grid grid-cols-4 gap-2" aria-label="Delivery progress">
                    {STEPS.map((step, index) => (
                        <li key={step.value} className="flex flex-col items-center gap-1.5 text-center">
                            <span className={cn('h-1.5 w-full rounded-full', index <= current ? 'bg-primary-600' : 'bg-slate-200')} aria-hidden="true" />
                            <span className={cn('text-xs', index === current ? 'font-semibold text-slate-900' : 'text-slate-500')}>
                                {step.label}
                                {index === current && <span className="sr-only"> (current)</span>}
                            </span>
                        </li>
                    ))}
                </ol>
            )}

            <div className="grid gap-4 lg:grid-cols-2">
                <Card>
                    <CardBody className="space-y-4">
                        <div className="flex flex-wrap items-center gap-2">
                            <MealTypeBadges types={[delivery.meal_type]} />
                            <span className="text-2xl font-bold text-slate-900 tabular-nums">{formatNumber(delivery.quantity)}</span>
                            <span className="text-sm text-slate-500">meals</span>
                        </div>
                        <p className="flex gap-2 text-base text-slate-800">
                            <MapPin className="mt-1 size-5 shrink-0 text-slate-400" aria-hidden="true" />
                            {delivery.delivery_address}
                        </p>
                        {delivery.delivery_time && (
                            <p className="flex gap-2 text-sm text-slate-700">
                                <Clock className="mt-0.5 size-4 shrink-0 text-slate-400" aria-hidden="true" />
                                Deliver by {formatTime(delivery.delivery_time)}
                            </p>
                        )}
                        <div className="grid grid-cols-2 gap-2">
                            <Button as="a" href={mapsUrl} target="_blank" rel="noopener noreferrer" variant="secondary" icon={Map}>
                                Open map
                            </Button>
                            <Button as="a" href={`tel:${delivery.contact_phone}`} variant="secondary" icon={Phone}>
                                Call
                            </Button>
                        </div>
                        <p className="text-sm text-slate-600">
                            Contact: <span className="font-medium text-slate-900">{delivery.contact_person}</span> · {delivery.contact_phone}
                        </p>
                        {delivery.special_instructions && <Alert tone="info">{delivery.special_instructions}</Alert>}
                        {delivery.notes && <Alert tone="info">Note from the kitchen: {delivery.notes}</Alert>}
                    </CardBody>
                </Card>

                <Card>
                    <CardHeader title="What to carry" />
                    {delivery.items.length === 0 ? (
                        <CardBody>
                            <p className="flex items-center gap-2 text-sm text-slate-500">
                                <UtensilsCrossed className="size-4" aria-hidden="true" />
                                {formatNumber(delivery.quantity)} {delivery.meal_type_label.toLowerCase()} boxes
                            </p>
                        </CardBody>
                    ) : (
                        <ul className="divide-y divide-slate-100">
                            {delivery.items.map((item) => (
                                <li key={item.id} className="flex justify-between gap-3 px-4 py-3 text-sm sm:px-6">
                                    <span className="font-medium text-slate-900">{item.name}</span>
                                    <span className="text-slate-500 tabular-nums">× {formatNumber(delivery.quantity)}</span>
                                </li>
                            ))}
                        </ul>
                    )}
                    {delivery.delivered_at && (
                        <p className="border-t border-slate-100 px-4 py-3 text-sm text-emerald-700 sm:px-6">
                            Delivered {formatDateTime(delivery.delivered_at)}
                        </p>
                    )}
                </Card>
            </div>

            {delivery.next_step && (
                <div className="sticky bottom-[calc(3.75rem+env(safe-area-inset-bottom))] z-20 -mx-4 mt-6 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur sm:mx-0 sm:rounded-xl sm:border sm:shadow-sm lg:bottom-4">
                    <RiderStepButton delivery={delivery} className="w-full" />
                </div>
            )}
        </>
    );
}
