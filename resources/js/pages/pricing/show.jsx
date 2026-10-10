import { Head, router, useForm, usePage } from '@inertiajs/react';
import { History, Trash2 } from 'lucide-react';
import { useState } from 'react';
import Alert from '@/components/ui/alert';
import Badge from '@/components/ui/badge';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import ChoiceChips from '@/components/ui/choice-chips';
import ConfirmDialog from '@/components/ui/confirm-dialog';
import DataTable from '@/components/ui/data-table';
import DateInput from '@/components/ui/date-input';
import EmptyState from '@/components/ui/empty-state';
import FormField from '@/components/ui/form-field';
import Input from '@/components/ui/input';
import MoneyInput from '@/components/ui/money-input';
import PageHeader from '@/components/ui/page-header';
import { useApp } from '@/hooks/use-app';
import { formatDate } from '@/lib/format';

const sourceLabels = {
    client: { label: 'Client price', tone: 'blue' },
    menu: { label: "From today's menu", tone: 'amber' },
    default: { label: 'Default price', tone: 'gray' },
};

export default function ClientPricing({ client, current, history, mealTypes, today }) {
    const { money } = useApp();
    const { errors: pageErrors = {} } = usePage().props;
    const [removing, setRemoving] = useState(null);
    const [removeOpen, setRemoveOpen] = useState(false);
    const [processing, setProcessing] = useState(false);

    const servedTypes = mealTypes.filter((type) => client.meal_types.includes(type.value));

    function remove() {
        router.delete(route('pricing.destroy', [client.id, removing.id]), {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setRemoveOpen(false);
            },
        });
    }

    const columns = [
        { key: 'effective_from', header: 'From', primary: true, render: (row) => (
            <span className="inline-flex flex-wrap items-center gap-2">
                <span className="font-medium text-slate-900">{formatDate(row.effective_from)}</span>
                {row.upcoming && <Badge tone="violet">Scheduled</Badge>}
            </span>
        ) },
        { key: 'meal_type_label', header: 'Meal' },
        { key: 'price', header: 'Price', align: 'right', render: (row) => <span className="tabular-nums">{money(row.price)}</span> },
        { key: 'notes', header: 'Note', className: 'hidden lg:table-cell' },
        { key: 'created_by', header: 'Set by', className: 'hidden xl:table-cell' },
    ];

    return (
        <>
            <Head title={`Pricing · ${client.name}`} />

            <PageHeader title={client.name} description="Meal prices for this client." back={{ href: route('pricing.index'), label: 'Pricing' }} />

            {pageErrors.price && (
                <Alert tone="error" className="mb-4">
                    {pageErrors.price}
                </Alert>
            )}

            <section aria-label="Current prices" className="mb-6 grid gap-3 sm:grid-cols-2">
                {servedTypes.map((type) => {
                    const price = current[type.value];
                    const source = sourceLabels[price.source];

                    return (
                        <Card key={type.value} className="p-4 sm:p-5">
                            <p className="text-sm font-medium text-slate-500">{type.label} today</p>
                            <p className="mt-1 text-3xl font-bold tracking-tight text-slate-900 tabular-nums">{money(price.price)}</p>
                            <Badge tone={source.tone} className="mt-2">
                                {source.label}
                            </Badge>
                        </Card>
                    );
                })}
            </section>

            <div className="grid gap-6 lg:grid-cols-3">
                <PriceForm client={client} mealTypes={servedTypes.length ? servedTypes : mealTypes} today={today} />

                <Card className="lg:col-span-2">
                    <CardHeader title="Price history" description="Each price applies from its date until the next one." />
                    <DataTable
                        caption="Price history"
                        columns={columns}
                        rows={history}
                        actions={(row) =>
                            row.upcoming && (
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    aria-label={`Remove scheduled price from ${formatDate(row.effective_from)}`}
                                    onClick={() => {
                                        setRemoving(row);
                                        setRemoveOpen(true);
                                    }}
                                    className="text-red-600 hover:text-red-700"
                                >
                                    <Trash2 className="size-4" aria-hidden="true" />
                                </Button>
                            )
                        }
                        empty={
                            <EmptyState
                                icon={History}
                                title="No client prices yet"
                                description="This client pays the default price until you set its own."
                            />
                        }
                    />
                </Card>
            </div>

            <ConfirmDialog
                open={removeOpen}
                onClose={() => setRemoveOpen(false)}
                onConfirm={remove}
                processing={processing}
                title="Remove scheduled price?"
                description={removing && `The ${removing.meal_type_label.toLowerCase()} price of ${money(removing.price)} from ${formatDate(removing.effective_from)} will not take effect.`}
                confirmLabel="Remove"
            />
        </>
    );
}

function PriceForm({ client, mealTypes, today }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        meal_type: mealTypes[0]?.value ?? 'lunch',
        price: '',
        effective_from: today,
        notes: '',
    });

    function submit(event) {
        event.preventDefault();
        post(route('pricing.store', client.id), { preserveScroll: true, onSuccess: () => reset('price', 'notes') });
    }

    return (
        <Card as="form" onSubmit={submit} noValidate className="h-fit">
            <CardHeader title="Set a price" description="Pick a future date to schedule a change." />
            <CardBody className="space-y-5">
                <FormField name="meal_type" label="Meal" error={errors.meal_type} required>
                    <ChoiceChips options={mealTypes} value={data.meal_type} onChange={(value) => setData('meal_type', value)} />
                </FormField>
                <FormField name="price" label="Price per meal" error={errors.price} required>
                    <MoneyInput value={data.price} onChange={(event) => setData('price', event.target.value)} />
                </FormField>
                <FormField
                    name="effective_from"
                    label="Applies from"
                    hint="Meals already recorded keep the price they were recorded with."
                    error={errors.effective_from}
                    required
                >
                    <DateInput value={data.effective_from} onChange={(event) => setData('effective_from', event.target.value)} />
                </FormField>
                <FormField name="notes" label="Note" error={errors.notes}>
                    <Input value={data.notes} onChange={(event) => setData('notes', event.target.value)} placeholder="e.g. New contract 2026" />
                </FormField>
                <Button type="submit" loading={processing} className="w-full">
                    Save price
                </Button>
            </CardBody>
        </Card>
    );
}
