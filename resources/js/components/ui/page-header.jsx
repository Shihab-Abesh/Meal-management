import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

export default function PageHeader({ title, description, actions, back }) {
    return (
        <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div className="min-w-0">
                {back && (
                    <Link
                        href={back.href}
                        className="mb-2 inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-700 sm:min-h-0 print:hidden"
                    >
                        <ArrowLeft className="size-4" aria-hidden="true" />
                        {back.label}
                    </Link>
                )}
                <h1 className="text-2xl font-bold tracking-tight text-slate-900">{title}</h1>
                {description && <p className="mt-1 text-sm text-slate-500">{description}</p>}
            </div>
            {actions && <div className="flex flex-wrap gap-2 print:hidden">{actions}</div>}
        </div>
    );
}
