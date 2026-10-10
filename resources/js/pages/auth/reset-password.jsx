import { Head, useForm } from '@inertiajs/react';
import Button from '@/components/ui/button';
import FormField from '@/components/ui/form-field';
import Input from '@/components/ui/input';
import PasswordInput from '@/components/ui/password-input';

export default function ResetPassword({ token, email }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        token,
        email,
        password: '',
        password_confirmation: '',
    });

    function submit(event) {
        event.preventDefault();
        post(route('password.store'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    }

    return (
        <>
            <Head title="Reset password" />

            <h1 className="text-2xl font-bold tracking-tight text-slate-900">Choose a new password</h1>
            <p className="mt-2 text-sm text-slate-500">Use at least 8 characters with letters and numbers.</p>

            <form onSubmit={submit} className="mt-8 space-y-5" noValidate>
                <FormField name="email" label="Email address" error={errors.email} required>
                    <Input
                        type="email"
                        autoComplete="email"
                        value={data.email}
                        onChange={(event) => setData('email', event.target.value)}
                    />
                </FormField>

                <FormField name="password" label="New password" error={errors.password} required>
                    <PasswordInput
                        autoComplete="new-password"
                        autoFocus
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

                <Button type="submit" size="lg" loading={processing} className="w-full">
                    Reset password
                </Button>
            </form>
        </>
    );
}
