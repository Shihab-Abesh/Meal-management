import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowRight, Store, UserCog } from 'lucide-react';
import Alert from '@/components/ui/alert';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import FormField from '@/components/ui/form-field';
import Input from '@/components/ui/input';
import MoneyInput from '@/components/ui/money-input';
import PageHeader from '@/components/ui/page-header';
import Toggle from '@/components/ui/toggle';

/**
 * Vendor settings. Each section saves on its own so a change in one never
 * resubmits the others.
 */
export default function Settings({ values, can, mailConfigured }) {
    return (
        <>
            <Head title="Settings" />

            <PageHeader title="Settings" description="Defaults that apply to every client unless the client has its own." />

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="min-w-0 space-y-6 lg:col-span-2">
                    <SettingsSection
                        section="meals"
                        title="Meal cutoff times"
                        description="After the cutoff, meals for that day can only be changed by someone allowed to override it. A client's own cutoff takes precedence."
                        initial={{ lunch_cutoff: values.lunch_cutoff, dinner_cutoff: values.dinner_cutoff }}
                    >
                        {({ data, setData, errors }) => (
                            <div className="grid gap-5 sm:grid-cols-2">
                                <FormField name="lunch_cutoff" label="Lunch cutoff" error={errors.lunch_cutoff} required>
                                    <Input type="time" value={data.lunch_cutoff} onChange={(event) => setData('lunch_cutoff', event.target.value)} />
                                </FormField>
                                <FormField name="dinner_cutoff" label="Dinner cutoff" error={errors.dinner_cutoff} required>
                                    <Input type="time" value={data.dinner_cutoff} onChange={(event) => setData('dinner_cutoff', event.target.value)} />
                                </FormField>
                            </div>
                        )}
                    </SettingsSection>

                    {can.pricing && (
                        <SettingsSection
                            section="pricing"
                            title="Default meal prices"
                            description="Used when a client has no price of its own and the day's menu has no price. Changes apply to meals confirmed from now on."
                            initial={{ lunch_price: values.lunch_price, dinner_price: values.dinner_price }}
                        >
                            {({ data, setData, errors }) => (
                                <div className="grid gap-5 sm:grid-cols-2">
                                    <FormField name="lunch_price" label="Lunch" error={errors.lunch_price} required>
                                        <MoneyInput value={data.lunch_price} onChange={(event) => setData('lunch_price', event.target.value)} />
                                    </FormField>
                                    <FormField name="dinner_price" label="Dinner" error={errors.dinner_price} required>
                                        <MoneyInput value={data.dinner_price} onChange={(event) => setData('dinner_price', event.target.value)} />
                                    </FormField>
                                </div>
                            )}
                        </SettingsSection>
                    )}

                    <SettingsSection
                        section="billing"
                        title="Billing"
                        description="How long clients have to pay a bill. Unpaid bills become overdue after this."
                        initial={{ due_days: values.due_days }}
                    >
                        {({ data, setData, errors }) => (
                            <FormField name="due_days" label="Payment due after" hint="Days after the bill is issued." error={errors.due_days} required className="sm:max-w-xs">
                                <Input type="number" inputMode="numeric" min="0" max="120" value={data.due_days} onChange={(event) => setData('due_days', event.target.value)} />
                            </FormField>
                        )}
                    </SettingsSection>

                    <SettingsSection
                        section="notifications"
                        title="Notifications"
                        description="Emails sent to a client's own accounts."
                        initial={{ bill_issued: values.bill_issued, payment_received: values.payment_received, order_delivered: values.order_delivered }}
                    >
                        {({ data, setData }) => (
                            <div className="space-y-4">
                                {!mailConfigured && (
                                    <Alert tone="info">
                                        Emails are currently written to the log file. Set the MAIL_ values in .env to send real emails.
                                    </Alert>
                                )}
                                <div className="divide-y divide-slate-100 rounded-lg ring-1 ring-slate-200">
                                    <Toggle className="px-4 py-2" label="New bill" description="When a bill is issued." checked={data.bill_issued} onChange={(value) => setData('bill_issued', value)} />
                                    <Toggle className="px-4 py-2" label="Payment received" description="A receipt when a payment is recorded." checked={data.payment_received} onChange={(value) => setData('payment_received', value)} />
                                    <Toggle className="px-4 py-2" label="Meals delivered" description="When the rider marks an order delivered." checked={data.order_delivered} onChange={(value) => setData('order_delivered', value)} />
                                </div>
                            </div>
                        )}
                    </SettingsSection>
                </div>

                <div className="min-w-0 space-y-6">
                    <Card>
                        <CardHeader title="Business information" />
                        <CardBody className="space-y-2">
                            <ShortcutLink href={route('vendor-profile.show')} icon={Store} label="Vendor profile" hint="Name, logo, contact details, VAT/TIN" />
                            {can.users && <ShortcutLink href={route('users.index')} icon={UserCog} label="Users and permissions" hint="Vendor admins and staff" />}
                        </CardBody>
                    </Card>
                </div>
            </div>
        </>
    );
}

function SettingsSection({ section, title, description, initial, children }) {
    const form = useForm({ section, ...initial });

    function submit(event) {
        event.preventDefault();
        // Saved values become the new baseline, so Save disables again.
        form.put(route('settings.update'), { preserveScroll: true, onSuccess: () => form.setDefaults() });
    }

    return (
        <Card as="form" onSubmit={submit} noValidate>
            <CardHeader title={title} description={description} />
            <CardBody>{children(form)}</CardBody>
            <div className="flex justify-end border-t border-slate-100 px-4 py-3 sm:px-6">
                <Button type="submit" loading={form.processing} disabled={!form.isDirty} className="w-full sm:w-auto">
                    Save
                </Button>
            </div>
        </Card>
    );
}

function ShortcutLink({ href, icon: Icon, label, hint }) {
    return (
        <Link href={href} className="-mx-2 flex min-h-11 items-center gap-3 rounded-lg px-2 py-2 hover:bg-slate-50">
            <span className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600">
                <Icon className="size-5" aria-hidden="true" />
            </span>
            <span className="min-w-0 flex-1">
                <span className="block text-sm font-medium text-slate-900">{label}</span>
                <span className="block truncate text-xs text-slate-500">{hint}</span>
            </span>
            <ArrowRight className="size-4 shrink-0 text-slate-400" aria-hidden="true" />
        </Link>
    );
}
