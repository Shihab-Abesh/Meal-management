import { Head, router } from '@inertiajs/react';
import { CalendarCheck, ChevronLeft, ChevronRight, CopyPlus } from 'lucide-react';
import { Fragment, useMemo, useState } from 'react';
import { CATEGORIES } from '@/components/menu-chart/categories';
import MenuCell from '@/components/menu-chart/menu-cell';
import MenuEditor from '@/components/menu-chart/menu-editor';
import Badge from '@/components/ui/badge';
import Button from '@/components/ui/button';
import Card from '@/components/ui/card';
import DateInput from '@/components/ui/date-input';
import { shiftDate, shiftMonth } from '@/components/ui/date-navigator';
import PageHeader from '@/components/ui/page-header';
import { useFilters } from '@/hooks/use-filters';
import { parseDate } from '@/lib/format';
import { cn } from '@/lib/utils';

const MEAL_DOTS = { lunch: 'bg-amber-400', dinner: 'bg-violet-500' };

function daysBetween(start, end) {
    const days = [];

    for (let day = start; day <= end; day = shiftDate(day, 1)) {
        days.push(day);
    }

    return days;
}

function format(date, options) {
    return parseDate(date).toLocaleDateString('en-GB', options);
}

function rangeLabel(view, start, end, date) {
    if (view === 'month') {
        return format(date, { month: 'long', year: 'numeric' });
    }

    const sameMonth = start.slice(0, 7) === end.slice(0, 7);

    return `${format(start, sameMonth ? { day: '2-digit' } : { day: '2-digit', month: 'short' })} – ${format(end, { day: '2-digit', month: 'short', year: 'numeric' })}`;
}

/**
 * The menu chart: every day of the week (or month) against lunch and
 * dinner, planned and edited in place.
 */
export default function MenuChart({ view, date, start, end, today, menus, previous, items, mealTypes, canPrice, defaultPrices }) {
    const { values, setMany, loading } = useFilters(route('daily-menus.index'), { view, date });
    const [slot, setSlot] = useState(null);
    const [editorOpen, setEditorOpen] = useState(false);
    const [copying, setCopying] = useState(false);

    const days = useMemo(() => daysBetween(start, end), [start, end]);
    const bySlot = useMemo(() => Object.fromEntries(menus.map((menu) => [`${menu.date}|${menu.meal_type}`, menu])), [menus]);
    const mealLabel = Object.fromEntries(mealTypes.map((type) => [type.value, type.label]));
    const month = date.slice(0, 7);
    const countedDays = view === 'month' ? days.filter((day) => day.startsWith(month)) : days;
    const planned = countedDays.reduce((count, day) => count + mealTypes.filter((type) => bySlot[`${day}|${type.value}`]).length, 0);
    const slots = countedDays.length * mealTypes.length;
    const isCurrent = view === 'week' ? today >= start && today <= end : today.startsWith(month);

    function go(direction) {
        setMany({ date: view === 'week' ? shiftDate(values.date, 7 * direction) : `${shiftMonth(month, direction)}-01` });
    }

    function open(day, mealType) {
        setSlot({ date: day, meal_type: mealType });
        setEditorOpen(true);
    }

    // The latest earlier menu of the same meal, offered as "Same as …?".
    function suggestionFor(target) {
        const earlier = menus
            .filter((menu) => menu.meal_type === target.meal_type && menu.date < target.date)
            .sort((a, b) => b.date.localeCompare(a.date))[0];
        const before = previous[target.meal_type];

        return earlier ?? (before && before.date < target.date ? before : null);
    }

    function copyLastWeek() {
        router.post(route('daily-menus.copy-week'), { week: start }, { preserveScroll: true, onStart: () => setCopying(true), onFinish: () => setCopying(false) });
    }

    const editingMenu = slot ? bySlot[`${slot.date}|${slot.meal_type}`] : null;

    return (
        <>
            <Head title="Menu chart" />

            <PageHeader
                title="Menu chart"
                description="What is served each day. Tap a meal to plan or change it; clients see it on Today's Menu."
                actions={
                    view === 'week' && (
                        <Button variant="secondary" icon={CopyPlus} loading={copying} onClick={copyLastWeek} className="w-full sm:w-auto">
                            Copy last week
                        </Button>
                    )
                }
            />

            <div className="mb-5 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <div className="flex items-center gap-2">
                    <Button variant="secondary" size="icon" aria-label={view === 'week' ? 'Previous week' : 'Previous month'} onClick={() => go(-1)}>
                        <ChevronLeft className="size-5" aria-hidden="true" />
                    </Button>
                    <p className="min-w-0 flex-1 text-center text-base font-semibold text-slate-900 md:min-w-44 md:flex-none" aria-live="polite">
                        {rangeLabel(view, start, end, date)}
                    </p>
                    <Button variant="secondary" size="icon" aria-label={view === 'week' ? 'Next week' : 'Next month'} onClick={() => go(1)}>
                        <ChevronRight className="size-5" aria-hidden="true" />
                    </Button>
                    {!isCurrent && (
                        <Button
                            variant="ghost"
                            icon={CalendarCheck}
                            aria-label={view === 'week' ? 'This week' : 'This month'}
                            onClick={() => setMany({ date: today })}
                            className="px-3"
                        >
                            <span className="hidden sm:inline">{view === 'week' ? 'This week' : 'This month'}</span>
                        </Button>
                    )}
                </div>

                <div className="flex items-center gap-2">
                    <DateInput aria-label="Go to date" value={values.date} onChange={(event) => event.target.value && setMany({ date: event.target.value })} className="flex-1 md:w-40 md:flex-none" />
                    <div className="inline-flex rounded-lg bg-slate-100 p-1" role="group" aria-label="Chart view">
                        {[
                            { value: 'week', label: 'Week' },
                            { value: 'month', label: 'Month' },
                        ].map((option) => (
                            <button
                                key={option.value}
                                type="button"
                                aria-pressed={view === option.value}
                                onClick={() => setMany({ view: option.value })}
                                className={cn(
                                    'min-h-9 rounded-md px-4 text-sm font-medium transition-colors',
                                    view === option.value ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900',
                                )}
                            >
                                {option.label}
                            </button>
                        ))}
                    </div>
                </div>
            </div>

            <Card className="mb-5 flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:gap-6 sm:px-5">
                <div className="flex-1">
                    <div className="flex items-baseline justify-between gap-3 text-sm">
                        <span className="font-medium text-slate-700">
                            {planned} of {slots} meals planned {view === 'week' ? 'this week' : 'this month'}
                        </span>
                        <span className="text-xs text-slate-500 tabular-nums">{slots ? Math.round((planned / slots) * 100) : 0}%</span>
                    </div>
                    <div className="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100" aria-hidden="true">
                        <div className="h-full rounded-full bg-primary-500 transition-[width]" style={{ width: `${slots ? (planned / slots) * 100 : 0}%` }} />
                    </div>
                </div>
                <ul className="flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500" aria-label="Dish categories">
                    {CATEGORIES.map((category) => (
                        <li key={category.value} className="inline-flex items-center gap-1.5">
                            <span className={cn('size-2 rounded-full', category.dot)} aria-hidden="true" />
                            {category.label}
                        </li>
                    ))}
                </ul>
            </Card>

            <div className={cn('transition-opacity', loading && 'opacity-60')} aria-busy={loading || undefined}>
                {view === 'week' ? (
                    <WeekChart days={days} today={today} mealTypes={mealTypes} bySlot={bySlot} onOpen={open} />
                ) : (
                    <MonthChart
                        days={days}
                        month={month}
                        today={today}
                        mealTypes={mealTypes}
                        bySlot={bySlot}
                        onOpen={open}
                        onPickDay={(day) => setMany({ view: 'week', date: day })}
                    />
                )}
            </div>

            {slot && (
                <MenuEditor
                    key={`${slot.date}|${slot.meal_type}|${editingMenu?.id ?? 'new'}`}
                    open={editorOpen}
                    onClose={() => setEditorOpen(false)}
                    slot={slot}
                    menu={editingMenu}
                    suggestion={editingMenu ? null : suggestionFor(slot)}
                    items={items}
                    mealLabel={mealLabel[slot.meal_type]}
                    canPrice={canPrice}
                    defaultPrice={defaultPrices[slot.meal_type]}
                />
            )}
        </>
    );
}

function DayHeading({ day, today, className }) {
    const isToday = day === today;

    return (
        <div className={cn('flex items-center gap-2', className)}>
            <span className={cn('text-xs font-semibold tracking-wide uppercase', isToday ? 'text-primary-700' : 'text-slate-500')}>{format(day, { weekday: 'short' })}</span>
            <span
                className={cn(
                    'flex size-7 items-center justify-center rounded-full text-sm font-semibold tabular-nums',
                    isToday ? 'bg-primary-600 text-white' : 'text-slate-900',
                )}
            >
                {format(day, { day: 'numeric' })}
            </span>
        </div>
    );
}

/**
 * Seven days side by side on wide screens (meals as rows), one card per
 * day below that.
 */
function WeekChart({ days, today, mealTypes, bySlot, onOpen }) {
    return (
        <>
            <div className="hidden gap-x-2 gap-y-2 lg:grid lg:grid-cols-7">
                {days.map((day) => (
                    <DayHeading key={day} day={day} today={today} className={cn('rounded-lg px-2 py-1.5', day === today && 'bg-primary-50')} />
                ))}
                {mealTypes.map((type) => (
                    <Fragment key={type.value}>
                        <div className="col-span-7 mt-2 flex items-center gap-2">
                            <span className={cn('size-2 rounded-full', MEAL_DOTS[type.value])} aria-hidden="true" />
                            <h2 className="text-sm font-semibold text-slate-900">{type.label}</h2>
                            <span className="text-xs text-slate-500">{days.filter((day) => bySlot[`${day}|${type.value}`]).length} of 7 planned</span>
                        </div>
                        {days.map((day) => (
                            <MenuCell
                                key={`${day}-${type.value}`}
                                menu={bySlot[`${day}|${type.value}`]}
                                mealLabel={type.label}
                                past={day < today}
                                onOpen={() => onOpen(day, type.value)}
                                className={day === today ? 'ring-primary-300' : ''}
                            />
                        ))}
                    </Fragment>
                ))}
            </div>

            <div className="grid gap-3 md:grid-cols-2 lg:hidden">
                {days.map((day) => (
                    <Card key={day} as="section" aria-label={format(day, { weekday: 'long', day: 'numeric', month: 'long' })} className={cn('p-3', day === today && 'ring-2 ring-primary-400')}>
                        <div className="mb-2 flex items-center justify-between">
                            <DayHeading day={day} today={today} />
                            {day === today && <Badge tone="green">Today</Badge>}
                        </div>
                        <div className="space-y-2">
                            {mealTypes.map((type) => (
                                <div key={type.value}>
                                    <p className="mb-1 flex items-center gap-1.5 text-xs font-medium text-slate-500">
                                        <span className={cn('size-1.5 rounded-full', MEAL_DOTS[type.value])} aria-hidden="true" />
                                        {type.label}
                                    </p>
                                    <MenuCell compact menu={bySlot[`${day}|${type.value}`]} mealLabel={type.label} past={day < today} onOpen={() => onOpen(day, type.value)} />
                                </div>
                            ))}
                        </div>
                    </Card>
                ))}
            </div>
        </>
    );
}

/**
 * A calendar of the month: dish names on wide screens, a dot per planned
 * meal on phones (tap a day to open its week).
 */
function MonthChart({ days, month, today, mealTypes, bySlot, onOpen, onPickDay }) {
    const weekdays = days.slice(0, 7).map((day) => format(day, { weekday: 'short' }));

    return (
        <Card className="overflow-hidden">
            <div className="grid grid-cols-7 border-b border-slate-200 bg-slate-50">
                {weekdays.map((weekday) => (
                    <div key={weekday} className="px-2 py-2 text-center text-xs font-semibold tracking-wide text-slate-500 uppercase md:text-left">
                        {weekday}
                    </div>
                ))}
            </div>
            <div className="grid grid-cols-7">
                {days.map((day) => {
                    const inMonth = day.startsWith(month);
                    const isToday = day === today;

                    return (
                        <div key={day} className={cn('min-w-0 border-b border-r border-slate-100 [&:nth-child(7n)]:border-r-0', !inMonth && 'bg-slate-50/70')}>
                            {/* Phones: tap the day to plan it in the week view. */}
                            <button
                                type="button"
                                onClick={() => onPickDay(day)}
                                className="flex w-full flex-col items-center gap-1 py-2 md:hidden"
                                aria-label={`${format(day, { weekday: 'long', day: 'numeric', month: 'long' })}: ${mealTypes.filter((type) => bySlot[`${day}|${type.value}`]).map((type) => type.label).join(' and ') || 'nothing planned'}`}
                            >
                                <span
                                    className={cn(
                                        'flex size-7 items-center justify-center rounded-full text-sm tabular-nums',
                                        isToday ? 'bg-primary-600 font-semibold text-white' : inMonth ? 'text-slate-900' : 'text-slate-400',
                                    )}
                                >
                                    {format(day, { day: 'numeric' })}
                                </span>
                                <span className="flex h-1.5 gap-1" aria-hidden="true">
                                    {mealTypes.map((type) => (
                                        <span key={type.value} className={cn('size-1.5 rounded-full', bySlot[`${day}|${type.value}`] ? MEAL_DOTS[type.value] : 'bg-slate-200')} />
                                    ))}
                                </span>
                            </button>

                            {/* Wider screens: both meals, editable in place. */}
                            <div className="hidden min-h-32 flex-col gap-1 p-1.5 md:flex">
                                <span
                                    className={cn(
                                        'flex size-6 items-center justify-center rounded-full text-xs tabular-nums',
                                        isToday ? 'bg-primary-600 font-semibold text-white' : inMonth ? 'font-medium text-slate-700' : 'text-slate-400',
                                    )}
                                >
                                    {format(day, { day: 'numeric' })}
                                </span>
                                {mealTypes.map((type) => {
                                    const menu = bySlot[`${day}|${type.value}`];

                                    return (
                                        <button
                                            key={type.value}
                                            type="button"
                                            onClick={() => onOpen(day, type.value)}
                                            title={menu ? menu.items.map((item) => item.name).join(', ') : `Add ${type.label.toLowerCase()} menu`}
                                            className={cn(
                                                'flex min-h-9 min-w-0 items-start gap-1.5 rounded-md px-1.5 py-1 text-left text-xs transition-colors focus-visible:outline-2 focus-visible:outline-primary-600',
                                                menu ? 'bg-white ring-1 ring-slate-200 hover:ring-primary-300' : 'text-slate-400 hover:bg-primary-50 hover:text-primary-700',
                                            )}
                                        >
                                            <span className={cn('mt-1 size-1.5 shrink-0 rounded-full', menu ? MEAL_DOTS[type.value] : 'bg-slate-200')} aria-hidden="true" />
                                            <span className="min-w-0">
                                                <span className="sr-only">{type.label}: </span>
                                                {menu ? (
                                                    <span className="line-clamp-2 text-slate-700">{menu.items.map((item) => item.name).join(', ')}</span>
                                                ) : (
                                                    <span>{inMonth && day >= today ? `+ ${type.label}` : '—'}</span>
                                                )}
                                            </span>
                                        </button>
                                    );
                                })}
                            </div>
                        </div>
                    );
                })}
            </div>
        </Card>
    );
}
