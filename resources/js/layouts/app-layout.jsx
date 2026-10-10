import { Link, router, usePage } from '@inertiajs/react';
import { LogOut } from 'lucide-react';
import { useEffect, useState } from 'react';
import BottomNav from '@/components/layout/bottom-nav';
import Brand from '@/components/layout/brand';
import SidebarNav from '@/components/layout/sidebar-nav';
import Topbar from '@/components/layout/topbar';
import Avatar from '@/components/ui/avatar';
import Drawer from '@/components/ui/drawer';
import Toaster from '@/components/ui/toaster';
import { useAuth } from '@/hooks/use-auth';

/**
 * Signed-in shell: fixed sidebar on desktop; top bar, slide-out drawer and
 * bottom tab bar on phones and tablets.
 */
export default function AppLayout({ children }) {
    const { navigation = [] } = usePage().props;
    const { user } = useAuth();
    const [menuOpen, setMenuOpen] = useState(false);

    // Close the mobile drawer whenever the user navigates.
    useEffect(() => router.on('navigate', () => setMenuOpen(false)), []);

    return (
        <div className="min-h-dvh">
            <a
                href="#main"
                className="sr-only z-50 rounded-lg bg-white px-4 py-2 font-medium text-primary-700 shadow focus:not-sr-only focus:fixed focus:top-3 focus:left-3"
            >
                Skip to content
            </a>

            <aside className="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-slate-200 bg-white lg:flex print:hidden">
                <div className="flex h-16 shrink-0 items-center px-5">
                    <Brand href={route('dashboard')} />
                </div>
                <div className="flex-1 overflow-y-auto px-3 py-4">
                    <SidebarNav groups={navigation} />
                </div>
            </aside>

            <Drawer open={menuOpen} onClose={() => setMenuOpen(false)} side="left" title="Navigation" hideTitle>
                <div className="flex h-full flex-col">
                    <div className="flex h-16 shrink-0 items-center px-5 pr-14">
                        <Brand />
                    </div>
                    <div className="flex-1 overflow-y-auto px-3 py-4">
                        <SidebarNav groups={navigation} />
                    </div>
                    {user && (
                        <div className="border-t border-slate-200 p-4 pb-safe">
                            <div className="flex items-center gap-3">
                                <Avatar name={user.name} />
                                <div className="min-w-0 flex-1">
                                    <p className="truncate text-sm font-semibold text-slate-900">{user.name}</p>
                                    <p className="truncate text-xs text-slate-500">{user.role_label}</p>
                                </div>
                            </div>
                            <Link
                                href={route('logout')}
                                method="post"
                                as="button"
                                className="mt-3 flex min-h-11 w-full items-center justify-center gap-2 rounded-lg text-sm font-semibold text-red-600 ring-1 ring-red-200 ring-inset hover:bg-red-50"
                            >
                                <LogOut className="size-4" aria-hidden="true" />
                                Sign out
                            </Link>
                        </div>
                    )}
                </div>
            </Drawer>

            <div className="lg:pl-64 print:pl-0">
                <Topbar onOpenMenu={() => setMenuOpen(true)} />
                <main id="main" className="mx-auto w-full max-w-7xl px-4 pt-6 pb-28 sm:px-6 lg:px-8 lg:pb-12 print:max-w-none print:p-0">
                    {children}
                </main>
            </div>

            <BottomNav groups={navigation} onOpenMenu={() => setMenuOpen(true)} />
            <Toaster />
        </div>
    );
}
