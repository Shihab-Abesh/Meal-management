import { Head, Link } from '@inertiajs/react';
import { History, Mail, Pencil, Phone, Utensils } from 'lucide-react';
import EmployeeActionMenu from '@/components/employees/employee-action-menu';
import MealFlags from '@/components/employees/meal-flags';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import DescriptionList from '@/components/ui/description-list';
import EmptyState from '@/components/ui/empty-state';
import PageHeader from '@/components/ui/page-header';
import StatusBadge from '@/components/ui/status-badge';
import { useApp } from '@/hooks/use-app';
import { formatDate, formatDateTime } from '@/lib/format';

export default function ShowEmployee({ employee, history, meals, mealsThisMonth, can }) {
    const { money } = useApp();

    return (
        <>
            <Head title={employee.name} />

            <PageHeader
                title={employee.name}
                description={
                    <span className="inline-flex flex-wrap items-center gap-2">
                        <StatusBadge status={employee.status} label={employee.status_label} />
                        <span>{[employee.employee_code, employee.client?.name].filter(Boolean).join(' · ')}</span>
                    </span>
                }
                back={{ href: route('employees.index'), label: 'Employees' }}
                actions={
                    can.update && (
                        <div className="flex items-center gap-2">
                            <Button as={Link} href={route('employees.edit', employee.id)} variant="secondary" icon={Pencil}>
                                Edit
                            </Button>
                            <EmployeeActionMenu employee={employee} showEdit={false} />
                        </div>
                    )
                }
            />

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader title="Details" />
                        <CardBody className="space-y-5">
                            {(employee.phone || employee.email) && (
                                <div className="flex flex-col gap-1 sm:flex-row sm:flex-wrap sm:gap-x-6">
                                    {employee.phone && (
                                        <a
                                            href={`tel:${employee.phone}`}
                                            className="inline-flex min-h-11 items-center gap-2 text-sm font-medium text-slate-700 hover:text-primary-700"
                                        >
                                            <Phone className="size-4 text-slate-400" aria-hidden="true" />
                                            {employee.phone}
                                        </a>
                                    )}
                                    {employee.email && (
                                        <a
                                            href={`mailto:${employee.email}`}
                                            className="inline-flex min-h-11 items-center gap-2 text-sm font-medium break-all text-slate-700 hover:text-primary-700"
                                        >
                                            <Mail className="size-4 shrink-0 text-slate-400" aria-hidden="true" />
                                            {employee.email}
                                        </a>
                                    )}
                                </div>
                            )}
                            <DescriptionList
                                items={[
                                    { label: 'Employee ID', value: employee.employee_code },
                                    { label: 'Client', value: employee.client?.name },
                                    { label: 'Department', value: employee.department },
                                    { label: 'Designation', value: employee.designation },
                                    { label: 'Joining date', value: employee.joining_date && formatDate(employee.joining_date) },
                                    { label: 'Status', value: employee.status_label },
                                ]}
                            />
                        </CardBody>
                    </Card>

                    <Card>
                        <CardHeader
                            title="Meal history"
                            description={`${mealsThisMonth} meals so far this month.`}
                            actions={
                                can.meals && (
                                    <Link
                                        href={route('meals.history', { employee: employee.id, from: '2000-01-01' })}
                                        className="text-sm font-semibold text-primary-700 hover:text-primary-800"
                                    >
                                        View all
                                    </Link>
                                )
                            }
                        />
                        {meals.length === 0 ? (
                            <EmptyState
                                icon={Utensils}
                                title="No meals recorded yet"
                                description="Meals confirmed on the daily meal sheet will be listed here."
                            />
                        ) : (
                            <ul className="divide-y divide-slate-100">
                                {meals.map((meal) => (
                                    <li key={meal.id} className="flex items-center justify-between gap-3 px-4 py-3 sm:px-6">
                                        <div className="min-w-0">
                                            <p className="text-sm font-medium text-slate-900">{formatDate(meal.meal_date)}</p>
                                            <p className="text-xs text-slate-500">{meal.meal_type_label}</p>
                                        </div>
                                        <div className="flex items-center gap-3">
                                            {meal.unit_price !== null && <span className="text-sm text-slate-600 tabular-nums">{money(meal.unit_price)}</span>}
                                            <StatusBadge status={meal.status} label={meal.status_label} />
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </Card>
                </div>

                <div className="space-y-6">
                    <Card>
                        <CardHeader title="Meals" />
                        <CardBody>
                            <DescriptionList
                                className="sm:grid-cols-1"
                                items={[
                                    { label: 'Takes', value: <MealFlags employee={employee} /> },
                                    { label: 'Meal preference', value: employee.meal_preference_label },
                                    { label: 'Notes', value: employee.notes },
                                ]}
                            />
                        </CardBody>
                    </Card>

                    <Card>
                        <CardHeader title="History" description="Changes to this record." />
                        {history.length === 0 ? (
                            <EmptyState icon={History} title="No changes recorded" />
                        ) : (
                            <ol className="divide-y divide-slate-100">
                                {history.map((entry) => (
                                    <li key={entry.id} className="px-4 py-3 sm:px-6">
                                        <p className="text-sm text-slate-800">{entry.description}</p>
                                        <p className="mt-0.5 text-xs text-slate-500">
                                            {entry.user ?? 'System'} · {formatDateTime(entry.created_at)}
                                        </p>
                                    </li>
                                ))}
                            </ol>
                        )}
                    </Card>
                </div>
            </div>
        </>
    );
}
