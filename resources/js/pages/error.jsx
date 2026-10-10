import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Clock, FileQuestion, LockKeyhole, RefreshCw, ServerCrash, Wrench } from 'lucide-react';
import Button from '@/components/ui/button';

const pages = {
    403: {
        icon: LockKeyhole,
        title: 'You do not have access to this page',
        message: 'Your account is not allowed to open it. If you think this is a mistake, ask your administrator.',
    },
    404: {
        icon: FileQuestion,
        title: 'Page not found',
        message: 'The page may have been moved or deleted, or the link is wrong.',
    },
    419: {
        icon: Clock,
        title: 'This page has expired',
        message: 'You were away for a while. Reload the page and try again.',
    },
    429: {
        icon: Clock,
        title: 'Too many requests',
        message: 'You have tried this too often in a short time. Please wait a minute and try again.',
    },
    500: {
        icon: ServerCrash,
        title: 'Something went wrong',
        message: 'An unexpected error happened on our side. Please try again in a moment. If it keeps happening, contact your administrator.',
    },
    503: {
        icon: Wrench,
        title: 'Down for maintenance',
        message: 'The app is being updated. Please check back in a few minutes.',
    },
};

/**
 * Friendly error screen for 403, 404, 419, 429, 500 and 503. Rendered
 * without the app shell because the visitor may not be signed in.
 */
export default function ErrorPage({ status }) {
    const page = pages[status] ?? pages[500];
    const Icon = page.icon;

    return (
        <>
            <Head title={page.title} />

            <main className="flex min-h-dvh items-center justify-center px-4 py-12">
                <div className="w-full max-w-md text-center">
                    <span className="mx-auto flex size-16 items-center justify-center rounded-2xl bg-primary-50 text-primary-600">
                        <Icon className="size-8" aria-hidden="true" />
                    </span>
                    <p className="mt-6 text-sm font-semibold text-primary-700">Error {status}</p>
                    <h1 className="mt-2 text-2xl font-bold tracking-tight text-slate-900">{page.title}</h1>
                    <p className="mt-3 text-sm text-slate-600">{page.message}</p>
                    <div className="mt-8 flex flex-col justify-center gap-2 sm:flex-row">
                        {[419, 429, 500, 503].includes(status) ? (
                            <Button icon={RefreshCw} onClick={() => window.location.reload()}>
                                Try again
                            </Button>
                        ) : (
                            <Button variant="secondary" icon={ArrowLeft} onClick={() => window.history.back()}>
                                Go back
                            </Button>
                        )}
                        <Button as={Link} href={route('dashboard')} variant={[419, 429, 500, 503].includes(status) ? 'secondary' : 'primary'}>
                            Go to dashboard
                        </Button>
                    </div>
                </div>
            </main>
        </>
    );
}
