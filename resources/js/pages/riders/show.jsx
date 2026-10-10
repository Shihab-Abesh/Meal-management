import { Head, Link } from '@inertiajs/react';
import { KeyRound, Pencil, Phone, Truck } from 'lucide-react';
import StatCard from '@/components/dashboard/stat-card';
import RiderActionMenu from '@/components/riders/rider-action-menu';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import DataTable from '@/components/ui/data-table';
import DescriptionList from '@/components/ui/description-list';
import EmptyState from '@/components/ui/empty-state';
import PageHeader from '@/components/ui/page-header';
import Pagination from '@/components/ui/pagination';
import StatusBadge from '@/components/ui/status-badge';
import { formatDate, formatDateTime, formatNumber } from '@/lib/format';

export default function ShowRider({ rider, stats, deliveries }) {
    const statCards = [
        { key: 'assigned', label: 'Assigned', value: stats.assigned, icon: 'truck', hint: 'All deliveries given' },
        { key: 'completed', label: 'Delivered', value: stats.completed, icon: 'circle-check', hint: 'Completed deliveries' },
        { key: 'pending', label: 'On the road', value: stats.pending, icon: 'clock', hint: 'Not delivered yet' },
        { key: 'cancelled', label: 'Cancelled', value: stats.cancelled, icon: 'clipboard', hint: 'Cancelled after assignment' },
    ];

    const columns = [
        {
            key: 'client',
            header: 'Delivery',
            primary: true,
            render: (delivery) => (
                <span className="block min-w-0">
                    <span className="block truncate font-medium text-slate-900">{delivery.client}</span>
                    <span className="block text-xs text-slate-500">{delivery.order_number}</span>
                </span>
            ),
        },
        { key: 'order_date', header: 'Date', render: (delivery) => formatDate(delivery.order_date) },
        { key: 'meal', header: 'Meal', render: (delivery) => `${delivery.meal_type_label} · ${formatNumber(delivery.quantity)}` },
        {
            key: 'delivered_at',
            header: 'Delivered',
            className: 'hidden xl:table-cell',
            render: (delivery) => delivery.delivered_at && formatDateTime(delivery.delivered_at),
        },
        { key: 'status', header: 'Status', render: (delivery) => <StatusBadge status={delivery.status} label={delivery.status_label} /> },
    ];

    return (
        <>
            <Head title={rider.name} />

            <PageHeader
                title={rider.name}
                description={
                    <span className="inline-flex flex-wrap items-center gap-2">
                        <StatusBadge status={rider.status} label={rider.status_label} />
                        <span>{[rider.vehicle_type_label, rider.vehicle_number].filter(Boolean).join(' · ')}</span>
                    </span>
                }
                back={{ href: route('riders.index'), label: 'Riders' }}
                actions={
                    <div className="flex items-center gap-2">
                        <Button as={Link} href={route('riders.edit', rider.id)} variant="secondary" icon={Pencil}>
                            Edit
                        </Button>
                        <RiderActionMenu rider={rider} showEdit={false} />
                    </div>
                }
            />

            <section aria-label="Delivery figures" className="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
                {statCards.map((stat) => (
                    <StatCard key={stat.key} stat={{ format: 'number', ...stat }} />
                ))}
            </section>

            <div className="mt-6 grid gap-6 lg:grid-cols-3">
                <Card className="min-w-0 lg:col-span-2">
                    <CardHeader title="Delivery history" />
                    <DataTable
                        caption="Delivery history"
                        columns={columns}
                        rows={deliveries.data}
                        rowHref={(delivery) => route('orders.show', delivery.order_id)}
                        mobileCard={(delivery) => (
                            <div className="space-y-1">
                                <div className="flex items-start justify-between gap-2">
                                    <p className="min-w-0 truncate font-semibold text-slate-900">{delivery.client}</p>
                                    <StatusBadge status={delivery.status} label={delivery.status_label} />
                                </div>
                                <p className="text-sm text-slate-600">
                                    {formatDate(delivery.order_date)} · {delivery.meal_type_label} · {formatNumber(delivery.quantity)} meals
                                </p>
                                <p className="text-xs text-slate-500">{delivery.order_number}</p>
                            </div>
                        )}
                        empty={<EmptyState icon={Truck} title="No deliveries yet" description="Deliveries assigned to this rider will be listed here." />}
                    />
                    <Pagination meta={deliveries.meta} links={deliveries.links} />
                </Card>

                <div className="min-w-0 space-y-6">
                    <Card>
                        <CardHeader title="Details" />
                        <CardBody className="space-y-5">
                            <a
                                href={`tel:${rider.phone}`}
                                className="inline-flex min-h-11 items-center gap-2 text-sm font-medium text-slate-700 hover:text-primary-700"
                            >
                                <Phone className="size-4 text-slate-400" aria-hidden="true" />
                                {rider.phone}
                            </a>
                            <DescriptionList
                                className="sm:grid-cols-1"
                                items={[
                                    { label: 'NID', value: rider.nid },
                                    { label: 'Address', value: rider.address },
                                    { label: 'Emergency contact', value: rider.emergency_contact },
                                    { label: 'Joining date', value: rider.joining_date && formatDate(rider.joining_date) },
                                    { label: 'Notes', value: rider.notes },
                                ]}
                            />
                        </CardBody>
                    </Card>

                    <Card>
                        <CardBody>
                            <p className="flex items-start gap-2 text-sm text-slate-600">
                                <KeyRound className="mt-0.5 size-4 shrink-0 text-slate-400" aria-hidden="true" />
                                {rider.has_login
                                    ? 'Has a login for the rider app. Signs in with their phone number.'
                                    : 'No login. Edit the rider to create one.'}
                            </p>
                        </CardBody>
                    </Card>
                </div>
            </div>
        </>
    );
}
