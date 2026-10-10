import { Link } from '@inertiajs/react';
import NavIcon from '@/components/layout/nav-icon';
import { cn } from '@/lib/utils';

/**
 * Grouped navigation built by App\Support\Navigation on the server.
 */
export default function SidebarNav({ groups }) {
    return (
        <nav aria-label="Main" className="space-y-6">
            {groups.map((group, index) => (
                <div key={group.label ?? index}>
                    {group.label && (
                        <p className="mb-2 px-3 text-xs font-semibold tracking-wider text-slate-400 uppercase">
                            {group.label}
                        </p>
                    )}
                    <ul className="space-y-1">
                        {group.items.map((item) => (
                            <li key={item.href}>
                                <Link
                                    href={item.href}
                                    aria-current={item.active ? 'page' : undefined}
                                    className={cn(
                                        'flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-medium transition-colors',
                                        item.active
                                            ? 'bg-primary-50 text-primary-700'
                                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
                                    )}
                                >
                                    <NavIcon
                                        name={item.icon}
                                        className={cn('size-5 shrink-0', item.active ? 'text-primary-600' : 'text-slate-400')}
                                    />
                                    {item.label}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </div>
            ))}
        </nav>
    );
}
