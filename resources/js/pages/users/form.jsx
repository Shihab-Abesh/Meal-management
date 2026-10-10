import { Head, Link, useForm } from '@inertiajs/react';
import Alert from '@/components/ui/alert';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import Checkbox from '@/components/ui/checkbox';
import ChoiceChips from '@/components/ui/choice-chips';
import FormActions from '@/components/ui/form-actions';
import FormField from '@/components/ui/form-field';
import Input from '@/components/ui/input';
import PageHeader from '@/components/ui/page-header';
import PasswordInput from '@/components/ui/password-input';
import Select from '@/components/ui/select';

/**
 * Add or edit a vendor admin or staff account. Staff get exactly the
 * permissions ticked; admins always have every permission.
 */
export default function UserForm({ account, roles, statuses, permissions, staffDefaults }) {
    const editing = account !== null;
    const { data, setData, post, put, processing, errors, isDirty } = useForm({
        name: account?.name ?? '',
        email: account?.email ?? '',
        phone: account?.phone ?? '',
        password: '',
        role: account?.role ?? 'vendor_staff',
        status: account?.status ?? 'active',
        permissions: account?.permissions ?? staffDefaults,
    });
    const isSelf = Boolean(account?.is_self);
    const groups = [
        { title: 'Operational', description: 'Day-to-day work. Staff get these by default.', items: permissions.filter((permission) => permission.operational) },
        { title: 'Critical and financial', description: 'Only grant to people who need them.', items: permissions.filter((permission) => !permission.operational) },
    ];

    function toggle(value, checked) {
        setData('permissions', checked ? [...data.permissions, value] : data.permissions.filter((item) => item !== value));
    }

    function submit(event) {
        event.preventDefault();

        if (editing) {
            put(route('users.update', account.id), { preserveScroll: true });
        } else {
            post(route('users.store'), { preserveScroll: true });
        }
    }

    return (
        <>
            <Head title={editing ? `Edit ${account.name}` : 'Add user'} />

            <PageHeader
                title={editing ? 'Edit user' : 'Add user'}
                description={editing ? account.name : 'Vendor admins and staff sign in with their email or phone number.'}
                back={{ href: route('users.index'), label: 'Users' }}
            />

            {errors.role && (
                <Alert tone="error" className="mb-6">
                    {errors.role}
                </Alert>
            )}

            <form onSubmit={submit} noValidate className="space-y-6">
                <Card>
                    <CardHeader title="Account" />
                    <CardBody className="grid gap-5 sm:grid-cols-2">
                        <FormField name="name" label="Name" error={errors.name} required className="sm:col-span-2">
                            <Input autoComplete="off" value={data.name} onChange={(event) => setData('name', event.target.value)} />
                        </FormField>
                        <FormField name="email" label="Email" error={errors.email}>
                            <Input type="email" autoComplete="off" value={data.email} onChange={(event) => setData('email', event.target.value)} />
                        </FormField>
                        <FormField name="phone" label="Phone" hint="Email, phone or both. Either can be used to sign in." error={errors.phone}>
                            <Input type="tel" inputMode="tel" autoComplete="off" value={data.phone} onChange={(event) => setData('phone', event.target.value)} />
                        </FormField>
                        <FormField
                            name="password"
                            label={editing ? 'New password' : 'Password'}
                            hint={editing ? 'Leave empty to keep the current password.' : 'At least 8 characters with letters and numbers.'}
                            error={errors.password}
                            required={!editing}
                        >
                            <PasswordInput autoComplete="new-password" value={data.password} onChange={(event) => setData('password', event.target.value)} />
                        </FormField>
                        <FormField name="status" label="Status" error={errors.status} hint={isSelf ? 'You cannot deactivate your own account.' : undefined} required>
                            <Select options={statuses} value={data.status} disabled={isSelf} onChange={(event) => setData('status', event.target.value)} />
                        </FormField>
                    </CardBody>
                </Card>

                <Card>
                    <CardHeader title="Role and permissions" />
                    <CardBody className="space-y-5">
                        <FormField name="role" label="Role" hint={isSelf ? 'You cannot change your own role.' : undefined} required>
                            <ChoiceChips options={roles} value={data.role} onChange={(value) => !isSelf && setData('role', value)} />
                        </FormField>

                        {data.role === 'vendor_admin' ? (
                            <Alert tone="info">Vendor admins have every permission, including billing, settings and user management.</Alert>
                        ) : (
                            <div className="grid gap-6 md:grid-cols-2">
                                {groups.map((group) => (
                                    <fieldset key={group.title}>
                                        <legend className="text-sm font-semibold text-slate-900">{group.title}</legend>
                                        <p className="text-xs text-slate-500">{group.description}</p>
                                        <div className="mt-3 space-y-1">
                                            {group.items.map((permission) => (
                                                <Checkbox
                                                    key={permission.value}
                                                    id={`permission-${permission.value}`}
                                                    label={permission.label}
                                                    checked={data.permissions.includes(permission.value)}
                                                    onChange={(event) => toggle(permission.value, event.target.checked)}
                                                />
                                            ))}
                                        </div>
                                    </fieldset>
                                ))}
                            </div>
                        )}
                        {errors.permissions && <p className="text-sm text-red-600">{errors.permissions}</p>}
                    </CardBody>
                </Card>

                <FormActions>
                    <Button as={Link} href={route('users.index')} variant="secondary">
                        Cancel
                    </Button>
                    <Button type="submit" loading={processing} disabled={editing && !isDirty}>
                        {editing ? 'Save changes' : 'Add user'}
                    </Button>
                </FormActions>
            </form>
        </>
    );
}
