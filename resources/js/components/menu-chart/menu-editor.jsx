import { router, useForm, usePage } from '@inertiajs/react';
import { ArrowDown, ArrowUp, Check, Copy, Trash2, X } from 'lucide-react';
import { useMemo, useState } from 'react';
import { categoryDot, categoryIndex } from '@/components/menu-chart/categories';
import Button from '@/components/ui/button';
import DateInput from '@/components/ui/date-input';
import FormField, { FieldError } from '@/components/ui/form-field';
import Modal from '@/components/ui/modal';
import MoneyInput from '@/components/ui/money-input';
import SearchInput from '@/components/ui/search-input';
import Textarea from '@/components/ui/textarea';
import { useApp } from '@/hooks/use-app';
import { formatDate, formatLongDate } from '@/lib/format';
import { cn } from '@/lib/utils';

/**
 * Plan or change one meal of the chart without leaving it: pick dishes
 * (in serving order), set an optional price and note, copy an earlier menu
 * or copy this one to another day, or delete it.
 */
export default function MenuEditor({ open, onClose, slot, menu, suggestion, items, mealLabel, canPrice, defaultPrice }) {
    const { money } = useApp();
    // Errors from the copy action arrive with the page, not this form.
    const { errors: pageErrors = {} } = usePage().props;
    const [search, setSearch] = useState('');
    const [copyDate, setCopyDate] = useState('');
    const [confirmDelete, setConfirmDelete] = useState(false);
    const [busy, setBusy] = useState(null);
    const form = useForm({
        menu_date: slot.date,
        meal_type: slot.meal_type,
        items: menu?.items.map((item) => item.id) ?? [],
        price: menu?.price ?? '',
        notes: menu?.notes ?? '',
    });
    const { data, setData, errors, processing } = form;
    const formId = 'menu-editor-form';

    const itemsById = useMemo(() => Object.fromEntries(items.map((item) => [item.id, item])), [items]);

    // Disabled items stay visible only when they are already on this menu.
    const groups = useMemo(() => {
        const term = search.trim().toLowerCase();
        const grouped = {};

        for (const item of items) {
            if ((!item.active && !data.items.includes(item.id)) || (term && !item.name.toLowerCase().includes(term))) {
                continue;
            }

            (grouped[item.category] ??= { label: item.category_label, items: [] }).items.push(item);
        }

        return Object.entries(grouped)
            .sort(([a], [b]) => categoryIndex(a) - categoryIndex(b))
            .map(([category, group]) => ({ category, ...group }));
    }, [items, search, data.items]);

    function toggle(id) {
        setData('items', data.items.includes(id) ? data.items.filter((itemId) => itemId !== id) : [...data.items, id]);
    }

    function move(index, direction) {
        const next = [...data.items];
        [next[index], next[index + direction]] = [next[index + direction], next[index]];
        setData('items', next);
    }

    function applySuggestion() {
        setData((current) => ({
            ...current,
            items: suggestion.items.map((item) => item.id).filter((id) => itemsById[id]),
            price: canPrice && suggestion.price ? suggestion.price : current.price,
            notes: suggestion.notes ?? current.notes,
        }));
    }

    function submit(event) {
        event.preventDefault();
        const options = { preserveScroll: true, onSuccess: onClose };

        if (menu) {
            form.put(route('daily-menus.update', menu.id), options);
        } else {
            form.post(route('daily-menus.store'), options);
        }
    }

    function copyTo() {
        router.post(
            route('daily-menus.duplicate', menu.id),
            { date: copyDate },
            { preserveScroll: true, onStart: () => setBusy('copy'), onFinish: () => setBusy(null), onSuccess: () => setCopyDate('') },
        );
    }

    function destroy() {
        router.delete(route('daily-menus.destroy', menu.id), {
            preserveScroll: true,
            onStart: () => setBusy('delete'),
            onFinish: () => setBusy(null),
            onSuccess: onClose,
        });
    }

    const itemsError = errors.items ?? Object.entries(errors).find(([key]) => key.startsWith('items.'))?.[1];

    return (
        <Modal
            open={open}
            onClose={onClose}
            size="lg"
            title={`${mealLabel} · ${formatLongDate(slot.date)}`}
            description={menu ? `${menu.items.length} dishes planned` : 'Choose the dishes for this meal.'}
            footer={
                <>
                    {menu &&
                        (confirmDelete ? (
                            <Button variant="danger" icon={Trash2} loading={busy === 'delete'} onClick={destroy} className="w-full sm:mr-auto sm:w-auto">
                                Delete this menu
                            </Button>
                        ) : (
                            <Button variant="ghost" icon={Trash2} onClick={() => setConfirmDelete(true)} className="w-full text-red-600 hover:text-red-700 sm:mr-auto sm:w-auto">
                                Delete
                            </Button>
                        ))}
                    <Button variant="secondary" onClick={onClose} className="w-full sm:w-auto">
                        Cancel
                    </Button>
                    <Button type="submit" form={formId} loading={processing} disabled={data.items.length === 0} className="w-full sm:w-auto">
                        Save menu
                    </Button>
                </>
            }
        >
            <form id={formId} onSubmit={submit} noValidate className="space-y-6">
                {(errors.meal_type || errors.menu_date) && <FieldError message={errors.meal_type ?? errors.menu_date} />}

                {suggestion && data.items.length === 0 && (
                    <div className="flex flex-col gap-3 rounded-lg bg-primary-50/70 p-3 ring-1 ring-primary-100 sm:flex-row sm:items-center sm:justify-between">
                        <p className="text-sm text-slate-700">
                            Same as <span className="font-semibold text-slate-900">{formatDate(suggestion.date)}</span>?{' '}
                            <span className="text-slate-500">{suggestion.items.map((item) => item.name).join(', ')}</span>
                        </p>
                        <Button size="sm" variant="secondary" icon={Copy} onClick={applySuggestion} className="shrink-0">
                            Use it
                        </Button>
                    </div>
                )}

                <section aria-labelledby="menu-editor-selected">
                    <h3 id="menu-editor-selected" className="text-sm font-semibold text-slate-900">
                        On the menu {data.items.length > 0 && <span className="font-normal text-slate-500">· {data.items.length}</span>}
                    </h3>
                    {data.items.length === 0 ? (
                        <p className="mt-2 rounded-lg border border-dashed border-slate-300 px-3 py-4 text-center text-sm text-slate-500">
                            Tap dishes below to add them. They are served in the order you add them.
                        </p>
                    ) : (
                        <ol className="mt-2 divide-y divide-slate-100 rounded-lg ring-1 ring-slate-200">
                            {data.items.map((id, index) => (
                                <li key={id} className="flex items-center gap-2 py-1 pr-1 pl-3">
                                    <span className="w-4 text-xs text-slate-400 tabular-nums">{index + 1}</span>
                                    <span className={cn('size-2 shrink-0 rounded-full', categoryDot(itemsById[id]?.category))} aria-hidden="true" />
                                    <span className="min-w-0 flex-1 truncate text-sm font-medium text-slate-900">{itemsById[id]?.name ?? `Item ${id}`}</span>
                                    <IconButton label="Move up" disabled={index === 0} onClick={() => move(index, -1)} icon={ArrowUp} />
                                    <IconButton label="Move down" disabled={index === data.items.length - 1} onClick={() => move(index, 1)} icon={ArrowDown} />
                                    <IconButton label={`Remove ${itemsById[id]?.name ?? ''}`} onClick={() => toggle(id)} icon={X} />
                                </li>
                            ))}
                        </ol>
                    )}
                    <FieldError message={itemsError} />
                </section>

                <section aria-labelledby="menu-editor-dishes" className="space-y-3">
                    <div className="flex items-center justify-between gap-3">
                        <h3 id="menu-editor-dishes" className="text-sm font-semibold text-slate-900">
                            Dishes
                        </h3>
                    </div>
                    <SearchInput value={search} onChange={setSearch} placeholder="Find a dish…" label="Find a dish" />
                    {groups.length === 0 ? (
                        <p className="py-4 text-center text-sm text-slate-500">{items.length === 0 ? 'No menu items yet. Add dishes under Menu Items first.' : 'No dishes match your search.'}</p>
                    ) : (
                        <div className="space-y-4">
                            {groups.map((group) => (
                                <fieldset key={group.category}>
                                    <legend className="mb-2 flex items-center gap-1.5 text-xs font-semibold tracking-wide text-slate-500 uppercase">
                                        <span className={cn('size-2 rounded-full', categoryDot(group.category))} aria-hidden="true" />
                                        {group.label}
                                    </legend>
                                    <div className="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                        {group.items.map((item) => {
                                            const selected = data.items.includes(item.id);

                                            return (
                                                <button
                                                    key={item.id}
                                                    type="button"
                                                    role="checkbox"
                                                    aria-checked={selected}
                                                    onClick={() => toggle(item.id)}
                                                    className={cn(
                                                        'flex min-h-11 items-center gap-2 rounded-lg px-2.5 py-2 text-left text-sm ring-1 transition-colors ring-inset focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600',
                                                        selected ? 'bg-primary-50 font-medium text-primary-900 ring-primary-500' : 'bg-white text-slate-700 ring-slate-200 hover:bg-slate-50',
                                                    )}
                                                >
                                                    <span
                                                        className={cn(
                                                            'flex size-4.5 shrink-0 items-center justify-center rounded ring-1 ring-inset',
                                                            selected ? 'bg-primary-600 text-white ring-primary-600' : 'ring-slate-300',
                                                        )}
                                                        aria-hidden="true"
                                                    >
                                                        {selected && <Check className="size-3" strokeWidth={3} />}
                                                    </span>
                                                    <span className="min-w-0 truncate">{item.name}</span>
                                                </button>
                                            );
                                        })}
                                    </div>
                                </fieldset>
                            ))}
                        </div>
                    )}
                </section>

                <section className="grid gap-5 sm:grid-cols-2">
                    {canPrice ? (
                        <FormField
                            name="menu_price"
                            label="Menu price"
                            hint={`Optional. Empty uses the default (${money(defaultPrice)}). Clients with their own price always pay that.`}
                            error={errors.price}
                        >
                            <MoneyInput value={data.price ?? ''} onChange={(event) => setData('price', event.target.value)} />
                        </FormField>
                    ) : (
                        menu?.price && (
                            <div>
                                <p className="text-sm font-medium text-slate-700">Menu price</p>
                                <p className="mt-2 text-sm text-slate-900">{money(menu.price)}</p>
                            </div>
                        )
                    )}
                    <FormField name="menu_notes" label="Note for clients" hint="e.g. Special Friday menu." error={errors.notes} className={canPrice || menu?.price ? '' : 'sm:col-span-2'}>
                        <Textarea rows={2} value={data.notes ?? ''} onChange={(event) => setData('notes', event.target.value)} />
                    </FormField>
                </section>

                {menu && (
                    <section className="rounded-lg bg-slate-50 p-3">
                        <p className="text-sm font-medium text-slate-700">Copy this menu to another day</p>
                        <div className="mt-2 flex gap-2">
                            <DateInput aria-label="Copy to date" value={copyDate} onChange={(event) => setCopyDate(event.target.value)} className="flex-1" />
                            <Button variant="secondary" icon={Copy} disabled={!copyDate} loading={busy === 'copy'} onClick={copyTo}>
                                Copy
                            </Button>
                        </div>
                        <FieldError message={pageErrors.date} />
                    </section>
                )}
            </form>
        </Modal>
    );
}

function IconButton({ label, icon: Icon, onClick, disabled = false }) {
    return (
        <button
            type="button"
            onClick={onClick}
            disabled={disabled}
            className="flex size-9 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-2 focus-visible:outline-primary-600 disabled:opacity-30"
        >
            <span className="sr-only">{label}</span>
            <Icon className="size-4" aria-hidden="true" />
        </button>
    );
}
