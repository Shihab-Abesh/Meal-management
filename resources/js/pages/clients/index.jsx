import { Head, Link } from '@inertiajs/react';
import { Building2, Phone, Plus, SearchX } from 'lucide-react';
import ClientActionMenu from '@/components/clients/client-action-menu';
import MealTypeBadges from '@/components/clients/meal-type-badges';
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
    { value: 'archived', label: 'Archived' },
];

const sortOptions = [
    { value: 'name:asc', label: 'Name (A–Z)' },
    { value: 'name:desc', label: 'Name (Z–A)' },
    { value: 'city:asc', label: 'City' },
    { value: 'employees_count:desc', label: 'Most employees' },
    { value: 'created_at:desc', label: 'Newest first' },
    { value: 'created_at:asc', label: 'Oldest first' },
];

const FILTER_KEYS = ['status', 'meal_type', 'city'];

function clientStatus(client) {
    return client.archived ? <StatusBadge status="archived" /> : <StatusBadge status={client.status} label={client.status_label} />;
}

export default function ClientsIndex({ clients, filters, cities, mealTypes, can }) {
    const { values, set, setMany, reset, loading } = useFilters(route('clients.index'), filters);
    const activeFilters = FILTER_KEYS.filter((key) => values[key]).length;
    const hasQuery = activeFilters > 0 || values.search;

    function sortBy(key) {
        const direction = values.sort === key && values.direction === 'asc' ? 'desc' : 'asc';
        setMany({ sort: key, direction });
    }

    const columns = [
        {
            key: 'name',
            header: 'Organization',
            primary: true,
            sortable: true,
            render: (client) => (
                <span className="block min-w-0">
                    <span className="block truncate font-medium text-slate-900">{client.name}</span>
                    <span className="block truncate text-xs text-slate-500">{client.contact_person}</span>
                </span>
            ),
        },
        {
            key: 'phone',
            header: 'Contact',
            render: (client) => (
                <span className="block min-w-0">
                    <span className="block">{client.phone}</span>
                    {client.email && <span className="block truncate text-xs text-slate-500">{client.email}</span>}
                </span>
            ),
        },
        { key: 'city', header: 'City', sortable: true, className: 'hidden xl:table-cell' },
        { key: 'employees_count', header: 'Employees', sortable: true, align: 'right', className: 'hidden xl:table-cell' },
        { key: 'meal_types', header: 'Meals', render: (client) => <MealTypeBadges types={client.meal_types} /> },
        { key: 'status', header: 'Status', render: clientStatus },
    ];

    const emptyState = hasQuery ? (
        <EmptyState
            icon={SearchX}
            title="No clients match your search"
            description="Try a different name or clear the filters."
            action={
                <Button variant="secondary" onClick={() => reset(['search', ...FILTER_KEYS])}>
                    Clear search and filters
                </Button>
            }
        />
    ) : (
        <EmptyState
            icon={Building2}
            title="No clients yet"
            description="Add the offices you deliver meals to. Each client gets its own employees, meals and reports."
            action={
                can.create && (
                    <Button as={Link} href={route('clients.create')} icon={Plus}>
                        Add your first client
                    </Button>
                )
            }
        />
    );

    return (
        <>
            <Head title="Clients" />

            <PageHeader
                title="Clients"
                description="Organizations you supply meals to."
                actions={
                    can.create && (
                        <Button as={Link} href={route('clients.create')} icon={Plus} className="w-full sm:w-auto">
                            Add client
                        </Button>
                    )
                }
            />

            <Card>
                <div className="flex flex-col gap-3 border-b border-slate-100 p-4 sm:px-6 md:flex-row md:items-end">
                    <div className="flex gap-2 md:contents">
                        <SearchInput
                            value={values.search}
                            onChange={(value) => set('search', value, { debounce: true })}
                            placeholder="Search name, contact, phone…"
                            className="min-w-0 flex-1 md:max-w-xs"
                        />
                        <FilterPanel activeCount={activeFilters} onReset={() => reset(FILTER_KEYS)}>
                            <FilterField label="Status" htmlFor="filter-status">
                                <Select
                                    id="filter-status"
                                    placeholder="Active & inactive"
                                    options={statusOptions}
                                    value={values.status}
                                    onChange={(event) => set('status', event.target.value)}
                                />
                            </FilterField>
                            <FilterField label="Meal type" htmlFor="filter-meal">
                                <Select
                                    id="filter-meal"
                                    placeholder="All meal types"
                                    options={mealTypes}
                                    value={values.meal_type}
                                    onChange={(event) => set('meal_type', event.target.value)}
                                />
                            </FilterField>
                            <FilterField label="City" htmlFor="filter-city">
                                <Select
                                    id="filter-city"
                                    placeholder="All cities"
                                    options={cities.map((city) => ({ value: city, label: city }))}
                                    value={values.city}
                                    onChange={(event) => set('city', event.target.value)}
                                />
                            </FilterField>
                            <FilterField label="Sort by" htmlFor="filter-sort">
                                <Select
                                    id="filter-sort"
                                    options={sortOptions}
                                    value={`${values.sort}:${values.direction}`}
                                    onChange={(event) => {
                                        const [sort, direction] = event.target.value.split(':');
                                        setMany({ sort, direction });
                                    }}
                                />
                            </FilterField>
                        </FilterPanel>
                    </div>
                </div>

                <DataTable
                    caption="Clients"
                    columns={columns}
                    rows={clients.data}
                    sort={{ key: values.sort, direction: values.direction }}
                    onSort={sortBy}
                    loading={loading}
                    rowHref={(client) => route('clients.show', client.id)}
                    actions={(client) => <ClientActionMenu client={client} canManage={can.create} showView />}
                    mobileCard={(client) => (
                        <div className="space-y-1.5">
                            <div className="flex items-start justify-between gap-2">
                                <p className="min-w-0 truncate font-semibold text-slate-900">{client.name}</p>
                                {clientStatus(client)}
                            </div>
                            <p className="truncate text-sm text-slate-600">
                                {client.contact_person} · {client.city}
                            </p>
                            <div className="flex flex-wrap items-center gap-x-3 gap-y-1.5 text-sm text-slate-500">
                                <span className="inline-flex items-center gap-1">
                                    <Phone className="size-3.5" aria-hidden="true" />
                                    {client.phone}
                                </span>
                                <MealTypeBadges types={client.meal_types} />
                            </div>
                        </div>
                    )}
                    empty={emptyState}
                />

                <Pagination meta={clients.meta} links={clients.links} />
            </Card>
        </>
    );
}
