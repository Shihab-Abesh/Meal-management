import { Head } from '@inertiajs/react';
import EmployeeForm from '@/components/employees/employee-form';
import PageHeader from '@/components/ui/page-header';

export default function EditEmployee({ employee, mealPreferences, statuses }) {
    const showUrl = route('employees.show', employee.id);

    return (
        <>
            <Head title={`Edit ${employee.name}`} />

            <PageHeader
                title="Edit employee"
                description={[employee.name, employee.client?.name].filter(Boolean).join(' · ')}
                back={{ href: showUrl, label: employee.name }}
            />

            <EmployeeForm
                employee={employee}
                mealPreferences={mealPreferences}
                statuses={statuses}
                submitLabel="Save changes"
                cancelUrl={showUrl}
                onSubmit={(form) => form.put(route('employees.update', employee.id), { preserveScroll: true })}
            />
        </>
    );
}
