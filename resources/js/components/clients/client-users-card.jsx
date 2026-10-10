import { router, useForm } from '@inertiajs/react';
import { KeyRound, Pencil, Plus, Trash2, UserRound } from 'lucide-react';
import { useState } from 'react';
import ActionMenu from '@/components/ui/action-menu';
import Avatar from '@/components/ui/avatar';
import Button from '@/components/ui/button';
import Card, { CardHeader } from '@/components/ui/card';
import ConfirmDialog from '@/components/ui/confirm-dialog';
import { DropdownItem } from '@/components/ui/dropdown';
import EmptyState from '@/components/ui/empty-state';
import FormField from '@/components/ui/form-field';
import Input from '@/components/ui/input';
import Modal from '@/components/ui/modal';
import PasswordInput from '@/components/ui/password-input';
import Select from '@/components/ui/select';
import StatusBadge from '@/components/ui/status-badge';
import { formatDateTime } from '@/lib/format';

/**
 * Login accounts for a client organization (client admins).
 */
export default function ClientUsersCard({ client, users, statuses, canManage }) {
    const [editing, setEditing] = useState(null);
    const [formOpen, setFormOpen] = useState(false);
    const [removing, setRemoving] = useState(null);
    const [removeOpen, setRemoveOpen] = useState(false);
    const [processing, setProcessing] = useState(false);

    function openForm(user = null) {
        setEditing(user);
        setFormOpen(true);
    }

    function remove() {
        router.delete(route('clients.users.destroy', [client.id, removing.id]), {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setRemoveOpen(false);
            },
        });
    }

    return (
        <Card>
            <CardHeader
                title="Client logins"
                description="People at this client who can sign in to enter meals and see reports."
                actions={
                    canManage && (
                        <Button size="sm" icon={Plus} onClick={() => openForm()}>
                            Add login
                        </Button>
                    )
                }
            />

            {users.length === 0 ? (
                <EmptyState
                    icon={UserRound}
                    title="No logins yet"
                    description="Create a login so the client can mark daily meals themselves."
                />
            ) : (
                <ul className="divide-y divide-slate-100">
                    {users.map((user) => (
                        <li key={user.id} className="flex items-center gap-3 py-3 pr-2 pl-4 sm:pl-6">
                            <Avatar name={user.name} size="sm" />
                            <div className="min-w-0 flex-1">
                                <div className="flex flex-wrap items-center gap-2">
                                    <p className="truncate text-sm font-medium text-slate-900">{user.name}</p>
                                    <StatusBadge status={user.status} label={user.status_label} />
                                </div>
                                <p className="truncate text-sm text-slate-500">
                                    {[user.email, user.phone].filter(Boolean).join(' · ')}
                                </p>
                                <p className="text-xs text-slate-400">
                                    Last sign in: {user.last_login_at ? formatDateTime(user.last_login_at) : 'Never'}
                                </p>
                            </div>
                            {canManage && (
                                <ActionMenu label={`Actions for ${user.name}`}>
                                    <DropdownItem icon={Pencil} onClick={() => openForm(user)}>
                                        Edit
                                    </DropdownItem>
                                    <DropdownItem icon={KeyRound} onClick={() => openForm(user)}>
                                        Reset password
                                    </DropdownItem>
                                    <DropdownItem
                                        icon={Trash2}
                                        tone="danger"
                                        onClick={() => {
                                            setRemoving(user);
                                            setRemoveOpen(true);
                                        }}
                                    >
                                        Remove login
                                    </DropdownItem>
                                </ActionMenu>
                            )}
                        </li>
                    ))}
                </ul>
            )}

            {canManage && (
                <ClientUserForm
                    key={editing?.id ?? 'new'}
                    open={formOpen}
                    onClose={() => setFormOpen(false)}
                    client={client}
                    user={editing}
                    statuses={statuses}
                />
            )}

            <ConfirmDialog
                open={removeOpen}
                onClose={() => setRemoveOpen(false)}
                onConfirm={remove}
                processing={processing}
                title="Remove login?"
                description={removing && `${removing.name} will no longer be able to sign in.`}
                confirmLabel="Remove login"
            />
        </Card>
    );
}

function ClientUserForm({ open, onClose, client, user, statuses }) {
    const { data, setData, post, put, processing, errors, reset } = useForm({
        name: user?.name ?? '',
        email: user?.email ?? '',
        phone: user?.phone ?? '',
        password: '',
        status: user?.status ?? 'active',
    });

    function submit(event) {
        event.preventDefault();

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                onClose();
            },
        };

        if (user) {
            put(route('clients.users.update', [client.id, user.id]), options);
        } else {
            post(route('clients.users.store', client.id), options);
        }
    }

    const formId = 'client-user-form';

    return (
        <Modal
            open={open}
            onClose={onClose}
            title={user ? `Edit ${user.name}` : 'Add client login'}
            description={`For ${client.name}. They can sign in with the email or phone below.`}
            footer={
                <>
                    <Button variant="secondary" onClick={onClose} className="w-full sm:w-auto">
                        Cancel
                    </Button>
                    <Button type="submit" form={formId} loading={processing} className="w-full sm:w-auto">
                        {user ? 'Save changes' : 'Create login'}
                    </Button>
                </>
            }
        >
            <form id={formId} onSubmit={submit} noValidate className="space-y-5">
                <FormField name="name" label="Full name" error={errors.name} required>
                    <Input autoComplete="off" value={data.name} onChange={(event) => setData('name', event.target.value)} />
                </FormField>
                <div className="grid gap-5 sm:grid-cols-2">
                    <FormField name="email" label="Email" error={errors.email}>
                        <Input
                            type="email"
                            autoComplete="off"
                            value={data.email}
                            onChange={(event) => setData('email', event.target.value)}
                        />
                    </FormField>
                    <FormField name="phone" label="Phone" error={errors.phone}>
                        <Input
                            type="tel"
                            inputMode="tel"
                            autoComplete="off"
                            value={data.phone}
                            onChange={(event) => setData('phone', event.target.value)}
                        />
                    </FormField>
                </div>
                <FormField
                    name="password"
                    label={user ? 'New password' : 'Password'}
                    hint={user ? 'Leave empty to keep the current password.' : 'At least 8 characters with letters and numbers.'}
                    error={errors.password}
                    required={!user}
                >
                    <PasswordInput
                        autoComplete="new-password"
                        value={data.password}
                        onChange={(event) => setData('password', event.target.value)}
                    />
                </FormField>
                <FormField name="status" label="Status" error={errors.status} required>
                    <Select options={statuses} value={data.status} onChange={(event) => setData('status', event.target.value)} />
                </FormField>
            </form>
        </Modal>
    );
}
