import { router } from '@inertiajs/react';
import { Eye, Pencil, Power, PowerOff } from 'lucide-react';
import { useState } from 'react';
import ActionMenu from '@/components/ui/action-menu';
import ConfirmDialog from '@/components/ui/confirm-dialog';
import { DropdownItem } from '@/components/ui/dropdown';

export default function EmployeeActionMenu({ employee, showView = false, showEdit = true }) {
    const [confirmOpen, setConfirmOpen] = useState(false);
    const [processing, setProcessing] = useState(false);

    function setStatus(status) {
        router.patch(
            route('employees.status', employee.id),
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
            <ActionMenu label={`Actions for ${employee.name}`}>
                {showView && (
                    <DropdownItem href={route('employees.show', employee.id)} icon={Eye}>
                        View details
                    </DropdownItem>
                )}
                {showEdit && (
                    <DropdownItem href={route('employees.edit', employee.id)} icon={Pencil}>
                        Edit
                    </DropdownItem>
                )}
                {employee.status === 'active' ? (
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
                title="Deactivate employee?"
                description={`${employee.name} will no longer appear on daily meal sheets. Their meal history is kept.`}
                confirmLabel="Deactivate"
            />
        </>
    );
}
