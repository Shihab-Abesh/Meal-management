/**
 * Join class names, skipping falsy values: cn('a', cond && 'b').
 */
export function cn(...classes) {
    return classes.flat().filter(Boolean).join(' ');
}

/**
 * "Abdullah Al Mamun" -> "AM"
 */
export function initials(name = '') {
    const parts = name.trim().split(/\s+/).filter(Boolean);

    if (parts.length === 0) {
        return '?';
    }

    const first = parts[0][0];
    const last = parts.length > 1 ? parts[parts.length - 1][0] : '';

    return (first + last).toUpperCase();
}
