import { Head, Link, useForm } from '@inertiajs/react';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import DateInput from '@/components/ui/date-input';
import FormActions from '@/components/ui/form-actions';
import FormField from '@/components/ui/form-field';
import Input from '@/components/ui/input';
import PageHeader from '@/components/ui/page-header';
import PasswordInput from '@/components/ui/password-input';
import Select from '@/components/ui/select';
import Textarea from '@/components/ui/textarea';
import Toggle from '@/components/ui/toggle';

/**
 * Add or edit a rider. A rider can optionally get a login (phone number +
 * password) to use the rider app on their phone.
 */
export default function RiderForm({ rider, vehicleTypes, statuses }) {
    const editing = rider !== null;
    const { data, setData, post, put, processing, errors, isDirty } = useForm({
        name: rider?.name ?? '',
        phone: rider?.phone ?? '',
        nid: rider?.nid ?? '',
        address: rider?.address ?? '',
        emergency_contact: rider?.emergency_contact ?? '',
        vehicle_type: rider?.vehicle_type ?? 'motorcycle',
        vehicle_number: rider?.vehicle_number ?? '',
        joining_date: rider?.joining_date ?? '',
        status: rider?.status ?? 'active',
        notes: rider?.notes ?? '',
        create_login: false,
        password: '',
    });
    const hasLogin = Boolean(rider?.has_login);
    const cancelUrl = editing ? route('riders.show', rider.id) : route('riders.index');

    function field(name) {
        return { value: data[name] ?? '', onChange: (event) => setData(name, event.target.value) };
    }

    function submit(event) {
        event.preventDefault();

        if (editing) {
            put(route('riders.update', rider.id), { preserveScroll: true });
        } else {
            post(route('riders.store'), { preserveScroll: true });
        }
    }

    return (
        <>
            <Head title={editing ? `Edit ${rider.name}` : 'Add rider'} />

            <PageHeader
                title={editing ? 'Edit rider' : 'Add rider'}
                description={editing ? rider.name : 'Riders can be assigned to deliveries once they are active.'}
                back={{ href: cancelUrl, label: editing ? rider.name : 'Riders' }}
            />

            <form onSubmit={submit} noValidate className="space-y-6">
                <Card>
                    <CardHeader title="Rider" />
                    <CardBody className="grid gap-5 sm:grid-cols-2">
                        <FormField name="name" label="Rider name" error={errors.name} required>
                            <Input autoComplete="off" {...field('name')} />
                        </FormField>
                        <FormField name="phone" label="Phone" error={errors.phone} required hint={hasLogin ? 'Also used to sign in.' : undefined}>
                            <Input type="tel" inputMode="tel" autoComplete="off" {...field('phone')} />
                        </FormField>
                        <FormField name="nid" label="NID" hint="Optional national ID number." error={errors.nid}>
                            <Input inputMode="numeric" autoComplete="off" {...field('nid')} />
                        </FormField>
                        <FormField name="joining_date" label="Joining date" error={errors.joining_date}>
                            <DateInput {...field('joining_date')} />
                        </FormField>
                        <FormField name="address" label="Address" error={errors.address} className="sm:col-span-2">
                            <Textarea rows={2} {...field('address')} />
                        </FormField>
                        <FormField
                            name="emergency_contact"
                            label="Emergency contact"
                            hint="Name and phone number, e.g. Karim (brother) 01711-000000."
                            error={errors.emergency_contact}
                            className="sm:col-span-2"
                        >
                            <Input autoComplete="off" {...field('emergency_contact')} />
                        </FormField>
                    </CardBody>
                </Card>

                <Card>
                    <CardHeader title="Vehicle and status" />
                    <CardBody className="grid gap-5 sm:grid-cols-2">
                        <FormField name="vehicle_type" label="Vehicle type" error={errors.vehicle_type} required>
                            <Select options={vehicleTypes} {...field('vehicle_type')} />
                        </FormField>
                        <FormField name="vehicle_number" label="Vehicle number" error={errors.vehicle_number}>
                            <Input autoComplete="off" autoCapitalize="characters" {...field('vehicle_number')} />
                        </FormField>
                        <FormField name="status" label="Status" error={errors.status} required>
                            <Select options={statuses} {...field('status')} />
                        </FormField>
                        <FormField name="notes" label="Notes" error={errors.notes} className="sm:col-span-2">
                            <Textarea rows={3} {...field('notes')} />
                        </FormField>
                    </CardBody>
                </Card>

                <Card>
                    <CardHeader
                        title="Rider app login"
                        description="With a login the rider sees their deliveries on their phone and marks them picked up and delivered."
                    />
                    <CardBody className="space-y-5">
                        {hasLogin ? (
                            <FormField
                                name="password"
                                label="New password"
                                hint="Leave empty to keep the current password. The rider signs in with their phone number."
                                error={errors.password}
                            >
                                <PasswordInput autoComplete="new-password" {...field('password')} />
                            </FormField>
                        ) : (
                            <>
                                <Toggle
                                    label="Create a login"
                                    description="The rider signs in with their phone number."
                                    checked={data.create_login}
                                    onChange={(value) => setData('create_login', value)}
                                />
                                {data.create_login && (
                                    <FormField name="password" label="Password" hint="At least 8 characters with letters and numbers." error={errors.password} required>
                                        <PasswordInput autoComplete="new-password" {...field('password')} />
                                    </FormField>
                                )}
                            </>
                        )}
                    </CardBody>
                </Card>

                <FormActions>
                    <Button as={Link} href={cancelUrl} variant="secondary">
                        Cancel
                    </Button>
                    <Button type="submit" loading={processing} disabled={editing && !isDirty}>
                        {editing ? 'Save changes' : 'Add rider'}
                    </Button>
                </FormActions>
            </form>
        </>
    );
}
