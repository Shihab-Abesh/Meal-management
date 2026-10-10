import { CalendarDays, Menu } from 'lucide-react';
import Brand from '@/components/layout/brand';
import NotificationBell from '@/components/notifications/notification-bell';
import UserMenu from '@/components/layout/user-menu';
import { formatLongDate } from '@/lib/format';

export default function Topbar({ onOpenMenu }) {
    return (
        <header className="sticky top-0 z-30 border-b print:hidden border-slate-200 bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/75">
            <div className="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                <button
                    type="button"
                    onClick={onOpenMenu}
                    className="-ml-2 flex size-11 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-primary-600 lg:hidden"
                >
                    <span className="sr-only">Open navigation</span>
                    <Menu className="size-6" aria-hidden="true" />
                </button>

                <Brand href={route('dashboard')} className="lg:hidden" nameClassName="text-sm sm:text-base" />

                <div className="ml-auto flex items-center gap-2 sm:gap-4">
                    <p className="hidden items-center gap-2 text-sm text-slate-500 sm:flex">
                        <CalendarDays className="size-4" aria-hidden="true" />
                        {formatLongDate(new Date())}
                    </p>
                    <div className="flex items-center gap-1">
                        <NotificationBell />
                        <UserMenu />
                    </div>
                </div>
            </div>
        </header>
    );
}
