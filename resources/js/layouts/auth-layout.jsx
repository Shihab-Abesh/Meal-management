import { ClipboardCheck, Truck, Utensils } from 'lucide-react';
import Avatar from '@/components/ui/avatar';
import Toaster from '@/components/ui/toaster';
import { useApp } from '@/hooks/use-app';

const highlights = [
    { icon: Utensils, text: 'Daily lunch and dinner counts for every client office' },
    { icon: Truck, text: 'Orders, riders and deliveries in one place' },
    { icon: ClipboardCheck, text: 'Accurate billing and reports without the notebook' },
];

/**
 * Guest screens (sign in, password reset). A brand panel sits beside the
 * form on large screens; phones get the form only.
 */
export default function AuthLayout({ children }) {
    const { name, logoUrl } = useApp();

    return (
        <div className="flex min-h-dvh bg-white">
            <aside className="relative hidden w-[45%] max-w-2xl flex-col justify-between overflow-hidden bg-primary-800 p-12 text-white lg:flex">
                <div
                    className="pointer-events-none absolute -top-24 -right-24 size-96 rounded-full bg-primary-600/40 blur-3xl"
                    aria-hidden="true"
                />
                <div
                    className="pointer-events-none absolute -bottom-32 -left-16 size-96 rounded-full bg-primary-950/40 blur-3xl"
                    aria-hidden="true"
                />

                <div className="relative flex items-center gap-3">
                    <Avatar name={name} src={logoUrl} size="md" rounded="xl" className="bg-white" />
                    <span className="text-lg font-semibold">{name}</span>
                </div>

                <div className="relative">
                    <h2 className="text-3xl leading-tight font-bold">Office meals, managed simply.</h2>
                    <ul className="mt-8 space-y-4">
                        {highlights.map(({ icon: Icon, text }) => (
                            <li key={text} className="flex items-center gap-3 text-primary-50">
                                <span className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-white/10">
                                    <Icon className="size-5" aria-hidden="true" />
                                </span>
                                {text}
                            </li>
                        ))}
                    </ul>
                </div>

                <p className="relative text-sm text-primary-200">
                    &copy; {new Date().getFullYear()} {name}
                </p>
            </aside>

            <main className="flex flex-1 flex-col justify-center px-4 py-10 sm:px-6 lg:px-16">
                <div className="mx-auto w-full max-w-sm">
                    <div className="mb-8 flex items-center gap-3 lg:hidden">
                        <Avatar name={name} src={logoUrl} size="md" rounded="xl" />
                        <span className="text-lg font-semibold text-slate-900">{name}</span>
                    </div>
                    {children}
                </div>
            </main>

            <Toaster />
        </div>
    );
}
