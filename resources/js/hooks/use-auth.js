import { usePage } from '@inertiajs/react';

/**
 * The signed-in user and a permission check that mirrors the backend.
 * Only use this to hide UI; the server always enforces access itself.
 */
export function useAuth() {
    const { auth } = usePage().props;
    const user = auth?.user ?? null;

    const can = (permission) => Boolean(user?.permissions?.includes(permission));

    return { user, can };
}
