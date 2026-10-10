import { cn } from '@/lib/utils';

export default function Card({ as: Component = 'div', className, children, ...props }) {
    return (
        <Component className={cn('rounded-xl bg-white shadow-sm ring-1 ring-slate-200', className)} {...props}>
            {children}
        </Component>
    );
}

export function CardHeader({ title, description, actions, className }) {
    return (
        <div
            className={cn(
                'flex flex-col gap-3 border-b border-slate-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6',
                className,
            )}
        >
            <div className="min-w-0">
                <h2 className="text-base font-semibold text-slate-900">{title}</h2>
                {description && <p className="mt-0.5 text-sm text-slate-500">{description}</p>}
            </div>
            {actions && <div className="flex shrink-0 items-center gap-2">{actions}</div>}
        </div>
    );
}

export function CardBody({ className, children }) {
    return <div className={cn('px-4 py-5 sm:px-6', className)}>{children}</div>;
}
