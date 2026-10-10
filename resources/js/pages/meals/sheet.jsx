import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, Check, Lock, Minus, ShieldAlert, Users } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import Alert from '@/components/ui/alert';
import Badge from '@/components/ui/badge';
import Button from '@/components/ui/button';
import Card from '@/components/ui/card';
import ChoiceChips from '@/components/ui/choice-chips';
import ConfirmDialog from '@/components/ui/confirm-dialog';
import DateNavigator from '@/components/ui/date-navigator';
import EmptyState from '@/components/ui/empty-state';
import SearchInput from '@/components/ui/search-input';
import Select from '@/components/ui/select';
import { useApp } from '@/hooks/use-app';
import { formatDateTime, formatLongDate, formatNumber, formatTime } from '@/lib/format';
import { cn } from '@/lib/utils';

/**
 * The daily meal sheet: one row per employee with a YES / NO toggle for each
 * meal. Replaces the notebook count. Remounted after every save so local
 * state always starts from what the server has.
 */
export default function MealSheetPage(props) {
    const signature = [props.client.id, props.date, ...props.meals.map((meal) => `${meal.last_saved_at}:${Object.keys(meal.selected).length}`)].join('|');

    return <MealSheet key={signature} {...props} />;
}

function initialSelection(meals) {
    return Object.fromEntries(meals.map((meal) => [meal.meal_type, new Set(Object.keys(meal.selected).map(Number))]));
}

function sameSet(a, b) {
    return a.size === b.size && [...a].every((id) => b.has(id));
}

function MealSheet({ client, date, today, maxDate, meals, employees, clients, showAmounts, canOverride }) {
    const { money } = useApp();
    const { errors = {} } = usePage().props;
    const initial = useMemo(() => initialSelection(meals), [meals]);
    const [selected, setSelected] = useState(() => initialSelection(meals));
    const [focus, setFocus] = useState('all');
    const [search, setSearch] = useState('');
    const [processing, setProcessing] = useState(false);
    const [overrideOpen, setOverrideOpen] = useState(false);

    const mealsByType = Object.fromEntries(meals.map((meal) => [meal.meal_type, meal]));
    const visibleTypes = focus === 'all' ? client.meal_types : [focus];
    const editable = (type) => !mealsByType[type].closed || canOverride;
    const dirtyTypes = client.meal_types.filter((type) => !sameSet(selected[type], initial[type]));
    const isDirty = dirtyTypes.length > 0;
    const needsOverride = dirtyTypes.some((type) => mealsByType[type].closed);

    // An employee gets a toggle for a meal they normally take, or already have.
    const eligible = (employee, type) => employee.takes[type] || initial[type].has(employee.id);
    const canSelect = (employee, type) => eligible(employee, type) && (employee.active || initial[type].has(employee.id));

    const term = search.trim().toLowerCase();
    const listed = employees.filter(
        (employee) =>
            visibleTypes.some((type) => eligible(employee, type)) &&
            (!term || [employee.name, employee.employee_code, employee.department].some((value) => value?.toLowerCase().includes(term))),
    );

    // Warn before leaving with unsaved changes.
    useEffect(() => {
        if (!isDirty) {
            return undefined;
        }

        const beforeUnload = (event) => {
            event.preventDefault();
            event.returnValue = '';
        };
        window.addEventListener('beforeunload', beforeUnload);
        const removeRouterGuard = router.on('before', (event) => {
            const visit = event.detail.visit;

            if (visit.method === 'get' && !window.confirm('You have meal changes that are not confirmed. Leave without saving?')) {
                event.preventDefault();
            }
        });

        return () => {
            window.removeEventListener('beforeunload', beforeUnload);
            removeRouterGuard();
        };
    }, [isDirty]);

    function toggle(employee, type) {
        if (!editable(type) || !canSelect(employee, type)) {
            return;
        }

        setSelected((current) => {
            const next = new Set(current[type]);
            next.has(employee.id) ? next.delete(employee.id) : next.add(employee.id);

            return { ...current, [type]: next };
        });
    }

    function bulk(types, select, scope = listed) {
        setSelected((current) => {
            const next = { ...current };

            for (const type of types) {
                if (!client.meal_types.includes(type) || !editable(type)) {
                    continue;
                }

                const set = new Set(current[type]);

                for (const employee of scope) {
                    if (select && canSelect(employee, type)) {
                        set.add(employee.id);
                    } else if (!select) {
                        set.delete(employee.id);
                    }
                }

                next[type] = set;
            }

            return next;
        });
    }

    function submit(override = false) {
        if (needsOverride && !override) {
            setOverrideOpen(true);

            return;
        }

        // Only the meals that changed are sent, so an untouched closed meal never needs an override.
        const changed = Object.fromEntries(dirtyTypes.map((type) => [type, [...selected[type]]]));

        router.post(
            route('meals.store'),
            { client_id: client.id, date, meals: changed, override },
            {
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => {
                    setProcessing(false);
                    setOverrideOpen(false);
                },
            },
        );
    }

    function changeDate(value) {
        router.get(route('meals.index'), clients ? { client: client.id, date: value } : { date: value }, { preserveScroll: true });
    }

    const totals = client.meal_types.map((type) => {
        const meal = mealsByType[type];
        let amount = 0;

        if (showAmounts) {
            for (const id of selected[type]) {
                amount += Number(meal.selected[id] ?? meal.price ?? 0);
            }
        }

        return { type, label: meal.label, count: selected[type].size, amount };
    });
    const totalMeals = totals.reduce((sum, item) => sum + item.count, 0);
    const totalAmount = totals.reduce((sum, item) => sum + item.amount, 0);
    const everSaved = meals.some((meal) => meal.saved);
    const errorMessages = Object.entries(errors)
        .filter(([key]) => key === 'meals' || key.startsWith('meals.') || key === 'override' || key === 'date')
        .map(([, message]) => message);

    return (
        <>
            <Head title={`Meals · ${client.name}`} />

            <div className="mb-4 space-y-3">
                {clients && (
                    <Link
                        href={route('meals.index', { date })}
                        className="inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-700"
                    >
                        <ArrowLeft className="size-4" aria-hidden="true" />
                        All clients
                    </Link>
                )}
                <div className="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div className="min-w-0">
                        <h1 className="text-2xl font-bold tracking-tight text-slate-900">{clients ? client.name : 'Daily meals'}</h1>
                        <p className="mt-1 text-sm text-slate-500">{formatLongDate(date)}</p>
                    </div>
                    <div className="flex flex-col gap-2 sm:flex-row sm:items-center">
                        {clients && (
                            <Select
                                aria-label="Client"
                                options={clients}
                                value={String(client.id)}
                                onChange={(event) => router.get(route('meals.index'), { client: event.target.value, date })}
                                className="sm:w-56"
                            />
                        )}
                        <DateNavigator value={date} today={today} max={maxDate} onChange={changeDate} />
                    </div>
                </div>
            </div>

            {errorMessages.length > 0 && (
                <Alert tone="error" className="mb-4">
                    {errorMessages.map((message) => (
                        <p key={message}>{message}</p>
                    ))}
                </Alert>
            )}

            <div className="mb-4 grid gap-2 sm:grid-cols-2">
                {meals.map((meal) => (
                    <CutoffStatus key={meal.meal_type} meal={meal} canOverride={canOverride} />
                ))}
            </div>

            <Card>
                <div className="space-y-3 border-b border-slate-100 p-4 sm:px-6">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        {client.meal_types.length > 1 && (
                            <ChoiceChips
                                options={[{ value: 'all', label: 'All meals' }, ...meals.map((meal) => ({ value: meal.meal_type, label: meal.label }))]}
                                value={focus}
                                onChange={setFocus}
                            />
                        )}
                        {employees.length > 8 && (
                            <SearchInput
                                value={search}
                                onChange={setSearch}
                                placeholder="Find employee…"
                                label="Find employee"
                                className="sm:w-64"
                            />
                        )}
                    </div>
                    <div className="flex flex-wrap gap-2" role="group" aria-label="Bulk actions">
                        <BulkButton onClick={() => bulk(visibleTypes, true)}>Select all</BulkButton>
                        <BulkButton onClick={() => bulk(visibleTypes, false)}>Unselect all</BulkButton>
                        {client.meal_types.includes('lunch') && <BulkButton onClick={() => bulk(['lunch'], true)}>All lunch</BulkButton>}
                        {client.meal_types.includes('dinner') && <BulkButton onClick={() => bulk(['dinner'], true)}>All dinner</BulkButton>}
                        <BulkButton onClick={() => bulk(client.meal_types, false, employees)} danger>
                            Clear all
                        </BulkButton>
                    </div>
                </div>

                {listed.length === 0 ? (
                    <EmptyState
                        icon={Users}
                        title={search ? 'No employees match your search' : 'No employees to show'}
                        description={search ? undefined : 'Add active employees to this client to record their meals.'}
                    />
                ) : (
                    <ul className="divide-y divide-slate-100">
                        {listed.map((employee) => (
                            <EmployeeRow
                                key={employee.id}
                                employee={employee}
                                types={visibleTypes}
                                selected={selected}
                                eligible={eligible}
                                canSelect={canSelect}
                                editable={editable}
                                onToggle={toggle}
                            />
                        ))}
                    </ul>
                )}
            </Card>

            {/* Running total and confirm button, kept within thumb reach. */}
            <div className="sticky bottom-[calc(3.75rem+env(safe-area-inset-bottom))] z-20 -mx-4 mt-4 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur sm:mx-0 sm:rounded-xl sm:border sm:shadow-sm lg:bottom-4">
                <div className="flex items-center gap-3">
                    <dl className="flex min-w-0 flex-1 flex-wrap items-baseline gap-x-4 gap-y-0.5 text-sm">
                        {totals.map((item) => (
                            <div key={item.type} className="flex items-baseline gap-1">
                                <dt className="text-slate-500">{item.label}</dt>
                                <dd className="font-bold text-slate-900 tabular-nums">{formatNumber(item.count)}</dd>
                            </div>
                        ))}
                        {totals.length > 1 && (
                            <div className="flex items-baseline gap-1">
                                <dt className="text-slate-500">Total</dt>
                                <dd className="font-bold text-slate-900 tabular-nums">{formatNumber(totalMeals)}</dd>
                            </div>
                        )}
                        {showAmounts && (
                            <div className="flex basis-full items-baseline gap-1 sm:basis-auto">
                                <dt className="sr-only">Amount</dt>
                                <dd className="text-base font-bold text-primary-700 tabular-nums">{money(totalAmount)}</dd>
                            </div>
                        )}
                    </dl>
                    <Button
                        onClick={() => submit(false)}
                        loading={processing}
                        disabled={everSaved ? !isDirty : totalMeals === 0}
                        variant={!isDirty && everSaved ? 'secondary' : 'primary'}
                        icon={!isDirty && everSaved ? Check : undefined}
                        className={cn('shrink-0', !isDirty && everSaved && 'text-primary-700 disabled:opacity-100')}
                    >
                        {!isDirty && everSaved ? 'Confirmed' : 'Confirm meals'}
                    </Button>
                </div>
                {showAmounts && totals.length > 0 && (
                    <p className="mt-1 text-xs text-slate-500">
                        {totals
                            .filter((item) => item.count > 0)
                            .map((item) => `${formatNumber(item.count)} ${item.label.toLowerCase()} × ${money(mealsByType[item.type].price)}`)
                            .join(' · ') || 'No meals selected'}
                    </p>
                )}
            </div>

            <ConfirmDialog
                open={overrideOpen}
                onClose={() => setOverrideOpen(false)}
                onConfirm={() => submit(true)}
                processing={processing}
                tone="primary"
                title="Change meals after the cutoff?"
                description="The cutoff for this meal has passed. Your change will be saved as an override and recorded in the activity log."
                confirmLabel="Save override"
            />
        </>
    );
}

function BulkButton({ onClick, danger = false, children }) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={cn(
                'inline-flex min-h-10 items-center rounded-full px-3.5 text-sm font-medium ring-1 ring-inset focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600',
                danger ? 'text-red-600 ring-red-200 hover:bg-red-50' : 'text-slate-700 ring-slate-300 hover:bg-slate-50',
            )}
        >
            {children}
        </button>
    );
}

function CutoffStatus({ meal, canOverride }) {
    let tone = 'bg-emerald-50 text-emerald-800 ring-emerald-600/20';
    let text = `${meal.label} open until ${formatTime(meal.cutoff)}`;

    if (meal.closed) {
        tone = canOverride ? 'bg-amber-50 text-amber-800 ring-amber-600/20' : 'bg-slate-100 text-slate-700 ring-slate-500/20';
        text = canOverride ? `${meal.label} closed at ${formatTime(meal.cutoff)} · you can override` : `${meal.label} closed at ${formatTime(meal.cutoff)}`;
    }

    return (
        <div className={cn('flex items-center gap-2 rounded-lg px-3 py-2 text-sm ring-1 ring-inset', tone)}>
            {meal.closed ? (
                canOverride ? (
                    <ShieldAlert className="size-4 shrink-0" aria-hidden="true" />
                ) : (
                    <Lock className="size-4 shrink-0" aria-hidden="true" />
                )
            ) : (
                <span className="size-2 shrink-0 rounded-full bg-emerald-500" aria-hidden="true" />
            )}
            <span className="min-w-0 flex-1">{text}</span>
            {meal.last_saved_at && (
                <span className="hidden text-xs opacity-75 md:inline" title={meal.last_confirmed_by ?? undefined}>
                    Saved {formatDateTime(meal.last_saved_at)}
                </span>
            )}
        </div>
    );
}

function EmployeeRow({ employee, types, selected, eligible, canSelect, editable, onToggle }) {
    const single = types.length === 1;
    const type = types[0];
    const details = [employee.employee_code, employee.department].filter(Boolean).join(' · ');

    const info = (
        <div className="min-w-0 flex-1">
            <p className="truncate font-medium text-slate-900">{employee.name}</p>
            <p className="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-slate-500">
                <span className="truncate">{details}</span>
                {employee.meal_preference !== 'regular' && <Badge tone="blue">{employee.meal_preference_label}</Badge>}
                {!employee.active && <Badge>Inactive</Badge>}
            </p>
        </div>
    );

    // With one meal in focus the whole row is the toggle: the biggest possible target.
    if (single) {
        const isOn = selected[type].has(employee.id);
        const disabled = !editable(type) || !canSelect(employee, type);

        return (
            <li>
                <button
                    type="button"
                    role="switch"
                    aria-checked={isOn}
                    aria-label={`${employee.name}: ${type}`}
                    disabled={disabled}
                    onClick={() => onToggle(employee, type)}
                    className={cn(
                        'flex min-h-16 w-full items-center gap-3 px-4 py-2 text-left transition-colors focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-primary-600 disabled:cursor-not-allowed sm:px-6',
                        isOn ? 'bg-primary-50/60' : 'hover:bg-slate-50',
                    )}
                >
                    {info}
                    <YesNo on={isOn} disabled={disabled} />
                </button>
            </li>
        );
    }

    return (
        <li className="flex flex-wrap items-center gap-x-3 gap-y-2 px-4 py-3 sm:px-6">
            <div className="min-w-0 basis-full sm:basis-0 sm:flex-1">{info}</div>
            <div className="grid flex-1 grid-cols-2 gap-2 sm:flex-none">
                {types.map((mealType) => {
                    if (!eligible(employee, mealType)) {
                        return (
                            <span key={mealType} className="flex min-h-12 items-center justify-center rounded-lg text-xs text-slate-400 sm:w-28">
                                No {mealType}
                            </span>
                        );
                    }

                    const isOn = selected[mealType].has(employee.id);
                    const disabled = !editable(mealType) || !canSelect(employee, mealType);

                    return (
                        <button
                            key={mealType}
                            type="button"
                            role="switch"
                            aria-checked={isOn}
                            aria-label={`${employee.name}: ${mealType}`}
                            disabled={disabled}
                            onClick={() => onToggle(employee, mealType)}
                            className={cn(
                                'flex min-h-12 items-center justify-center gap-1.5 rounded-lg px-3 text-sm font-semibold ring-1 transition-colors ring-inset focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:cursor-not-allowed disabled:opacity-60 sm:w-28',
                                isOn ? 'bg-primary-600 text-white ring-primary-600' : 'bg-white text-slate-500 ring-slate-300 hover:bg-slate-50',
                            )}
                        >
                            {isOn ? <Check className="size-4" aria-hidden="true" /> : <Minus className="size-4" aria-hidden="true" />}
                            <span className="capitalize">{mealType}</span>
                        </button>
                    );
                })}
            </div>
        </li>
    );
}

function YesNo({ on, disabled }) {
    return (
        <span
            className={cn(
                'flex h-10 w-20 shrink-0 items-center justify-center gap-1 rounded-lg text-sm font-bold',
                on ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-500',
                disabled && 'opacity-60',
            )}
            aria-hidden="true"
        >
            {on ? <Check className="size-4" /> : null}
            {on ? 'YES' : 'NO'}
        </span>
    );
}
