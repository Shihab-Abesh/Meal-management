import { Head, Link, router, usePage } from '@inertiajs/react';
import { Pencil, Plus, Power, PowerOff, SearchX, UserCog } from 'lucide-react';
import { useState } from 'react';
import ActionMenu from '@/components/ui/action-menu';
import Alert from '@/components/ui/alert';
import Badge from '@/components/ui/badge';
import Button from '@/components/ui/button';
import Card from '@/components/ui/card';
import ConfirmDialog from '@/components/ui/confirm-dialog';
import DataTable from '@/components/ui/data-table';
import { DropdownItem } from '@/components/ui/dropdown';
import EmptyState from '@/components/ui/empty-state';
import FilterPanel, { FilterField } from '@/components/ui/filter-panel';
import PageHeader from '@/components/ui/page-header';
import Pagination from '@/components/ui/pagination';
import SearchInput from '@/components/ui/search-input';
import Select from '@/components/ui/select';
import StatusBadge from '@/components/ui/status-badge';
import { useFilters } from '@/hooks/use-filters';
import { formatDateTime } from '@/lib/format';

const FILTER_KEYS = ['role', 'status'];
const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

export default function UsersIndex({ users, filters, roles }) {
    const { errors = {} } = usePage().props;
    const { values, set, reset, loading } = useFilters(route('users.index'), filters);
    const activeFilters = FILTER_KEYS.filter((key) => values[key]).length;

    const columns = [
        {
            key: 'name',
            header: 'User',
            primary: true,
            render: (user) => (
                <span className="block min-w-0">
                    <span className="block truncate font-medium text-slate-900">
                        {user.name}
                        {user.is_self && <span className="ml-1.5 text-xs font-normal text-slate-500">(you)</span>}
                    </span>
                    <span className="block truncate text-xs text-slate-500">{user.email || user.phone}</span>
                </span>
            ),
        },
        { key: 'role', header: 'Role', render: (user) => <Badge tone={user.role === 'vendor_admin' ? 'violet' : 'blue'}>{user.role_label}</Badge> },
        { key: 'permissions', header: 'Permissions', className: 'hidden lg:table-cell', render: (user) => (user.role === 'vendor_admin' ? 'All' : user.permissions_count) },
        { key: 'last_login_at', header: 'Last sign-in', className: 'hidden xl:table-cell', render: (user) => (user.last_login_at ? formatDateTime(user.last_login_at) : 'Never') },
        { key: 'status', header: 'Status', render: (user) => <StatusBadge status={user.status} label={user.status_label} /> },
    ];

    return (
        <>
            <Head title="Users" />

            <PageHeader
                title="Users"
                description="Vendor admins and staff. Client and rider logins are managed on their own pages."
                actions={
                    <Button as={Link} href={route('users.create')} icon={Plus} className="w-full sm:w-auto">
                        Add user
                    </Button>
                }
            />

            {errors.role && (
                <Alert tone="error" className="mb-4">
                    {errors.role}
                </Alert>
            )}

            <Card>
                <div className="flex gap-2 border-b border-slate-100 p-4 sm:px-6 md:items-end">
                    <SearchInput value={values.search} onChange={(value) => set('search', value, { debounce: true })} placeholder="Name, email or phone…" className="min-w-0 flex-1 md:max-w-xs" />
                    <FilterPanel activeCount={activeFilters} onReset={() => reset(FILTER_KEYS)}>
                        <FilterField label="Role" htmlFor="filter-role">
                            <Select id="filter-role" placeholder="Any role" options={roles} value={values.role} onChange={(event) => set('role', event.target.value)} />
                        </FilterField>
                        <FilterField label="Status" htmlFor="filter-status">
                            <Select id="filter-status" placeholder="Any status" options={statusOptions} value={values.status} onChange={(event) => set('status', event.target.value)} />
                        </FilterField>
                    </FilterPanel>
                </div>

                <DataTable
                    caption="Users"
                    columns={columns}
                    rows={users.data}
                    loading={loading}
                    rowHref={(user) => route('users.edit', user.id)}
                    actions={(user) => <UserActions user={user} />}
                    mobileCard={(user) => (
                        <div className="space-y-1">
                            <div className="flex items-start justify-between gap-2">
                                <p className="min-w-0 truncate font-semibold text-slate-900">
                                    {user.name}
                                    {user.is_self && <span className="ml-1.5 text-xs font-normal text-slate-500">(you)</span>}
                                </p>
                                <StatusBadge status={user.status} label={user.status_label} />
                            </div>
                            <p className="truncate text-sm text-slate-600">
                                {user.role_label} · {user.email || user.phone}
                            </p>
                        </div>
                    )}
                    empty={
                        activeFilters > 0 || values.search ? (
                            <EmptyState icon={SearchX} title="No users found" />
                        ) : (
                            <EmptyState icon={UserCog} title="No users yet" />
                        )
                    }
                />
                <Pagination meta={users.meta} links={users.links} />
            </Card>
        </>
    );
}

function UserActions({ user }) {
    const [confirmOpen, setConfirmOpen] = useState(false);
    const [processing, setProcessing] = useState(false);

    function setStatus(status) {
        router.patch(
            route('users.status', user.id),
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
            <ActionMenu label={`Actions for ${user.name}`}>
                <DropdownItem href={route('users.edit', user.id)} icon={Pencil}>
                    Edit
                </DropdownItem>
                {!user.is_self &&
                    (user.status === 'active' ? (
                        <DropdownItem icon={PowerOff} tone="danger" onClick={() => setConfirmOpen(true)}>
                            Deactivate
                        </DropdownItem>
                    ) : (
                        <DropdownItem icon={Power} onClick={() => setStatus('active')}>
                            Activate
                        </DropdownItem>
                    ))}
            </ActionMenu>
            <ConfirmDialog
                open={confirmOpen}
                onClose={() => setConfirmOpen(false)}
                onConfirm={() => setStatus('inactive')}
                processing={processing}
                title={`Deactivate ${user.name}?`}
                description="They are signed out straight away and cannot sign in until the account is activated again."
                confirmLabel="Deactivate"
            />
        </>
    );
}
