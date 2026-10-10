import { router } from '@inertiajs/react';
import { Archive, ArchiveRestore, Eye, Pencil, Power, PowerOff } from 'lucide-react';
import { useState } from 'react';
import ActionMenu from '@/components/ui/action-menu';
import ConfirmDialog from '@/components/ui/confirm-dialog';
import { DropdownDivider, DropdownItem } from '@/components/ui/dropdown';

const confirmations = {
    deactivate: {
        title: 'Deactivate client?',
        description: (name) =>
            `${name} will stop appearing in daily meal entry and its users will be signed out until it is activated again.`,
        confirmLabel: 'Deactivate',
        tone: 'danger',
    },
    archive: {
        title: 'Archive client?',
        description: (name) =>
            `${name} will be moved to the archived list and its users lose access. Meal, order and billing history is kept, and you can restore it at any time.`,
        confirmLabel: 'Archive',
        tone: 'danger',
    },
};

/**
 * Row / page actions for a client. Destructive actions ask first.
 */
export default function ClientActionMenu({ client, canManage, showView = false, showEdit = true }) {
    const [dialog, setDialog] = useState({ open: false, action: null });
    const [processing, setProcessing] = useState(false);

    function send(action) {
        const options = {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setDialog((current) => ({ ...current, open: false }));
            },
        };

        if (action === 'activate' || action === 'deactivate') {
            router.patch(
                route('clients.status', client.id),
                { status: action === 'activate' ? 'active' : 'inactive' },
                options,
            );
        } else if (action === 'archive') {
            router.delete(route('clients.destroy', client.id), options);
        } else if (action === 'restore') {
            router.patch(route('clients.restore', client.id), {}, options);
        }
    }

    // The action is kept after closing so the text does not vanish mid-animation.
    const confirmation = confirmations[dialog.action];

    return (
        <>
            <ActionMenu label={`Actions for ${client.name}`}>
                {showView && (
                    <DropdownItem href={route('clients.show', client.id)} icon={Eye}>
                        View details
                    </DropdownItem>
                )}
                {canManage && !client.archived && (
                    <>
                        {showEdit && (
                            <DropdownItem href={route('clients.edit', client.id)} icon={Pencil}>
                                Edit
                            </DropdownItem>
                        )}
                        {client.status === 'active' ? (
                            <DropdownItem icon={PowerOff} onClick={() => setDialog({ open: true, action: 'deactivate' })}>
                                Deactivate
                            </DropdownItem>
                        ) : (
                            <DropdownItem icon={Power} onClick={() => send('activate')}>
                                Activate
                            </DropdownItem>
                        )}
                        <DropdownDivider />
                        <DropdownItem icon={Archive} tone="danger" onClick={() => setDialog({ open: true, action: 'archive' })}>
                            Archive
                        </DropdownItem>
                    </>
                )}
                {canManage && client.archived && (
                    <DropdownItem icon={ArchiveRestore} onClick={() => send('restore')}>
                        Restore
                    </DropdownItem>
                )}
            </ActionMenu>

            <ConfirmDialog
                open={dialog.open}
                onClose={() => setDialog((current) => ({ ...current, open: false }))}
                onConfirm={() => send(dialog.action)}
                processing={processing}
                title={confirmation?.title}
                description={confirmation?.description(client.name)}
                confirmLabel={confirmation?.confirmLabel}
                tone={confirmation?.tone}
            />
        </>
    );
}
