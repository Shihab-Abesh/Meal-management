import { ChevronDown, LogOut, Store, UserRound } from 'lucide-react';
import Avatar from '@/components/ui/avatar';
import Dropdown, { DropdownDivider, DropdownHeader, DropdownItem } from '@/components/ui/dropdown';
import { useAuth } from '@/hooks/use-auth';

export default function UserMenu() {
    const { user } = useAuth();

    if (!user) {
        return null;
    }

    return (
        <Dropdown
            label="Open account menu"
            buttonClassName="gap-2 p-1.5 hover:bg-slate-100"
            trigger={
                <>
                    <Avatar name={user.name} size="sm" />
                    <span className="hidden max-w-40 truncate text-sm font-medium text-slate-700 md:block">
                        {user.name}
                    </span>
                    <ChevronDown className="hidden size-4 text-slate-400 md:block" aria-hidden="true" />
                </>
            }
        >
            <DropdownHeader>
                <p className="truncate text-sm font-semibold text-slate-900">{user.name}</p>
                <p className="truncate text-xs text-slate-500">{user.email ?? user.phone}</p>
                <p className="mt-1 text-xs font-medium text-primary-700">{user.role_label}</p>
            </DropdownHeader>
            <DropdownDivider />
            <DropdownItem href={route('profile.edit')} icon={UserRound}>
                My profile
            </DropdownItem>
            {user.is_vendor && (
                <DropdownItem href={route('vendor-profile.show')} icon={Store}>
                    Vendor profile
                </DropdownItem>
            )}
            <DropdownDivider />
            <DropdownItem href={route('logout')} method="post" icon={LogOut} tone="danger">
                Sign out
            </DropdownItem>
        </Dropdown>
    );
}
