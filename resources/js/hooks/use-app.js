import { usePage } from '@inertiajs/react';
import { formatCurrency } from '@/lib/format';

/**
 * Vendor branding and business settings shared with every page.
 */
export function useApp() {
    const { app } = usePage().props;

    return {
        name: app?.name ?? '',
        logoUrl: app?.logo_url ?? null,
        currency: app?.currency ?? { code: '', symbol: '' },
        money: (value) => formatCurrency(value, app?.currency?.symbol ?? ''),
    };
}
