import { Head, Link } from '@inertiajs/react';
import { Plus, SearchX, Upload, Users } from 'lucide-react';
import EmployeeActionMenu from '@/components/employees/employee-action-menu';
import MealFlags from '@/components/employees/meal-flags';
import Button from '@/components/ui/button';
import Card from '@/components/ui/card';
import DataTable from '@/components/ui/data-table';
import EmptyState from '@/components/ui/empty-state';
import FilterPanel, { FilterField } from '@/components/ui/filter-panel';
import PageHeader from '@/components/ui/page-header';
import Pagination from '@/components/ui/pagination';
import SearchInput from '@/components/ui/search-input';
import Select from '@/components/ui/select';
import StatusBadge from '@/components/ui/status-badge';
import { useFilters } from '@/hooks/use-filters';

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

const sortOptions = [
    { value: 'name:asc', label: 'Name (A–Z)' },
    { value: 'name:desc', label: 'Name (Z–A)' },
    { value: 'employee_code:asc', label: 'Employee ID' },
    { value: 'department:asc', label: 'Department' },
    { value: 'joining_date:desc', label: 'Recently joined' },
    { value: 'created_at:desc', label: 'Recently added' },
];

const FILTER_KEYS = ['client', 'status', 'department', 'meal_preference', 'meal'];

export default function EmployeesIndex({ employees, filters, clients, departments, mealPreferences, mealTypes, can }) {
    const { values, set, setMany, reset, loading } = useFilters(route('employees.index'), filters);
    const activeFilters = FILTER_KEYS.filter((key) => values[key]).length;
    const isVendor = clients !== null;
    const clientQuery = values.client ? { client: values.client } : {};

    function sortBy(key) {
        setMany({ sort: key, direction: values.sort === key && values.direction === 'asc' ? 'desc' : 'asc' });
    }

    const columns = [
        {
            key: 'name',
            header: 'Employee',
            primary: true,
            sortable: true,
            render: (employee) => (
                <span className="block min-w-0">
                    <span className="block truncate font-medium text-slate-900">{employee.name}</span>
                    <span className="block truncate text-xs text-slate-500">{employee.designation || employee.email}</span>
                </span>
            ),
        },
        { key: 'employee_code', header: 'ID', sortable: true, className: 'whitespace-nowrap' },
        // Secondary columns appear as the screen gets wider.
        isVendor && {
            key: 'client',
            header: 'Client',
            className: 'hidden xl:table-cell',
            render: (employee) => employee.client?.name,
        },
        { key: 'department', header: 'Department', sortable: true, className: 'hidden lg:table-cell' },
        { key: 'meals', header: 'Meals', render: (employee) => <MealFlags employee={employee} /> },
        {
            key: 'meal_preference',
            header: 'Preference',
            className: 'hidden 2xl:table-cell',
            render: (employee) => employee.meal_preference_label,
        },
        { key: 'status', header: 'Status', render: (e) => <StatusBadge status={e.status} label={e.status_label} /> },
    ].filter(Boolean);

    return (
        <>
            <Head title="Employees" />

            <PageHeader
                title="Employees"
                description={isVendor ? 'Employees of every client organization.' : 'Employees of your organization.'}
                actions={
                    can.create && (
                        <div className="flex w-full gap-2 sm:w-auto">
                            <Button
                                as={Link}
                                href={route('employees.import', clientQuery)}
                                variant="secondary"
                                icon={Upload}
                                className="flex-1 sm:flex-none"
                            >
                                Import
                            </Button>
                            <Button
                                as={Link}
                                href={route('employees.create', clientQuery)}
                                icon={Plus}
                                className="flex-1 sm:flex-none"
                            >
                                Add employee
                            </Button>
                        </div>
                    )
                }
            />

            <Card>
                <div className="flex flex-col gap-3 border-b border-slate-100 p-4 sm:px-6 md:flex-row md:flex-wrap md:items-end">
                    <div className="flex gap-2 md:contents">
                        <SearchInput
                            value={values.search}
                            onChange={(value) => set('search', value, { debounce: true })}
                            placeholder="Search name, ID, phone…"
                            className="min-w-0 flex-1 md:max-w-xs"
                        />
                        <FilterPanel activeCount={activeFilters} onReset={() => reset(FILTER_KEYS)}>
                            {isVendor && (
                                <FilterField label="Client" htmlFor="filter-client">
                                    <Select
                                        id="filter-client"
                                        placeholder="All clients"
                                        options={clients}
                                        value={values.client}
                                        onChange={(event) => setMany({ client: event.target.value, department: '' })}
                                    />
                                </FilterField>
                            )}
                            <FilterField label="Status" htmlFor="filter-status">
                                <Select
                                    id="filter-status"
                                    placeholder="Any status"
                                    options={statusOptions}
                                    value={values.status}
                                    onChange={(event) => set('status', event.target.value)}
                                />
                            </FilterField>
                            <FilterField label="Department" htmlFor="filter-department">
                                <Select
                                    id="filter-department"
                                    placeholder="All departments"
                                    options={departments.map((department) => ({ value: department, label: department }))}
                                    value={values.department}
                                    onChange={(event) => set('department', event.target.value)}
                                />
                            </FilterField>
                            <FilterField label="Takes" htmlFor="filter-meal">
                                <Select
                                    id="filter-meal"
                                    placeholder="Any meal"
                                    options={mealTypes}
                                    value={values.meal}
                                    onChange={(event) => set('meal', event.target.value)}
                                />
                            </FilterField>
                            <FilterField label="Preference" htmlFor="filter-preference">
                                <Select
                                    id="filter-preference"
                                    placeholder="Any preference"
                                    options={mealPreferences}
                                    value={values.meal_preference}
                                    onChange={(event) => set('meal_preference', event.target.value)}
                                />
                            </FilterField>
                            <FilterField label="Sort by" htmlFor="filter-sort">
                                <Select
                                    id="filter-sort"
                                    options={sortOptions}
                                    value={`${values.sort}:${values.direction}`}
                                    onChange={(event) => {
                                        const [sort, direction] = event.target.value.split(':');
                                        setMany({ sort, direction });
                                    }}
                                />
                            </FilterField>
                        </FilterPanel>
                    </div>
                </div>

                <DataTable
                    caption="Employees"
                    columns={columns}
                    rows={employees.data}
                    sort={{ key: values.sort, direction: values.direction }}
                    onSort={sortBy}
                    loading={loading}
                    rowHref={(employee) => route('employees.show', employee.id)}
                    actions={(employee) => <EmployeeActionMenu employee={employee} showView />}
                    mobileCard={(employee) => (
                        <div className="space-y-1.5">
                            <div className="flex items-start justify-between gap-2">
                                <p className="min-w-0 truncate font-semibold text-slate-900">{employee.name}</p>
                                <StatusBadge status={employee.status} label={employee.status_label} />
                            </div>
                            <p className="truncate text-sm text-slate-600">
                                {[employee.employee_code, employee.department, isVendor && employee.client?.name]
                                    .filter(Boolean)
                                    .join(' · ')}
                            </p>
                            <div className="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                                <MealFlags employee={employee} />
                                {employee.meal_preference !== 'regular' && <span>{employee.meal_preference_label}</span>}
                            </div>
                        </div>
                    )}
                    empty={
                        activeFilters > 0 || values.search ? (
                            <EmptyState
                                icon={SearchX}
                                title="No employees found"
                                description="Try another search or clear the filters."
                                action={
                                    <Button variant="secondary" onClick={() => reset(['search', ...FILTER_KEYS])}>
                                        Clear search and filters
                                    </Button>
                                }
                            />
                        ) : (
                            <EmptyState
                                icon={Users}
                                title="No employees yet"
                                description="Create your first employee, or import a list from a spreadsheet."
                                action={
                                    can.create && (
                                        <div className="flex flex-wrap justify-center gap-2">
                                            <Button as={Link} href={route('employees.create')} icon={Plus}>
                                                Add employee
                                            </Button>
                                            <Button as={Link} href={route('employees.import')} variant="secondary" icon={Upload}>
                                                Import CSV
                                            </Button>
                                        </div>
                                    )
                                }
                            />
                        )
                    }
                />

                <Pagination meta={employees.meta} links={employees.links} />
            </Card>
        </>
    );
}
