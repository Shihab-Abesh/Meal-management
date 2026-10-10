import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { formatNumber } from '@/lib/format';
import { cn } from '@/lib/utils';

const navClasses =
    'inline-flex h-11 items-center gap-1 rounded-lg px-3 text-sm font-medium text-slate-700 ring-1 ring-slate-300 ring-inset hover:bg-slate-50';
const disabledClasses = 'pointer-events-none opacity-40';

/**
 * Pagination for a Laravel resource collection (`meta` + `links`).
 * Phones get large previous/next buttons; wider screens add page numbers.
 */
export default function Pagination({ meta, links, className }) {
    if (!meta || meta.total === 0) {
        return null;
    }

    // meta.links = [previous, 1, 2, ..., next]; keep only the numbered pages.
    const pages = (meta.links ?? []).slice(1, -1);
    const summary = (
        <p className="text-sm text-slate-500">
            Showing <span className="font-medium text-slate-700">{formatNumber(meta.from)}</span>–
            <span className="font-medium text-slate-700">{formatNumber(meta.to)}</span> of{' '}
            <span className="font-medium text-slate-700">{formatNumber(meta.total)}</span>
        </p>
    );

    if (meta.last_page <= 1) {
        return <div className={cn('px-4 py-3 sm:px-6', className)}>{summary}</div>;
    }

    return (
        <nav
            aria-label="Pagination"
            className={cn('flex items-center justify-between gap-3 border-t border-slate-100 px-4 py-3 sm:px-6', className)}
        >
            <div className="hidden md:block">{summary}</div>

            <PageLink href={links?.prev} label="Previous page" className={cn(navClasses, 'md:hidden')}>
                <ChevronLeft className="size-4" aria-hidden="true" />
                Prev
            </PageLink>
            <p className="text-sm text-slate-600 md:hidden">
                Page {meta.current_page} of {meta.last_page}
            </p>
            <PageLink href={links?.next} label="Next page" className={cn(navClasses, 'md:hidden')}>
                Next
                <ChevronRight className="size-4" aria-hidden="true" />
            </PageLink>

            <div className="hidden items-center gap-1 md:flex">
                <PageLink href={links?.prev} label="Previous page" className={cn(navClasses, 'px-2')}>
                    <ChevronLeft className="size-4" aria-hidden="true" />
                </PageLink>
                {pages.map((page, index) =>
                    page.url ? (
                        <Link
                            key={`${page.label}-${index}`}
                            href={page.url}
                            preserveScroll
                            preserveState
                            aria-current={page.active ? 'page' : undefined}
                            className={cn(
                                'inline-flex h-11 min-w-11 items-center justify-center rounded-lg px-2 text-sm font-medium',
                                page.active ? 'bg-primary-600 text-white' : 'text-slate-700 hover:bg-slate-100',
                            )}
                        >
                            {page.label}
                        </Link>
                    ) : (
                        <span key={`gap-${index}`} className="px-2 text-sm text-slate-400">
                            …
                        </span>
                    ),
                )}
                <PageLink href={links?.next} label="Next page" className={cn(navClasses, 'px-2')}>
                    <ChevronRight className="size-4" aria-hidden="true" />
                </PageLink>
            </div>
        </nav>
    );
}

function PageLink({ href, label, className, children }) {
    if (!href) {
        return (
            <span aria-disabled="true" aria-label={label} className={cn(className, disabledClasses)}>
                {children}
            </span>
        );
    }

    return (
        <Link href={href} preserveScroll preserveState aria-label={label} className={className}>
            {children}
        </Link>
    );
}
