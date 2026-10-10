import { Head, Link, useForm, usePage } from '@inertiajs/react';
import Alert from '@/components/ui/alert';
import Button from '@/components/ui/button';
import Checkbox from '@/components/ui/checkbox';
import FormField from '@/components/ui/form-field';
import Input from '@/components/ui/input';
import PasswordInput from '@/components/ui/password-input';

export default function Login({ canResetPassword }) {
    const { flash } = usePage();
    const { data, setData, post, processing, errors, reset } = useForm({
        login: '',
        password: '',
        remember: false,
    });

    function submit(event) {
        event.preventDefault();
        post(route('login.store'), {
            onFinish: () => reset('password'),
        });
    }

    return (
        <>
            <Head title="Sign in" />

            <h1 className="text-2xl font-bold tracking-tight text-slate-900">Sign in</h1>
            <p className="mt-2 text-sm text-slate-500">Use the email address or phone number on your account.</p>

            {flash?.status && (
                <Alert tone="success" className="mt-6">
                    {flash.status}
                </Alert>
            )}

            <form onSubmit={submit} className="mt-8 space-y-5" noValidate>
                <FormField name="login" label="Email or phone" error={errors.login} required>
                    <Input
                        type="text"
                        inputMode="email"
                        autoComplete="username"
                        autoCapitalize="none"
                        autoFocus
                        value={data.login}
                        onChange={(event) => setData('login', event.target.value)}
                        placeholder="you@example.com or 017XXXXXXXX"
                    />
                </FormField>

                <FormField
                    name="password"
                    label="Password"
                    error={errors.password}
                    required
                    action={
                        canResetPassword && (
                            <Link
                                href={route('password.request')}
                                className="text-sm font-medium text-primary-700 hover:text-primary-800"
                            >
                                Forgot password?
                            </Link>
                        )
                    }
                >
                    <PasswordInput
                        autoComplete="current-password"
                        value={data.password}
                        onChange={(event) => setData('password', event.target.value)}
                    />
                </FormField>

                <Checkbox
                    id="remember"
                    name="remember"
                    label="Keep me signed in"
                    checked={data.remember}
                    onChange={(event) => setData('remember', event.target.checked)}
                />

                <Button type="submit" size="lg" loading={processing} className="w-full">
                    Sign in
                </Button>
            </form>
        </>
    );
}
