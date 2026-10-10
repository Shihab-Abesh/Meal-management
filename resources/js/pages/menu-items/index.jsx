import { Head, router, useForm, usePage } from '@inertiajs/react';
import { ChefHat, Pencil, Plus, Power, PowerOff, SearchX, Trash2 } from 'lucide-react';
import { useState } from 'react';
import ActionMenu from '@/components/ui/action-menu';
import Alert from '@/components/ui/alert';
import Avatar from '@/components/ui/avatar';
import Badge from '@/components/ui/badge';
import Button from '@/components/ui/button';
import Card from '@/components/ui/card';
import ConfirmDialog from '@/components/ui/confirm-dialog';
import DataTable from '@/components/ui/data-table';
import { DropdownItem } from '@/components/ui/dropdown';
import EmptyState from '@/components/ui/empty-state';
import FilterPanel, { FilterField } from '@/components/ui/filter-panel';
import FormField from '@/components/ui/form-field';
import ImageUpload from '@/components/ui/image-upload';
import Input from '@/components/ui/input';
import Modal from '@/components/ui/modal';
import MoneyInput from '@/components/ui/money-input';
import PageHeader from '@/components/ui/page-header';
import Pagination from '@/components/ui/pagination';
import SearchInput from '@/components/ui/search-input';
import Select from '@/components/ui/select';
import StatusBadge from '@/components/ui/status-badge';
import Textarea from '@/components/ui/textarea';
import { useApp } from '@/hooks/use-app';
import { useFilters } from '@/hooks/use-filters';

const FILTER_KEYS = ['category', 'status'];

export default function MenuItemsIndex({ items, filters, categories, statuses, imageRules }) {
    const { money } = useApp();
    const { errors: pageErrors = {} } = usePage().props;
    const { values, set, reset, loading } = useFilters(route('menu-items.index'), filters);
    const [editing, setEditing] = useState(null);
    const [formOpen, setFormOpen] = useState(false);
    const [deleting, setDeleting] = useState(null);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [processing, setProcessing] = useState(false);
    const activeFilters = FILTER_KEYS.filter((key) => values[key]).length;

    function openForm(item = null) {
        setEditing(item);
        setFormOpen(true);
    }

    function setStatus(item, status) {
        router.patch(route('menu-items.status', item.id), { status }, { preserveScroll: true });
    }

    function destroy() {
        router.delete(route('menu-items.destroy', deleting.id), {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setDeleteOpen(false);
            },
        });
    }

    const actions = (item) => (
        <ActionMenu label={`Actions for ${item.name}`}>
            <DropdownItem icon={Pencil} onClick={() => openForm(item)}>
                Edit
            </DropdownItem>
            {item.status === 'active' ? (
                <DropdownItem icon={PowerOff} onClick={() => setStatus(item, 'inactive')}>
                    Disable
                </DropdownItem>
            ) : (
                <DropdownItem icon={Power} onClick={() => setStatus(item, 'active')}>
                    Enable
                </DropdownItem>
            )}
            {item.menus_count === 0 && (
                <DropdownItem
                    icon={Trash2}
                    tone="danger"
                    onClick={() => {
                        setDeleting(item);
                        setDeleteOpen(true);
                    }}
                >
                    Delete
                </DropdownItem>
            )}
        </ActionMenu>
    );

    const columns = [
        {
            key: 'name',
            header: 'Item',
            primary: true,
            render: (item) => (
                <span className="flex min-w-0 items-center gap-3">
                    <Avatar name={item.name} src={item.image_url} size="md" rounded="xl" className="ring-1 ring-slate-200" />
                    <span className="min-w-0 max-w-56 xl:max-w-80">
                        <span className="block truncate font-medium text-slate-900">{item.name}</span>
                        {item.description && <span className="block truncate text-xs text-slate-500">{item.description}</span>}
                    </span>
                </span>
            ),
        },
        { key: 'category', header: 'Category', className: 'hidden xl:table-cell', render: (item) => <Badge>{item.category_label}</Badge> },
        { key: 'default_price', header: 'Default price', align: 'right', render: (item) => money(item.default_price) },
        { key: 'menus_count', header: 'Used on', align: 'right', className: 'hidden lg:table-cell', render: (item) => `${item.menus_count} menus` },
        { key: 'status', header: 'Status', render: (item) => <StatusBadge status={item.status} label={item.status === 'active' ? 'Enabled' : 'Disabled'} /> },
    ];

    return (
        <>
            <Head title="Menu items" />

            <PageHeader
                title="Menu items"
                description="Dishes and drinks you can put on a daily menu."
                actions={
                    <Button icon={Plus} onClick={() => openForm()} className="w-full sm:w-auto">
                        Add item
                    </Button>
                }
            />

            {pageErrors.item && (
                <Alert tone="error" className="mb-4">
                    {pageErrors.item}
                </Alert>
            )}

            <Card>
                <div className="flex gap-2 border-b border-slate-100 p-4 sm:px-6 md:items-end">
                    <SearchInput
                        value={values.search}
                        onChange={(value) => set('search', value, { debounce: true })}
                        placeholder="Search items…"
                        className="min-w-0 flex-1 md:max-w-xs"
                    />
                    <FilterPanel activeCount={activeFilters} onReset={() => reset(FILTER_KEYS)}>
                        <FilterField label="Category" htmlFor="filter-category">
                            <Select
                                id="filter-category"
                                placeholder="All categories"
                                options={categories}
                                value={values.category}
                                onChange={(event) => set('category', event.target.value)}
                            />
                        </FilterField>
                        <FilterField label="Status" htmlFor="filter-status">
                            <Select
                                id="filter-status"
                                placeholder="Enabled & disabled"
                                options={[
                                    { value: 'active', label: 'Enabled' },
                                    { value: 'inactive', label: 'Disabled' },
                                ]}
                                value={values.status}
                                onChange={(event) => set('status', event.target.value)}
                            />
                        </FilterField>
                    </FilterPanel>
                </div>

                <DataTable
                    caption="Menu items"
                    columns={columns}
                    rows={items.data}
                    loading={loading}
                    actions={actions}
                    mobileCard={(item) => (
                        <div className="flex items-center gap-3">
                            <Avatar name={item.name} src={item.image_url} size="md" rounded="xl" className="ring-1 ring-slate-200" />
                            <div className="min-w-0 flex-1">
                                <p className="truncate font-semibold text-slate-900">{item.name}</p>
                                <p className="truncate text-sm text-slate-500">
                                    {item.category_label} · {money(item.default_price)}
                                </p>
                                {item.status !== 'active' && <StatusBadge status="inactive" label="Disabled" className="mt-1" />}
                            </div>
                        </div>
                    )}
                    empty={
                        activeFilters > 0 || values.search ? (
                            <EmptyState
                                icon={SearchX}
                                title="No items found"
                                action={
                                    <Button variant="secondary" onClick={() => reset(['search', ...FILTER_KEYS])}>
                                        Clear search and filters
                                    </Button>
                                }
                            />
                        ) : (
                            <EmptyState
                                icon={ChefHat}
                                title="No menu items yet"
                                description="Add dishes such as Rice, Chicken Curry or Dal, then build daily menus from them."
                                action={
                                    <Button icon={Plus} onClick={() => openForm()}>
                                        Add your first item
                                    </Button>
                                }
                            />
                        )
                    }
                />

                <Pagination meta={items.meta} links={items.links} />
            </Card>

            <MenuItemForm
                key={editing?.id ?? 'new'}
                open={formOpen}
                onClose={() => setFormOpen(false)}
                item={editing}
                categories={categories}
                statuses={statuses}
                imageRules={imageRules}
            />

            <ConfirmDialog
                open={deleteOpen}
                onClose={() => setDeleteOpen(false)}
                onConfirm={destroy}
                processing={processing}
                title="Delete menu item?"
                description={deleting && `${deleting.name} will be removed permanently.`}
                confirmLabel="Delete"
            />
        </>
    );
}

function MenuItemForm({ open, onClose, item, categories, statuses, imageRules }) {
    const { data, setData, post, processing, errors, reset, progress } = useForm({
        _method: item ? 'put' : 'post',
        name: item?.name ?? '',
        category: item?.category ?? 'main_course',
        description: item?.description ?? '',
        default_price: item?.default_price ?? '',
        status: item?.status ?? 'active',
        image: null,
        remove_image: false,
    });
    const formId = 'menu-item-form';

    function submit(event) {
        event.preventDefault();
        post(item ? route('menu-items.update', item.id) : route('menu-items.store'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                reset();
                onClose();
            },
        });
    }

    return (
        <Modal
            open={open}
            onClose={onClose}
            title={item ? `Edit ${item.name}` : 'Add menu item'}
            footer={
                <>
                    <Button variant="secondary" onClick={onClose} className="w-full sm:w-auto">
                        Cancel
                    </Button>
                    <Button type="submit" form={formId} loading={processing} className="w-full sm:w-auto">
                        {item ? 'Save changes' : 'Add item'}
                    </Button>
                </>
            }
        >
            <form id={formId} onSubmit={submit} noValidate className="space-y-5">
                <FormField name="name" label="Item name" error={errors.name} required>
                    <Input value={data.name} onChange={(event) => setData('name', event.target.value)} placeholder="e.g. Chicken Curry" />
                </FormField>
                <div className="grid gap-5 sm:grid-cols-2">
                    <FormField name="category" label="Category" error={errors.category} required>
                        <Select options={categories} value={data.category} onChange={(event) => setData('category', event.target.value)} />
                    </FormField>
                    <FormField name="default_price" label="Default price" error={errors.default_price} required>
                        <MoneyInput value={data.default_price} onChange={(event) => setData('default_price', event.target.value)} />
                    </FormField>
                </div>
                <FormField name="description" label="Description" error={errors.description}>
                    <Textarea rows={2} value={data.description} onChange={(event) => setData('description', event.target.value)} />
                </FormField>
                <FormField name="status" label="Status" error={errors.status} required>
                    <Select
                        options={statuses.map((status) => ({ ...status, label: status.value === 'active' ? 'Enabled' : 'Disabled' }))}
                        value={data.status}
                        onChange={(event) => setData('status', event.target.value)}
                    />
                </FormField>
                <ImageUpload
                    label="Photo (optional)"
                    name={data.name}
                    file={data.image}
                    currentUrl={item?.image_url}
                    removed={data.remove_image}
                    accept={imageRules.mimes.map((type) => `.${type}`).join(',')}
                    hint={`${imageRules.mimes.join(', ').toUpperCase()}, up to ${Math.round(imageRules.max_kb / 1024)} MB.`}
                    error={errors.image}
                    onSelect={(file) => setData((current) => ({ ...current, image: file, remove_image: false }))}
                    onRemove={() => setData((current) => ({ ...current, image: null, remove_image: true }))}
                />
                {progress && (
                    <progress value={progress.percentage} max="100" className="h-1.5 w-full accent-primary-600">
                        {progress.percentage}%
                    </progress>
                )}
            </form>
        </Modal>
    );
}
