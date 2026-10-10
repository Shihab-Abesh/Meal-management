import { Menu, MenuButton, MenuItem, MenuItems, MenuSeparator } from '@headlessui/react';
import { Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';

/**
 * Accessible dropdown menu (keyboard, focus and outside-click handled by
 * Headless UI). `trigger` is the content of the toggle button.
 */
export default function Dropdown({ trigger, label, align = 'end', className, buttonClassName, children }) {
    return (
        <Menu as="div" className={cn('relative', className)}>
            <MenuButton
                aria-label={label}
                className={cn(
                    'flex items-center rounded-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600',
                    buttonClassName,
                )}
            >
                {trigger}
            </MenuButton>
            <MenuItems
                transition
                anchor={`bottom ${align}`}
                className="z-50 w-60 origin-top rounded-xl bg-white p-1.5 shadow-lg ring-1 ring-slate-900/10 transition duration-100 ease-out [--anchor-gap:8px] focus:outline-none data-closed:scale-95 data-closed:opacity-0"
            >
                {children}
            </MenuItems>
        </Menu>
    );
}

const itemClasses =
    'flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm text-slate-700 data-focus:bg-slate-100 data-focus:text-slate-900';

/**
 * A menu entry. With `href` it renders an Inertia <Link> (pass `method="post"`
 * for actions such as sign out); otherwise a button with `onClick`.
 */
export function DropdownItem({ href, method, icon: Icon, tone, onClick, children }) {
    const classes = cn(itemClasses, tone === 'danger' && 'text-red-600 data-focus:bg-red-50 data-focus:text-red-700');
    const content = (
        <>
            {Icon && <Icon className="size-4 shrink-0 opacity-70" aria-hidden="true" />}
            {children}
        </>
    );

    return (
        <MenuItem>
            {href ? (
                <Link href={href} method={method} as={method && method !== 'get' ? 'button' : 'a'} className={classes}>
                    {content}
                </Link>
            ) : (
                <button type="button" onClick={onClick} className={classes}>
                    {content}
                </button>
            )}
        </MenuItem>
    );
}

export function DropdownHeader({ children }) {
    return <div className="px-3 py-2.5">{children}</div>;
}

export function DropdownDivider() {
    return <MenuSeparator className="my-1.5 h-px bg-slate-100" />;
}
