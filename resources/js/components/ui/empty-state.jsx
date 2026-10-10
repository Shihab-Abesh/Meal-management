import { Inbox } from 'lucide-react';
import { cn } from '@/lib/utils';

export default function EmptyState({ icon: Icon = Inbox, title, description, action, className }) {
    return (
        <div className={cn('flex flex-col items-center px-4 py-10 text-center', className)}>
            <span className="flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <Icon className="size-6" aria-hidden="true" />
            </span>
            <h3 className="mt-4 text-sm font-semibold text-slate-900">{title}</h3>
            {description && <p className="mt-1 max-w-sm text-sm text-slate-500">{description}</p>}
            {action && <div className="mt-5">{action}</div>}
        </div>
    );
}
