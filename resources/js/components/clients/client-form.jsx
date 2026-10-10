import { Link, useForm } from '@inertiajs/react';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import ChoiceChips from '@/components/ui/choice-chips';
import FormActions from '@/components/ui/form-actions';
import FormField from '@/components/ui/form-field';
import Input from '@/components/ui/input';
import Select from '@/components/ui/select';
import Textarea from '@/components/ui/textarea';

/**
 * Create / edit form for a client organization.
 */
export default function ClientForm({ client, mealTypes, statuses, cities, submitLabel, cancelUrl, onSubmit }) {
    const form = useForm({
        name: client?.name ?? '',
        contact_person: client?.contact_person ?? '',
        phone: client?.phone ?? '',
        email: client?.email ?? '',
        address: client?.address ?? '',
        city: client?.city ?? '',
        delivery_address: client?.delivery_address ?? '',
        employee_count: client?.employee_count ?? '',
        meal_types: client?.meal_types ?? ['lunch'],
        office_start_time: client?.office_start_time ?? '',
        lunch_cutoff_time: client?.lunch_cutoff_time ?? '',
        dinner_cutoff_time: client?.dinner_cutoff_time ?? '',
        special_instructions: client?.special_instructions ?? '',
        status: client?.status ?? 'active',
        notes: client?.notes ?? '',
    });
    const { data, setData, errors, processing, isDirty } = form;

    function field(name) {
        return { value: data[name] ?? '', onChange: (event) => setData(name, event.target.value) };
    }

    function submit(event) {
        event.preventDefault();
        onSubmit(form);
    }

    return (
        <form onSubmit={submit} noValidate className="space-y-6">
            <Card>
                <CardHeader title="Organization" />
                <CardBody className="grid gap-5 sm:grid-cols-2">
                    <FormField name="name" label="Organization name" error={errors.name} required className="sm:col-span-2">
                        <Input autoComplete="organization" {...field('name')} />
                    </FormField>
                    <FormField
                        name="employee_count"
                        label="Number of employees"
                        hint="Headcount agreed with the client."
                        error={errors.employee_count}
                    >
                        <Input type="number" inputMode="numeric" min="0" {...field('employee_count')} />
                    </FormField>
                    <FormField name="status" label="Status" error={errors.status} required>
                        <Select options={statuses} {...field('status')} />
                    </FormField>
                </CardBody>
            </Card>

            <Card>
                <CardHeader title="Contact" />
                <CardBody className="grid gap-5 sm:grid-cols-2">
                    <FormField
                        name="contact_person"
                        label="Contact person"
                        error={errors.contact_person}
                        required
                        className="sm:col-span-2"
                    >
                        <Input autoComplete="name" {...field('contact_person')} />
                    </FormField>
                    <FormField name="phone" label="Phone" error={errors.phone} required>
                        <Input type="tel" inputMode="tel" autoComplete="tel" {...field('phone')} />
                    </FormField>
                    <FormField name="email" label="Email" error={errors.email}>
                        <Input type="email" autoComplete="email" {...field('email')} />
                    </FormField>
                </CardBody>
            </Card>

            <Card>
                <CardHeader title="Address & delivery" />
                <CardBody className="grid gap-5 sm:grid-cols-2">
                    <FormField name="address" label="Office address" error={errors.address} required className="sm:col-span-2">
                        <Textarea rows={2} autoComplete="street-address" {...field('address')} />
                    </FormField>
                    <FormField name="city" label="City" error={errors.city} required>
                        <Input list="client-city-options" autoComplete="address-level2" {...field('city')} />
                    </FormField>
                    <datalist id="client-city-options">
                        {cities.map((city) => (
                            <option key={city} value={city} />
                        ))}
                    </datalist>
                    <FormField
                        name="delivery_address"
                        label="Delivery address"
                        hint="Leave empty to deliver to the office address."
                        error={errors.delivery_address}
                        className="sm:col-span-2"
                    >
                        <Textarea rows={2} {...field('delivery_address')} />
                    </FormField>
                </CardBody>
            </Card>

            <Card>
                <CardHeader title="Meal settings" description="Cutoff times limit when meals can be changed for the day." />
                <CardBody className="grid gap-5 sm:grid-cols-3">
                    <FormField name="meal_types" label="Meal types" error={errors.meal_types} required className="sm:col-span-3">
                        <ChoiceChips
                            multiple
                            options={mealTypes}
                            value={data.meal_types}
                            onChange={(value) => setData('meal_types', value)}
                        />
                    </FormField>
                    <FormField name="office_start_time" label="Office start time" error={errors.office_start_time}>
                        <Input type="time" {...field('office_start_time')} />
                    </FormField>
                    <FormField
                        name="lunch_cutoff_time"
                        label="Lunch cutoff"
                        hint="Empty = default cutoff."
                        error={errors.lunch_cutoff_time}
                    >
                        <Input type="time" {...field('lunch_cutoff_time')} />
                    </FormField>
                    <FormField
                        name="dinner_cutoff_time"
                        label="Dinner cutoff"
                        hint="Empty = default cutoff."
                        error={errors.dinner_cutoff_time}
                    >
                        <Input type="time" {...field('dinner_cutoff_time')} />
                    </FormField>
                    <FormField
                        name="special_instructions"
                        label="Special instructions"
                        hint="Shown to the kitchen and riders, e.g. 'Deliver to 5th floor reception'."
                        error={errors.special_instructions}
                        className="sm:col-span-3"
                    >
                        <Textarea rows={3} {...field('special_instructions')} />
                    </FormField>
                </CardBody>
            </Card>

            <Card>
                <CardHeader title="Internal notes" description="Only visible to your team." />
                <CardBody>
                    <FormField name="notes" label="Notes" error={errors.notes}>
                        <Textarea rows={3} {...field('notes')} />
                    </FormField>
                </CardBody>
            </Card>

            <FormActions>
                <Button as={Link} href={cancelUrl} variant="secondary">
                    Cancel
                </Button>
                <Button type="submit" loading={processing} disabled={client && !isDirty}>
                    {submitLabel}
                </Button>
            </FormActions>
        </form>
    );
}
