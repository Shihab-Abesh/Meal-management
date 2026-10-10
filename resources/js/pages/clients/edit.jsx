import { Head } from '@inertiajs/react';
import ClientForm from '@/components/clients/client-form';
import PageHeader from '@/components/ui/page-header';

export default function EditClient({ client, mealTypes, statuses, cities }) {
    const showUrl = route('clients.show', client.id);

    return (
        <>
            <Head title={`Edit ${client.name}`} />

            <PageHeader title="Edit client" description={client.name} back={{ href: showUrl, label: client.name }} />

            <ClientForm
                client={client}
                mealTypes={mealTypes}
                statuses={statuses}
                cities={cities}
                submitLabel="Save changes"
                cancelUrl={showUrl}
                onSubmit={(form) => form.put(route('clients.update', client.id), { preserveScroll: true })}
            />
        </>
    );
}
