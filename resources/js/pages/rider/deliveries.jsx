import { Head } from '@inertiajs/react';
import { Truck } from 'lucide-react';
import DeliveryCard from '@/components/rider/delivery-card';
import Card from '@/components/ui/card';
import EmptyState from '@/components/ui/empty-state';
import PageHeader from '@/components/ui/page-header';
import { formatLongDate } from '@/lib/format';

/**
 * The rider's home screen: today's deliveries, open ones first.
 */
export default function RiderDeliveries({ deliveries, today, riderName }) {
    const open = deliveries.filter((delivery) => delivery.next_step);
    const done = deliveries.filter((delivery) => !delivery.next_step);

    return (
        <>
            <Head title="Today's deliveries" />

            <PageHeader title="Today's deliveries" description={`${riderName} · ${formatLongDate(today)}`} />

            {deliveries.length === 0 ? (
                <Card>
                    <EmptyState
                        icon={Truck}
                        title="No deliveries assigned"
                        description="When the kitchen assigns a delivery to you it shows up here. Pull down or reopen the page to refresh."
                    />
                </Card>
            ) : (
                <div className="space-y-6">
                    {open.length > 0 && (
                        <section aria-labelledby="to-deliver">
                            <h2 id="to-deliver" className="mb-3 text-sm font-semibold text-slate-500">
                                To deliver ({open.length})
                            </h2>
                            <ul className="grid gap-3 md:grid-cols-2">
                                {open.map((delivery) => (
                                    <DeliveryCard key={delivery.id} delivery={delivery} today={today} />
                                ))}
                            </ul>
                        </section>
                    )}
                    {done.length > 0 && (
                        <section aria-labelledby="finished">
                            <h2 id="finished" className="mb-3 text-sm font-semibold text-slate-500">
                                Finished today ({done.length})
                            </h2>
                            <ul className="grid gap-3 md:grid-cols-2">
                                {done.map((delivery) => (
                                    <DeliveryCard key={delivery.id} delivery={delivery} today={today} showAction={false} />
                                ))}
                            </ul>
                        </section>
                    )}
                </div>
            )}
        </>
    );
}
