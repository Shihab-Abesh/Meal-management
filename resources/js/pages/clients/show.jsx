import { Head, Link } from '@inertiajs/react';
import { Activity, CalendarRange, ClipboardList, FileText, Mail, MapPin, Pencil, Phone, Upload, UserPlus, Users } from 'lucide-react';
import StatCard from '@/components/dashboard/stat-card';
import ClientActionMenu from '@/components/clients/client-action-menu';
import ClientUsersCard from '@/components/clients/client-users-card';
import MealTypeBadges from '@/components/clients/meal-type-badges';
import Alert from '@/components/ui/alert';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import DescriptionList from '@/components/ui/description-list';
import EmptyState from '@/components/ui/empty-state';
import PageHeader from '@/components/ui/page-header';
import StatusBadge from '@/components/ui/status-badge';
import { useApp } from '@/hooks/use-app';
import { formatDate, formatDateTime, formatNumber, formatTime } from '@/lib/format';

export default function ShowClient({ client, overview, users, statuses, can }) {
    const { stats } = overview;
    const { money } = useApp();

    const statCards = [
        { key: 'employees', label: 'Employees', value: stats.employees, icon: 'users', hint: 'Active employees registered' },
        { key: 'todays_meals', label: "Today's meals", value: stats.todays_meals, icon: 'utensils', hint: 'Lunch and dinner' },
        { key: 'monthly_meals', label: 'This month', value: stats.monthly_meals, icon: 'calendar', hint: 'Meals this month' },
        stats.outstanding_balance !== null && {
            key: 'outstanding_balance',
            label: 'Outstanding',
            value: stats.outstanding_balance,
            icon: 'banknote',
            hint: 'Unpaid bills',
            format: 'currency',
        },
    ].filter(Boolean);

    return (
        <>
            <Head title={client.name} />

            <PageHeader
                title={client.name}
                description={
                    <span className="inline-flex flex-wrap items-center gap-2">
                        {client.archived ? <StatusBadge status="archived" /> : <StatusBadge status={client.status} label={client.status_label} />}
                        <MealTypeBadges types={client.meal_types} />
                    </span>
                }
                back={{ href: route('clients.index'), label: 'Clients' }}
                actions={
                    <div className="flex items-center gap-2">
                        {can.update && (
                            <Button as={Link} href={route('clients.edit', client.id)} icon={Pencil} variant="secondary">
                                Edit
                            </Button>
                        )}
                        {(can.update || can.restore) && <ClientActionMenu client={client} canManage showEdit={false} />}
                    </div>
                }
            />

            {client.archived && (
                <Alert tone="info" className="mb-6">
                    This client is archived. Its users cannot sign in and it is hidden from daily operations.
                    {can.restore && ' Use the actions menu to restore it.'}
                </Alert>
            )}

            <section aria-label="Client figures" className="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
                {statCards.map((stat) => (
                    <StatCard key={stat.key} stat={{ format: 'number', ...stat }} />
                ))}
            </section>

            <div className="mt-6 grid gap-6 lg:grid-cols-3">
                <div className="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader title="Contact" />
                        <CardBody className="space-y-5">
                            <div className="flex flex-col gap-1 sm:flex-row sm:flex-wrap sm:gap-x-6">
                                <a href={`tel:${client.phone}`} className="inline-flex min-h-11 items-center gap-2 text-sm font-medium text-slate-700 hover:text-primary-700">
                                    <Phone className="size-4 text-slate-400" aria-hidden="true" />
                                    {client.phone}
                                </a>
                                {client.email && (
                                    <a
                                        href={`mailto:${client.email}`}
                                        className="inline-flex min-h-11 items-center gap-2 text-sm font-medium break-all text-slate-700 hover:text-primary-700"
                                    >
                                        <Mail className="size-4 shrink-0 text-slate-400" aria-hidden="true" />
                                        {client.email}
                                    </a>
                                )}
                            </div>
                            <DescriptionList
                                items={[
                                    { label: 'Contact person', value: client.contact_person },
                                    { label: 'City', value: client.city },
                                    { label: 'Office address', value: client.address, wide: true },
                                    {
                                        label: 'Delivery address',
                                        value: client.delivery_address || 'Same as office address',
                                        wide: true,
                                    },
                                ]}
                            />
                        </CardBody>
                    </Card>

                    {users && (
                        <ClientUsersCard client={client} users={users} statuses={statuses} canManage={can.manageUsers} />
                    )}

                    <Card>
                        <CardHeader
                            title="Recent orders"
                            actions={
                                can.viewOrders &&
                                overview.recent_orders.length > 0 && (
                                    <Link
                                        href={route('orders.index', { client: client.id })}
                                        className="text-sm font-semibold text-primary-700 hover:text-primary-800"
                                    >
                                        Today's orders
                                    </Link>
                                )
                            }
                        />
                        {overview.recent_orders.length === 0 ? (
                            <EmptyState
                                icon={ClipboardList}
                                title="No orders yet"
                                description="Orders appear here once meals are confirmed for this client."
                            />
                        ) : (
                            <ul className="divide-y divide-slate-100">
                                {overview.recent_orders.map((order) => {
                                    const content = (
                                        <>
                                            <div className="min-w-0">
                                                <p className="text-sm font-medium text-slate-900">
                                                    {formatDate(order.order_date)} · {order.meal_type_label}
                                                </p>
                                                <p className="text-xs text-slate-500">
                                                    {order.order_number} · {formatNumber(order.quantity)} meals
                                                    {order.amount !== null && ` · ${money(order.amount)}`}
                                                </p>
                                            </div>
                                            <StatusBadge status={order.status} label={order.status_label} />
                                        </>
                                    );

                                    return (
                                        <li key={order.id}>
                                            {can.viewOrders ? (
                                                <Link
                                                    href={route('orders.show', order.id)}
                                                    className="flex items-center justify-between gap-3 px-4 py-3 hover:bg-slate-50 sm:px-6"
                                                >
                                                    {content}
                                                </Link>
                                            ) : (
                                                <div className="flex items-center justify-between gap-3 px-4 py-3 sm:px-6">{content}</div>
                                            )}
                                        </li>
                                    );
                                })}
                            </ul>
                        )}
                    </Card>
                </div>

                <div className="space-y-6">
                    {!client.archived && (
                        <Card>
                            <CardHeader title="Employees" />
                            <CardBody className="flex flex-col gap-2">
                                <Button as={Link} href={route('employees.index', { client: client.id })} variant="secondary" icon={Users}>
                                    View employees
                                </Button>
                                {client.status === 'active' && (
                                    <div className="grid grid-cols-2 gap-2">
                                        <Button as={Link} href={route('employees.create', { client: client.id })} variant="secondary" icon={UserPlus}>
                                            Add
                                        </Button>
                                        <Button as={Link} href={route('employees.import', { client: client.id })} variant="secondary" icon={Upload}>
                                            Import
                                        </Button>
                                    </div>
                                )}
                            </CardBody>
                        </Card>
                    )}

                    {stats.outstanding_balance !== null && (
                        <Card>
                            <CardHeader title="Billing" />
                            <CardBody className="grid grid-cols-2 gap-2">
                                <Button as={Link} href={route('bills.index', { client: client.id })} variant="secondary" icon={FileText}>
                                    Bills
                                </Button>
                                <Button as={Link} href={route('billing.statement', { client: client.id })} variant="secondary" icon={CalendarRange}>
                                    Statement
                                </Button>
                            </CardBody>
                        </Card>
                    )}

                    {overview.prices && Object.keys(overview.prices).length > 0 && (
                        <Card>
                            <CardHeader
                                title="Prices today"
                                actions={
                                    can.managePricing && (
                                        <Link href={route('pricing.show', client.id)} className="text-sm font-semibold text-primary-700 hover:text-primary-800">
                                            Manage
                                        </Link>
                                    )
                                }
                            />
                            <CardBody>
                                <dl className="grid grid-cols-2 gap-4">
                                    {Object.entries(overview.prices).map(([type, price]) => (
                                        <div key={type}>
                                            <dt className="text-xs font-medium tracking-wide text-slate-500 uppercase">{type}</dt>
                                            <dd className="mt-1 text-lg font-bold text-slate-900 tabular-nums">{money(price.price)}</dd>
                                            <dd className="text-xs text-slate-500">{price.source === 'client' ? 'Client price' : 'Default price'}</dd>
                                        </div>
                                    ))}
                                </dl>
                            </CardBody>
                        </Card>
                    )}

                    <Card>
                        <CardHeader title="Meal settings" />
                        <CardBody>
                            <DescriptionList
                                className="sm:grid-cols-1"
                                items={[
                                    { label: 'Agreed headcount', value: client.employee_count !== null ? formatNumber(client.employee_count) : null },
                                    { label: 'Office starts', value: client.office_start_time && formatTime(client.office_start_time) },
                                    {
                                        label: 'Lunch cutoff',
                                        value: client.meal_types.includes('lunch')
                                            ? client.lunch_cutoff_time
                                                ? formatTime(client.lunch_cutoff_time)
                                                : 'Default'
                                            : 'Not served',
                                    },
                                    {
                                        label: 'Dinner cutoff',
                                        value: client.meal_types.includes('dinner')
                                            ? client.dinner_cutoff_time
                                                ? formatTime(client.dinner_cutoff_time)
                                                : 'Default'
                                            : 'Not served',
                                    },
                                ]}
                            />
                        </CardBody>
                    </Card>

                    {(client.special_instructions || client.notes) && (
                        <Card>
                            <CardHeader title="Instructions & notes" />
                            <CardBody>
                                <DescriptionList
                                    className="sm:grid-cols-1"
                                    items={[
                                        { label: 'Special instructions', value: client.special_instructions },
                                        { label: 'Internal notes', value: client.notes },
                                    ].filter((item) => item.value)}
                                />
                            </CardBody>
                        </Card>
                    )}

                    <Card>
                        <CardHeader title="Recent activity" />
                        {overview.recent_activity.length === 0 ? (
                            <EmptyState icon={Activity} title="No activity yet" />
                        ) : (
                            <ul className="divide-y divide-slate-100">
                                {overview.recent_activity.map((entry) => (
                                    <li key={entry.id} className="px-4 py-3 sm:px-6">
                                        <p className="text-sm text-slate-800">{entry.description}</p>
                                        <p className="mt-0.5 text-xs text-slate-500">
                                            {entry.user ?? 'System'} · {formatDateTime(entry.created_at)}
                                        </p>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </Card>

                    <Card>
                        <CardBody>
                            <p className="flex items-start gap-2 text-xs text-slate-500">
                                <MapPin className="mt-0.5 size-3.5 shrink-0" aria-hidden="true" />
                                Added {formatDateTime(client.created_at)} · Updated {formatDateTime(client.updated_at)}
                            </p>
                        </CardBody>
                    </Card>
                </div>
            </div>
        </>
    );
}
