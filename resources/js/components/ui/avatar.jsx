import { cn, initials } from '@/lib/utils';

const sizes = {
    sm: 'size-8 text-xs',
    md: 'size-10 text-sm',
    lg: 'size-16 text-lg',
    xl: 'size-20 text-xl',
};

export default function Avatar({ name, src, size = 'md', rounded = 'full', className }) {
    const shape = rounded === 'full' ? 'rounded-full' : 'rounded-xl';

    if (src) {
        return (
            <img
                src={src}
                alt={name ? `${name} logo` : ''}
                className={cn('shrink-0 bg-white object-contain', shape, sizes[size], className)}
            />
        );
    }

    return (
        <span
            className={cn(
                'inline-flex shrink-0 items-center justify-center bg-primary-100 font-semibold text-primary-700',
                shape,
                sizes[size],
                className,
            )}
            aria-hidden="true"
        >
            {initials(name)}
        </span>
    );
}
