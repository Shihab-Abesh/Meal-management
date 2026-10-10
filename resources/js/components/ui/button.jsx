import Spinner from '@/components/ui/spinner';
import { cn } from '@/lib/utils';

const variants = {
    primary:
        'bg-primary-600 text-white shadow-sm hover:bg-primary-700 focus-visible:outline-primary-600 active:bg-primary-800',
    secondary:
        'bg-white text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 focus-visible:outline-primary-600 active:bg-slate-100',
    ghost: 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-primary-600',
    danger: 'bg-red-600 text-white shadow-sm hover:bg-red-700 focus-visible:outline-red-600 active:bg-red-800',
};

// md and lg meet the 44px minimum touch target.
const sizes = {
    sm: 'h-9 gap-1.5 px-3 text-sm',
    md: 'h-11 gap-2 px-4 text-sm',
    lg: 'h-12 gap-2 px-5 text-base',
    icon: 'size-11',
};

/**
 * Renders a <button> by default; pass `as={Link}` (with href) for navigation.
 * While `loading`, the button is disabled so forms cannot be submitted twice.
 */
export default function Button({
    as: Component = 'button',
    variant = 'primary',
    size = 'md',
    loading = false,
    disabled = false,
    icon: Icon,
    type,
    className,
    children,
    ...props
}) {
    const isButton = Component === 'button';

    return (
        <Component
            type={isButton ? (type ?? 'button') : undefined}
            disabled={isButton ? disabled || loading : undefined}
            aria-busy={loading || undefined}
            className={cn(
                'inline-flex shrink-0 select-none items-center justify-center rounded-lg font-semibold whitespace-nowrap transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-60',
                variants[variant],
                sizes[size],
                className,
            )}
            {...props}
        >
            {loading ? <Spinner className="size-4" /> : Icon && <Icon className="size-4 shrink-0" aria-hidden="true" />}
            {children}
        </Component>
    );
}
