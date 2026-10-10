import { Link } from '@inertiajs/react';
import { ChevronRight, Clock, MapPin } from 'lucide-react';
import MealTypeBadges from '@/components/clients/meal-type-badges';
import RiderStepButton from '@/components/rider/rider-step-button';
import Card from '@/components/ui/card';
import StatusBadge from '@/components/ui/status-badge';
import { formatDate, formatNumber, formatTime } from '@/lib/format';

/**
 * One delivery in the rider app: where, what, how many, and the next step.
 */
export default function DeliveryCard({ delivery, today, showAction = true }) {
    return (
        <Card as="li" className="overflow-hidden">
            <Link href={route('rider.deliveries.show', delivery.id)} className="flex items-start gap-3 px-4 pt-4 pb-3 active:bg-slate-50">
                <div className="min-w-0 flex-1 space-y-2">
                    <div className="flex items-start justify-between gap-2">
                        <p className="min-w-0 truncate text-base font-semibold text-slate-900">{delivery.client}</p>
                        <StatusBadge status={delivery.status} label={delivery.status_label} />
                    </div>
                    <p className="flex flex-wrap items-center gap-2 text-sm text-slate-700">
                        <MealTypeBadges types={[delivery.meal_type]} />
                        <span className="font-semibold tabular-nums">{formatNumber(delivery.quantity)} meals</span>
                        {delivery.order_date !== today && (
                            // An open delivery from an earlier day stands out; in history every date is shown plainly.
                            <span className={today ? 'text-amber-700' : 'text-slate-500'}>{formatDate(delivery.order_date)}</span>
                        )}
                    </p>
                    <p className="flex gap-2 text-sm text-slate-600">
                        <MapPin className="mt-0.5 size-4 shrink-0 text-slate-400" aria-hidden="true" />
                        <span className="line-clamp-2">{delivery.delivery_address}</span>
                    </p>
                    {delivery.delivery_time && (
                        <p className="flex gap-2 text-sm text-slate-600">
                            <Clock className="mt-0.5 size-4 shrink-0 text-slate-400" aria-hidden="true" />
                            Deliver by {formatTime(delivery.delivery_time)}
                        </p>
                    )}
                </div>
                <ChevronRight className="mt-1 size-5 shrink-0 text-slate-300" aria-hidden="true" />
            </Link>
            {showAction && delivery.next_step && (
                <div className="border-t border-slate-100 p-3">
                    <RiderStepButton delivery={delivery} className="w-full" />
                </div>
            )}
        </Card>
    );
}
