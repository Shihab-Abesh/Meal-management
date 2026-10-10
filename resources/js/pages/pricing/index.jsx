import { Head, useForm } from '@inertiajs/react';
import { Building2, SearchX } from 'lucide-react';
import Badge from '@/components/ui/badge';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import DataTable from '@/components/ui/data-table';
import EmptyState from '@/components/ui/empty-state';
import FormField from '@/components/ui/form-field';
import MoneyInput from '@/components/ui/money-input';
import PageHeader from '@/components/ui/page-header';
import SearchInput from '@/components/ui/search-input';
import StatusBadge from '@/components/ui/status-badge';
import { useApp } from '@/hooks/use-app';
import { useFilters } from '@/hooks/use-filters';

export default function PricingIndex({ defaults, clients, filters }) {
    const { money } = useApp();
    const { values, set, loading } = useFilters(route('pricing.index'), filters);

    function priceCell(client, type) {
        if (!client.meal_types.includes(type)) {
            return <span className="text-sm text-slate-400">Not served</span>;
        }

        const price = client.prices[type];

        return (
            <span className="inline-flex flex-wrap items-center gap-2">
                <span className="font-semibold text-slate-900 tabular-nums">{money(price.price)}</span>
                {price.custom ? <Badge tone="blue">Client price</Badge> : <Badge>Default</Badge>}
            </span>
        );
    }

    const columns = [
        {
            key: 'name',
            header: 'Client',
            primary: true,
            render: (client) => (
                <span className="inline-flex flex-wrap items-center gap-2">
                    <span className="font-medium text-slate-900">{client.name}</span>
                    {client.status !== 'active' && <StatusBadge status={client.status} />}
                </span>
            ),
        },
        { key: 'lunch', header: 'Lunch', render: (client) => priceCell(client, 'lunch') },
        { key: 'dinner', header: 'Dinner', render: (client) => priceCell(client, 'dinner') },
    ];

    return (
        <>
            <Head title="Pricing" />

            <PageHeader
                title="Pricing"
                description="What each client pays per meal. Price changes apply from their start date; meals already recorded keep their price."
            />

            <div className="grid gap-6 lg:grid-cols-3">
                <DefaultPrices defaults={defaults} />

                <Card className="lg:col-span-2">
                    <CardHeader title="Client prices" description="Prices in effect today. Open a client to change or schedule a price." />
                    <div className="border-b border-slate-100 p-4 sm:px-6">
                        <SearchInput
                            value={values.search}
                            onChange={(value) => set('search', value, { debounce: true })}
                            placeholder="Search clients…"
                            className="md:max-w-xs"
                        />
                    </div>
                    <DataTable
                        caption="Client prices"
                        columns={columns}
                        rows={clients}
                        loading={loading}
                        rowHref={(client) => route('pricing.show', client.id)}
                        empty={
                            values.search ? (
                                <EmptyState icon={SearchX} title="No clients match your search" />
                            ) : (
                                <EmptyState icon={Building2} title="No clients yet" description="Add clients first, then set their prices here." />
                            )
                        }
                    />
                </Card>
            </div>
        </>
    );
}

function DefaultPrices({ defaults }) {
    const { data, setData, put, processing, errors, isDirty } = useForm({
        lunch_price: defaults.lunch,
        dinner_price: defaults.dinner,
    });

    function submit(event) {
        event.preventDefault();
        put(route('pricing.defaults'), { preserveScroll: true });
    }

    return (
        <Card as="form" onSubmit={submit} noValidate className="h-fit">
            <CardHeader title="Default prices" description="Used for clients without their own price and days without a menu price." />
            <CardBody className="space-y-5">
                <FormField name="lunch_price" label="Lunch" error={errors.lunch_price} required>
                    <MoneyInput value={data.lunch_price} onChange={(event) => setData('lunch_price', event.target.value)} />
                </FormField>
                <FormField name="dinner_price" label="Dinner" error={errors.dinner_price} required>
                    <MoneyInput value={data.dinner_price} onChange={(event) => setData('dinner_price', event.target.value)} />
                </FormField>
                <Button type="submit" loading={processing} disabled={!isDirty} className="w-full">
                    Save default prices
                </Button>
            </CardBody>
        </Card>
    );
}
