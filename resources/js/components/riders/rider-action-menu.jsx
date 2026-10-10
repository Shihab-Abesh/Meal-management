import { router } from '@inertiajs/react';
import { Eye, Pencil, Power, PowerOff } from 'lucide-react';
import { useState } from 'react';
import ActionMenu from '@/components/ui/action-menu';
import ConfirmDialog from '@/components/ui/confirm-dialog';
import { DropdownItem } from '@/components/ui/dropdown';

export default function RiderActionMenu({ rider, showView = false, showEdit = true }) {
    const [confirmOpen, setConfirmOpen] = useState(false);
    const [processing, setProcessing] = useState(false);

    function setStatus(status) {
        router.patch(
            route('riders.status', rider.id),
            { status },
            {
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => {
                    setProcessing(false);
                    setConfirmOpen(false);
                },
            },
        );
    }

    return (
        <>
            <ActionMenu label={`Actions for ${rider.name}`}>
                {showView && (
                    <DropdownItem href={route('riders.show', rider.id)} icon={Eye}>
                        View details
                    </DropdownItem>
                )}
                {showEdit && (
                    <DropdownItem href={route('riders.edit', rider.id)} icon={Pencil}>
                        Edit
                    </DropdownItem>
                )}
                {rider.status === 'active' ? (
                    <DropdownItem icon={PowerOff} tone="danger" onClick={() => setConfirmOpen(true)}>
                        Deactivate
                    </DropdownItem>
                ) : (
                    <DropdownItem icon={Power} onClick={() => setStatus('active')}>
                        Activate
                    </DropdownItem>
                )}
            </ActionMenu>

            <ConfirmDialog
                open={confirmOpen}
                onClose={() => setConfirmOpen(false)}
                onConfirm={() => setStatus('inactive')}
                processing={processing}
                title="Deactivate rider?"
                description={`${rider.name} can no longer be assigned deliveries${rider.has_login ? ' and is signed out of the rider app' : ''}. Their delivery history is kept.`}
                confirmLabel="Deactivate"
            />
        </>
    );
}
