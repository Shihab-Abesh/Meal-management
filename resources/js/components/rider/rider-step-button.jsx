import { router } from '@inertiajs/react';
import { CircleCheck, Navigation, PackageCheck } from 'lucide-react';
import { useState } from 'react';
import Button from '@/components/ui/button';

const icons = { picked_up: PackageCheck, on_the_way: Navigation, delivered: CircleCheck };
const labels = { picked_up: 'Mark picked up', on_the_way: 'Start trip: on the way', delivered: 'Mark delivered' };

/**
 * The one big button a rider needs: the next step of their delivery.
 */
export default function RiderStepButton({ delivery, size = 'lg', className }) {
    const [processing, setProcessing] = useState(false);
    const step = delivery.next_step;

    if (!step) {
        return null;
    }

    return (
        <Button
            size={size}
            icon={icons[step.value]}
            loading={processing}
            className={className}
            onClick={() =>
                router.post(
                    route('rider.deliveries.status', delivery.id),
                    { status: step.value },
                    {
                        preserveScroll: true,
                        onStart: () => setProcessing(true),
                        onFinish: () => setProcessing(false),
                    },
                )
            }
        >
            {labels[step.value] ?? step.label}
        </Button>
    );
}
