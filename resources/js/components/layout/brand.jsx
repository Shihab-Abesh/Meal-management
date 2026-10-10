import { Link } from '@inertiajs/react';
import Avatar from '@/components/ui/avatar';
import { useApp } from '@/hooks/use-app';
import { cn } from '@/lib/utils';

/**
 * Vendor logo and business name, taken from the vendor profile.
 */
export default function Brand({ href, className, nameClassName }) {
    const { name, logoUrl } = useApp();
    const content = (
        <>
            <Avatar name={name} src={logoUrl} size="sm" rounded="xl" />
            <span className={cn('truncate text-base font-semibold text-slate-900', nameClassName)}>{name}</span>
        </>
    );

    const classes = cn('flex min-w-0 items-center gap-3', className);

    return href ? (
        <Link href={href} className={classes}>
            {content}
        </Link>
    ) : (
        <div className={classes}>{content}</div>
    );
}
