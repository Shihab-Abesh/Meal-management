import { Head } from '@inertiajs/react';
import { History } from 'lucide-react';
import DeliveryCard from '@/components/rider/delivery-card';
import Card from '@/components/ui/card';
import EmptyState from '@/components/ui/empty-state';
import PageHeader from '@/components/ui/page-header';
import Pagination from '@/components/ui/pagination';
import { useFilters } from '@/hooks/use-filters';
import { cn } from '@/lib/utils';

const TABS = [
    { value: '', label: 'All' },
    { value: 'delivered', label: 'Delivered' },
    { value: 'cancelled', label: 'Cancelled' },
];

export default function RiderHistory({ deliveries, filters }) {
    const { values, set, loading } = useFilters(route('rider.deliveries.history'), filters);

    return (
        <>
            <Head title="Delivery history" />

            <PageHeader title="Delivery history" description="Deliveries you have finished." />

            <div className="mb-4 flex gap-2" role="group" aria-label="Filter by result">
                {TABS.map((tab) => (
                    <button
                        key={tab.value || 'all'}
                        type="button"
                        aria-pressed={values.status === tab.value}
                        onClick={() => set('status', tab.value)}
                        className={cn(
                            'inline-flex min-h-10 items-center rounded-full px-4 text-sm font-medium ring-1 ring-inset',
                            values.status === tab.value ? 'bg-primary-600 text-white ring-primary-600' : 'bg-white text-slate-700 ring-slate-300 hover:bg-slate-50',
                        )}
                    >
                        {tab.label}
                    </button>
                ))}
            </div>

            <div className={cn('transition-opacity', loading && 'opacity-60')} aria-busy={loading || undefined}>
                {deliveries.data.length === 0 ? (
                    <Card>
                        <EmptyState icon={History} title="No finished deliveries yet" description="Deliveries you complete will be listed here." />
                    </Card>
                ) : (
                    <>
                        <ul className="grid gap-3 md:grid-cols-2">
                            {deliveries.data.map((delivery) => (
                                <DeliveryCard key={delivery.id} delivery={delivery} showAction={false} />
                            ))}
                        </ul>
                        <Card className="mt-4">
                            <Pagination meta={deliveries.meta} links={deliveries.links} className="border-t-0" />
                        </Card>
                    </>
                )}
            </div>
        </>
    );
}
