import { Link, useForm } from '@inertiajs/react';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import DateInput from '@/components/ui/date-input';
import FormActions from '@/components/ui/form-actions';
import FormField from '@/components/ui/form-field';
import Input from '@/components/ui/input';
import Select from '@/components/ui/select';
import Textarea from '@/components/ui/textarea';
import Toggle from '@/components/ui/toggle';

/**
 * Create / edit form for an employee. Vendor users pick the client when
 * creating; client accounts and edits keep the client fixed.
 */
export default function EmployeeForm({
    employee,
    clients,
    selectedClient,
    mealPreferences,
    statuses,
    submitLabel,
    cancelUrl,
    onSubmit,
}) {
    const form = useForm({
        client_id: employee?.client?.id ? String(employee.client.id) : selectedClient ? String(selectedClient) : '',
        employee_code: employee?.employee_code ?? '',
        name: employee?.name ?? '',
        phone: employee?.phone ?? '',
        email: employee?.email ?? '',
        department: employee?.department ?? '',
        designation: employee?.designation ?? '',
        meal_preference: employee?.meal_preference ?? 'regular',
        lunch_enabled: employee?.lunch_enabled ?? true,
        dinner_enabled: employee?.dinner_enabled ?? false,
        status: employee?.status ?? 'active',
        joining_date: employee?.joining_date ?? '',
        notes: employee?.notes ?? '',
        add_another: false,
    });
    const { data, setData, errors, processing, isDirty } = form;
    const chooseClient = clients && !employee;

    function field(name) {
        return { value: data[name] ?? '', onChange: (event) => setData(name, event.target.value) };
    }

    function submit(event, addAnother = false) {
        event.preventDefault();
        form.transform((values) => ({ ...values, add_another: addAnother }));
        onSubmit(form);
    }

    return (
        <form onSubmit={submit} noValidate className="space-y-6">
            <Card>
                <CardHeader title="Employee" />
                <CardBody className="grid gap-5 sm:grid-cols-2">
                    {chooseClient && (
                        <FormField name="client_id" label="Client" error={errors.client_id} required className="sm:col-span-2">
                            <Select placeholder="Choose a client" options={clients} {...field('client_id')} />
                        </FormField>
                    )}
                    <FormField name="name" label="Employee name" error={errors.name} required>
                        <Input autoComplete="off" {...field('name')} />
                    </FormField>
                    <FormField
                        name="employee_code"
                        label="Employee ID"
                        hint="The client's own staff ID, e.g. EMP-001."
                        error={errors.employee_code}
                        required
                    >
                        <Input autoComplete="off" autoCapitalize="characters" {...field('employee_code')} />
                    </FormField>
                    <FormField name="department" label="Department" error={errors.department}>
                        <Input {...field('department')} />
                    </FormField>
                    <FormField name="designation" label="Designation" error={errors.designation}>
                        <Input {...field('designation')} />
                    </FormField>
                    <FormField name="phone" label="Phone" error={errors.phone}>
                        <Input type="tel" inputMode="tel" autoComplete="off" {...field('phone')} />
                    </FormField>
                    <FormField name="email" label="Email" error={errors.email}>
                        <Input type="email" autoComplete="off" {...field('email')} />
                    </FormField>
                    <FormField name="joining_date" label="Joining date" error={errors.joining_date}>
                        <DateInput {...field('joining_date')} />
                    </FormField>
                    <FormField name="status" label="Status" error={errors.status} required>
                        <Select options={statuses} {...field('status')} />
                    </FormField>
                </CardBody>
            </Card>

            <Card>
                <CardHeader title="Meals" description="Which meals this employee normally takes." />
                <CardBody className="grid gap-5 sm:grid-cols-2">
                    <div className="divide-y divide-slate-100 rounded-lg ring-1 ring-slate-200 sm:col-span-2">
                        <Toggle
                            className="px-4 py-2"
                            label="Lunch"
                            description="Shown on the daily lunch sheet."
                            checked={data.lunch_enabled}
                            onChange={(value) => setData('lunch_enabled', value)}
                        />
                        <Toggle
                            className="px-4 py-2"
                            label="Dinner"
                            description="Shown on the daily dinner sheet."
                            checked={data.dinner_enabled}
                            onChange={(value) => setData('dinner_enabled', value)}
                        />
                    </div>
                    <FormField name="meal_preference" label="Meal preference" error={errors.meal_preference} required>
                        <Select options={mealPreferences} {...field('meal_preference')} />
                    </FormField>
                    <FormField name="notes" label="Notes" error={errors.notes} className="sm:col-span-2">
                        <Textarea rows={3} placeholder="Allergies or other details for the kitchen" {...field('notes')} />
                    </FormField>
                </CardBody>
            </Card>

            <FormActions>
                <Button as={Link} href={cancelUrl} variant="secondary">
                    Cancel
                </Button>
                {!employee && (
                    <Button
                        variant="secondary"
                        disabled={processing}
                        onClick={(event) => submit(event, true)}
                        className="max-sm:hidden"
                    >
                        Save & add another
                    </Button>
                )}
                <Button type="submit" loading={processing} disabled={employee && !isDirty}>
                    {submitLabel}
                </Button>
            </FormActions>
        </form>
    );
}
