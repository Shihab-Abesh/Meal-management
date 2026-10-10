import { Head, Link } from '@inertiajs/react';
import { Bike, KeyRound, Plus, SearchX } from 'lucide-react';
import RiderActionMenu from '@/components/riders/rider-action-menu';
import Button from '@/components/ui/button';
import Card from '@/components/ui/card';
import DataTable from '@/components/ui/data-table';
import EmptyState from '@/components/ui/empty-state';
import FilterPanel, { FilterField } from '@/components/ui/filter-panel';
import PageHeader from '@/components/ui/page-header';
import Pagination from '@/components/ui/pagination';
import SearchInput from '@/components/ui/search-input';
import Select from '@/components/ui/select';
import StatusBadge from '@/components/ui/status-badge';
import { useFilters } from '@/hooks/use-filters';

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

const FILTER_KEYS = ['status', 'vehicle_type'];

export default function RidersIndex({ riders, filters, vehicleTypes }) {
    const { values, set, reset, loading } = useFilters(route('riders.index'), filters);
    const activeFilters = FILTER_KEYS.filter((key) => values[key]).length;

    const columns = [
        {
            key: 'name',
            header: 'Rider',
            primary: true,
            render: (rider) => (
                <span className="block min-w-0">
                    <span className="block truncate font-medium text-slate-900">{rider.name}</span>
                    <span className="block text-xs text-slate-500">{rider.phone}</span>
                </span>
            ),
        },
        {
            key: 'vehicle',
            header: 'Vehicle',
            render: (rider) => [rider.vehicle_type_label, rider.vehicle_number].filter(Boolean).join(' · '),
        },
        {
            key: 'open_deliveries',
            header: 'On the road',
            align: 'right',
            className: 'hidden lg:table-cell',
            render: (rider) => rider.open_deliveries,
        },
        {
            key: 'has_login',
            header: 'Login',
            className: 'hidden lg:table-cell',
            render: (rider) => (rider.has_login ? 'Yes' : <span className="text-slate-400">No</span>),
        },
        { key: 'status', header: 'Status', render: (rider) => <StatusBadge status={rider.status} label={rider.status_label} /> },
    ];

    return (
        <>
            <Head title="Riders" />

            <PageHeader
                title="Riders"
                description="The people who take meals to your clients."
                actions={
                    <Button as={Link} href={route('riders.create')} icon={Plus} className="w-full sm:w-auto">
                        Add rider
                    </Button>
                }
            />

            <Card>
                <div className="flex gap-2 border-b border-slate-100 p-4 sm:px-6 md:items-end">
                    <SearchInput
                        value={values.search}
                        onChange={(value) => set('search', value, { debounce: true })}
                        placeholder="Search name, phone, vehicle…"
                        className="min-w-0 flex-1 md:max-w-xs"
                    />
                    <FilterPanel activeCount={activeFilters} onReset={() => reset(FILTER_KEYS)}>
                        <FilterField label="Status" htmlFor="filter-status">
                            <Select
                                id="filter-status"
                                placeholder="Any status"
                                options={statusOptions}
                                value={values.status}
                                onChange={(event) => set('status', event.target.value)}
                            />
                        </FilterField>
                        <FilterField label="Vehicle" htmlFor="filter-vehicle">
                            <Select
                                id="filter-vehicle"
                                placeholder="Any vehicle"
                                options={vehicleTypes}
                                value={values.vehicle_type}
                                onChange={(event) => set('vehicle_type', event.target.value)}
                            />
                        </FilterField>
                    </FilterPanel>
                </div>

                <DataTable
                    caption="Riders"
                    columns={columns}
                    rows={riders.data}
                    loading={loading}
                    rowHref={(rider) => route('riders.show', rider.id)}
                    actions={(rider) => <RiderActionMenu rider={rider} showView />}
                    mobileCard={(rider) => (
                        <div className="space-y-1.5">
                            <div className="flex items-start justify-between gap-2">
                                <p className="min-w-0 truncate font-semibold text-slate-900">{rider.name}</p>
                                <StatusBadge status={rider.status} label={rider.status_label} />
                            </div>
                            <p className="truncate text-sm text-slate-600">
                                {[rider.phone, rider.vehicle_type_label, rider.vehicle_number].filter(Boolean).join(' · ')}
                            </p>
                            <p className="flex items-center gap-3 text-xs text-slate-500">
                                <span>{rider.open_deliveries} on the road</span>
                                {rider.has_login && (
                                    <span className="inline-flex items-center gap-1">
                                        <KeyRound className="size-3.5" aria-hidden="true" />
                                        Has login
                                    </span>
                                )}
                            </p>
                        </div>
                    )}
                    empty={
                        activeFilters > 0 || values.search ? (
                            <EmptyState
                                icon={SearchX}
                                title="No riders found"
                                description="Try another search or clear the filters."
                                action={
                                    <Button variant="secondary" onClick={() => reset(['search', ...FILTER_KEYS])}>
                                        Clear search and filters
                                    </Button>
                                }
                            />
                        ) : (
                            <EmptyState
                                icon={Bike}
                                title="No riders yet"
                                description="Add your first rider to start assigning deliveries."
                                action={
                                    <Button as={Link} href={route('riders.create')} icon={Plus}>
                                        Add rider
                                    </Button>
                                }
                            />
                        )
                    }
                />

                <Pagination meta={riders.meta} links={riders.links} />
            </Card>
        </>
    );
}
