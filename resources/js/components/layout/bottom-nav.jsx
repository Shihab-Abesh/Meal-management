import { Link } from '@inertiajs/react';
import { Menu } from 'lucide-react';
import NavIcon from '@/components/layout/nav-icon';
import { cn } from '@/lib/utils';

const MAX_ITEMS = 4;

const itemClasses =
    'flex min-h-14 flex-1 flex-col items-center justify-center gap-1 px-1 text-[11px] font-medium transition-colors';

/**
 * Thumb-friendly tab bar for phones and tablets. Shows the navigation items
 * flagged `mobile` on the server, plus a "Menu" button for everything else.
 */
export default function BottomNav({ groups, onOpenMenu }) {
    const items = groups
        .flatMap((group) => group.items)
        .filter((item) => item.mobile)
        .slice(0, MAX_ITEMS);

    return (
        <nav
            aria-label="Quick navigation"
            className="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 pb-safe backdrop-blur lg:hidden print:hidden"
        >
            <div className="mx-auto flex max-w-lg">
                {items.map((item) => (
                    <Link
                        key={item.href}
                        href={item.href}
                        aria-current={item.active ? 'page' : undefined}
                        className={cn(itemClasses, item.active ? 'text-primary-600' : 'text-slate-500 hover:text-slate-800')}
                    >
                        <NavIcon name={item.icon} className="size-6" />
                        <span className="max-w-full truncate">{item.label}</span>
                    </Link>
                ))}
                <button
                    type="button"
                    onClick={onOpenMenu}
                    className={cn(itemClasses, 'text-slate-500 hover:text-slate-800')}
                >
                    <Menu className="size-6" aria-hidden="true" />
                    <span>Menu</span>
                </button>
            </div>
        </nav>
    );
}
