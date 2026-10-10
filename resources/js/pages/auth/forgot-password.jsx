import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import Alert from '@/components/ui/alert';
import Button from '@/components/ui/button';
import FormField from '@/components/ui/form-field';
import Input from '@/components/ui/input';

export default function ForgotPassword() {
    const { flash } = usePage();
    const { data, setData, post, processing, errors } = useForm({ email: '' });

    function submit(event) {
        event.preventDefault();
        post(route('password.email'));
    }

    return (
        <>
            <Head title="Forgot password" />

            <h1 className="text-2xl font-bold tracking-tight text-slate-900">Forgot your password?</h1>
            <p className="mt-2 text-sm text-slate-500">
                Enter your account email and we will send you a link to choose a new password.
            </p>

            {flash?.status && (
                <Alert tone="success" className="mt-6">
                    {flash.status}
                </Alert>
            )}

            <form onSubmit={submit} className="mt-8 space-y-5" noValidate>
                <FormField name="email" label="Email address" error={errors.email} required>
                    <Input
                        type="email"
                        autoComplete="email"
                        autoFocus
                        value={data.email}
                        onChange={(event) => setData('email', event.target.value)}
                    />
                </FormField>

                <Button type="submit" size="lg" loading={processing} className="w-full">
                    Email reset link
                </Button>
            </form>

            <Link
                href={route('login')}
                className="mt-6 inline-flex min-h-11 items-center gap-2 text-sm font-medium text-slate-600 hover:text-slate-900"
            >
                <ArrowLeft className="size-4" aria-hidden="true" />
                Back to sign in
            </Link>
        </>
    );
}
