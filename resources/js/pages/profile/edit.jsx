import { Head, useForm } from '@inertiajs/react';
import Avatar from '@/components/ui/avatar';
import Badge from '@/components/ui/badge';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import FormField from '@/components/ui/form-field';
import Input from '@/components/ui/input';
import PageHeader from '@/components/ui/page-header';
import PasswordInput from '@/components/ui/password-input';
import { formatDateTime } from '@/lib/format';

export default function EditProfile({ account }) {
    return (
        <>
            <Head title="My profile" />

            <PageHeader title="My profile" description="Your personal account details and password." />

            <div className="grid gap-6 lg:grid-cols-3">
                <Card className="h-fit">
                    <CardBody className="flex items-center gap-4 lg:flex-col lg:text-center">
                        <Avatar name={account.name} size="lg" />
                        <div className="min-w-0">
                            <p className="truncate text-lg font-semibold text-slate-900">{account.name}</p>
                            <Badge tone="violet" className="mt-1">
                                {account.role_label}
                            </Badge>
                            <p className="mt-2 text-xs text-slate-500">
                                Last sign in: {account.last_login_at ? formatDateTime(account.last_login_at) : 'First visit'}
                            </p>
                        </div>
                    </CardBody>
                </Card>

                <div className="space-y-6 lg:col-span-2">
                    <AccountForm account={account} />
                    <PasswordForm />
                </div>
            </div>
        </>
    );
}

function AccountForm({ account }) {
    const { data, setData, put, processing, errors, isDirty } = useForm({
        name: account.name ?? '',
        email: account.email ?? '',
        phone: account.phone ?? '',
    });

    function submit(event) {
        event.preventDefault();
        put(route('profile.update'), { preserveScroll: true });
    }

    return (
        <Card as="form" onSubmit={submit} noValidate>
            <CardHeader
                title="Account information"
                description="You can sign in with either your email address or phone number."
            />
            <CardBody className="space-y-5">
                <FormField name="name" label="Full name" error={errors.name} required>
                    <Input
                        autoComplete="name"
                        value={data.name}
                        onChange={(event) => setData('name', event.target.value)}
                    />
                </FormField>
                <div className="grid gap-5 sm:grid-cols-2">
                    <FormField name="email" label="Email address" error={errors.email}>
                        <Input
                            type="email"
                            autoComplete="email"
                            value={data.email}
                            onChange={(event) => setData('email', event.target.value)}
                        />
                    </FormField>
                    <FormField name="phone" label="Phone number" error={errors.phone}>
                        <Input
                            type="tel"
                            inputMode="tel"
                            autoComplete="tel"
                            value={data.phone}
                            onChange={(event) => setData('phone', event.target.value)}
                        />
                    </FormField>
                </div>
            </CardBody>
            <div className="flex justify-end border-t border-slate-100 px-4 py-4 sm:px-6">
                <Button type="submit" loading={processing} disabled={!isDirty} className="w-full sm:w-auto">
                    Save changes
                </Button>
            </div>
        </Card>
    );
}

function PasswordForm() {
    const { data, setData, put, processing, errors, reset } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    function submit(event) {
        event.preventDefault();
        put(route('profile.password.update'), {
            preserveScroll: true,
            onSuccess: () => reset(),
            onError: () => reset('password', 'password_confirmation'),
        });
    }

    return (
        <Card as="form" onSubmit={submit} noValidate>
            <CardHeader title="Change password" description="Use at least 8 characters with letters and numbers." />
            <CardBody className="space-y-5">
                <FormField name="current_password" label="Current password" error={errors.current_password} required>
                    <PasswordInput
                        autoComplete="current-password"
                        value={data.current_password}
                        onChange={(event) => setData('current_password', event.target.value)}
                    />
                </FormField>
                <div className="grid gap-5 sm:grid-cols-2">
                    <FormField name="password" label="New password" error={errors.password} required>
                        <PasswordInput
                            autoComplete="new-password"
                            value={data.password}
                            onChange={(event) => setData('password', event.target.value)}
                        />
                    </FormField>
                    <FormField
                        name="password_confirmation"
                        label="Confirm new password"
                        error={errors.password_confirmation}
                        required
                    >
                        <PasswordInput
                            autoComplete="new-password"
                            value={data.password_confirmation}
                            onChange={(event) => setData('password_confirmation', event.target.value)}
                        />
                    </FormField>
                </div>
            </CardBody>
            <div className="flex justify-end border-t border-slate-100 px-4 py-4 sm:px-6">
                <Button type="submit" loading={processing} className="w-full sm:w-auto">
                    Update password
                </Button>
            </div>
        </Card>
    );
}
