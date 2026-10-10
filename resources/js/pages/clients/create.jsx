import { Head } from '@inertiajs/react';
import ClientForm from '@/components/clients/client-form';
import PageHeader from '@/components/ui/page-header';

export default function CreateClient({ mealTypes, statuses, cities }) {
    return (
        <>
            <Head title="Add client" />

            <PageHeader
                title="Add client"
                description="A client organization receives meals for its employees."
                back={{ href: route('clients.index'), label: 'Clients' }}
            />

            <ClientForm
                mealTypes={mealTypes}
                statuses={statuses}
                cities={cities}
                submitLabel="Add client"
                cancelUrl={route('clients.index')}
                onSubmit={(form) => form.post(route('clients.store'), { preserveScroll: true })}
            />
        </>
    );
}
