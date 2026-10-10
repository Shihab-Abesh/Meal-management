import { router } from '@inertiajs/react';
import { useState } from 'react';
import Button from '@/components/ui/button';
import ConfirmDialog from '@/components/ui/confirm-dialog';
import Input from '@/components/ui/input';

const RIDER_STEPS = ['picked_up', 'on_the_way', 'delivered'];

const labels = {
    confirmed: 'Confirm order',
    preparing: 'Start preparing',
    ready: 'Mark ready',
    picked_up: 'Picked up',
    on_the_way: 'On the way',
    delivered: 'Delivered',
};

/**
 * Next-step buttons for an order. Kitchen steps need the orders permission,
 * rider steps the deliveries permission; cancelling asks for a reason.
 */
export default function OrderStatusActions({ order, canManageOrders, canManageDeliveries, size = 'md', className }) {
    const [processing, setProcessing] = useState(null);
    const [cancelOpen, setCancelOpen] = useState(false);
    const [reason, setReason] = useState('');

    const allowed = (status) => (RIDER_STEPS.includes(status) ? canManageDeliveries : canManageOrders);
    const steps = order.next_statuses.filter((status) => status.value !== 'cancelled' && allowed(status.value));
    const canCancel = canManageOrders && order.next_statuses.some((status) => status.value === 'cancelled');

    function send(status, extra = {}) {
        router.patch(
            route('orders.status', order.id),
            { status, ...extra },
            {
                preserveScroll: true,
                onStart: () => setProcessing(status),
                onFinish: () => {
                    setProcessing(null);
                    setCancelOpen(false);
                },
            },
        );
    }

    if (steps.length === 0 && !canCancel) {
        return null;
    }

    return (
        <div className={className ?? 'flex flex-wrap gap-2'}>
            {steps.map((status, index) => (
                <Button
                    key={status.value}
                    size={size}
                    variant={index === 0 ? 'primary' : 'secondary'}
                    loading={processing === status.value}
                    disabled={processing !== null}
                    onClick={() => send(status.value)}
                >
                    {labels[status.value] ?? status.label}
                </Button>
            ))}
            {canCancel && (
                <Button size={size} variant="ghost" className="text-red-600 hover:text-red-700" disabled={processing !== null} onClick={() => setCancelOpen(true)}>
                    Cancel order
                </Button>
            )}

            <ConfirmDialog
                open={cancelOpen}
                onClose={() => setCancelOpen(false)}
                onConfirm={() => send('cancelled', { reason })}
                processing={processing === 'cancelled'}
                title={`Cancel order ${order.order_number}?`}
                description="The order and its meals will be cancelled and will not be billed."
                confirmLabel="Cancel order"
                cancelLabel="Keep order"
            >
                <label htmlFor="cancel-reason" className="mt-4 block text-sm font-medium text-slate-700">
                    Reason (optional)
                </label>
                <Input id="cancel-reason" className="mt-1.5" value={reason} onChange={(event) => setReason(event.target.value)} />
            </ConfirmDialog>
        </div>
    );
}
