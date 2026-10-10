import { Head } from '@inertiajs/react';
import EmployeeForm from '@/components/employees/employee-form';
import PageHeader from '@/components/ui/page-header';

export default function CreateEmployee({ clients, selectedClient, mealPreferences, statuses }) {
    return (
        <>
            <Head title="Add employee" />

            <PageHeader
                title="Add employee"
                description="Employees appear on the daily meal sheet for their client."
                back={{ href: route('employees.index'), label: 'Employees' }}
            />

            <EmployeeForm
                clients={clients}
                selectedClient={selectedClient}
                mealPreferences={mealPreferences}
                statuses={statuses}
                submitLabel="Add employee"
                cancelUrl={route('employees.index')}
                onSubmit={(form) => form.post(route('employees.store'), { preserveScroll: true })}
            />
        </>
    );
}
