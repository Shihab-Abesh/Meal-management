import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

function toQuery(values) {
    return Object.fromEntries(
        Object.entries(values).filter(([, value]) => value !== '' && value !== null && value !== undefined && value !== false),
    );
}

/**
 * List filters kept in the URL. Changing a filter reloads the page data
 * (back to page 1) while keeping scroll position and local component state.
 *
 * const { values, set, setMany, reset, loading } = useFilters(route('clients.index'), filters);
 * set('search', text, { debounce: true });
 */
export function useFilters(url, initial) {
    const [values, setValues] = useState(initial);
    const [loading, setLoading] = useState(false);
    const timer = useRef(null);

    useEffect(() => () => clearTimeout(timer.current), []);

    function visit(next) {
        router.get(url, toQuery(next), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onStart: () => setLoading(true),
            onFinish: () => setLoading(false),
        });
    }

    function apply(next, debounce) {
        setValues(next);
        clearTimeout(timer.current);

        if (debounce) {
            timer.current = setTimeout(() => visit(next), 350);
        } else {
            visit(next);
        }
    }

    return {
        values,
        loading,
        set: (key, value, { debounce = false } = {}) => apply({ ...values, [key]: value }, debounce),
        setMany: (changes) => apply({ ...values, ...changes }, false),
        reset: (keys) => apply({ ...values, ...Object.fromEntries(keys.map((key) => [key, ''])) }, false),
    };
}
