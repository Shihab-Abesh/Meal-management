import { Head, Link, router, useForm } from '@inertiajs/react';
import { FileText } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import BillLinesTable from '@/components/billing/bill-lines-table';
import Alert from '@/components/ui/alert';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import DateInput from '@/components/ui/date-input';
import EmptyState from '@/components/ui/empty-state';
import FormActions from '@/components/ui/form-actions';
import FormField from '@/components/ui/form-field';
import LoadingState from '@/components/ui/loading-state';
import PageHeader from '@/components/ui/page-header';
import Select from '@/components/ui/select';
import Textarea from '@/components/ui/textarea';
import { formatDate, formatNumber } from '@/lib/format';

function monthRange(month) {
    const [year, number] = month.split('-').map(Number);
    const last = new Date(year, number, 0).getDate();

    return { start: `${month}-01`, end: `${month}-${String(last).padStart(2, '0')}` };
}

/**
 * Generate a bill. Choosing the client and period shows a live preview of
 * the meals and amount, calculated on the server from confirmed meals.
 */
export default function CreateBill({ clients, values, preview, lastBill, dueDays, today }) {
    const { data, setData, post, processing, errors } = useForm({
        client_id: values.client_id,
        period_start: values.period_start,
        period_end: values.period_end,
        notes: '',
    });
    const [loadingPreview, setLoadingPreview] = useState(false);
    const first = useRef(true);
    const month = data.period_start?.slice(0, 7) ?? '';
    const isWholeMonth = data.period_start === monthRange(month).start && data.period_end === monthRange(month).end;

    // Refresh the preview whenever the client or period changes.
    useEffect(() => {
        if (first.current) {
            first.current = false;

            return;
        }

        router.get(
            route('bills.create'),
            { client: data.client_id || undefined, start: data.period_start || undefined, end: data.period_end || undefined },
            {
                only: ['preview', 'lastBill'],
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onStart: () => setLoadingPreview(true),
                onFinish: () => setLoadingPreview(false),
            },
        );
    }, [data.client_id, data.period_start, data.period_end]);

    function submit(event) {
        event.preventDefault();
        post(route('bills.store'));
    }

    const dueDate = new Date();
    dueDate.setDate(dueDate.getDate() + dueDays);

    return (
        <>
            <Head title="Generate bill" />

            <PageHeader
                title="Generate bill"
                description="Totals come from the confirmed meals in the period, at the price each meal was confirmed at."
                back={{ href: route('bills.index'), label: 'Bills' }}
            />

            <form onSubmit={submit} noValidate className="space-y-6">
                <div className="grid gap-6 lg:grid-cols-5">
                    <Card className="min-w-0 lg:col-span-2">
                        <CardHeader title="Client and period" />
                        <CardBody className="space-y-5">
                            <FormField name="client_id" label="Client" error={errors.client_id} required>
                                <Select placeholder="Choose a client" options={clients} value={data.client_id} onChange={(event) => setData('client_id', event.target.value)} />
                            </FormField>
                            <FormField name="month" label="Month" hint="Fills in the whole month. Adjust the dates for a custom period.">
                                <DateInput
                                    type="month"
                                    value={month}
                                    onChange={(event) => {
                                        if (event.target.value) {
                                            const range = monthRange(event.target.value);
                                            setData((current) => ({ ...current, period_start: range.start, period_end: range.end }));
                                        }
                                    }}
                                />
                            </FormField>
                            <div className="grid grid-cols-2 gap-3">
                                <FormField name="period_start" label="From" error={errors.period_start} required>
                                    <DateInput value={data.period_start} onChange={(event) => setData('period_start', event.target.value)} />
                                </FormField>
                                <FormField name="period_end" label="To" error={errors.period_end} required>
                                    <DateInput value={data.period_end} min={data.period_start} onChange={(event) => setData('period_end', event.target.value)} />
                                </FormField>
                            </div>
                            {lastBill && (
                                <p className="text-xs text-slate-500">
                                    Last bill {lastBill.bill_number} covered {formatDate(lastBill.period_start)} – {formatDate(lastBill.period_end)}.
                                </p>
                            )}
                            {data.period_end > today && (
                                <Alert tone="info">The period runs past today. Meals added later will need a recalculation.</Alert>
                            )}
                            <FormField name="notes" label="Note on the bill" error={errors.notes}>
                                <Textarea rows={2} value={data.notes} onChange={(event) => setData('notes', event.target.value)} />
                            </FormField>
                        </CardBody>
                    </Card>

                    <Card className="min-w-0 lg:col-span-3" aria-busy={loadingPreview || undefined}>
                        <CardHeader
                            title="Preview"
                            description={data.client_id && preview ? (isWholeMonth ? 'Whole month' : `${formatDate(data.period_start)} – ${formatDate(data.period_end)}`) : undefined}
                        />
                        <div className={loadingPreview ? 'opacity-60 transition-opacity' : 'transition-opacity'}>
                            {!preview && loadingPreview ? (
                                <LoadingState label="Calculating the bill…" />
                            ) : !preview ? (
                                <EmptyState icon={FileText} title="Choose a client" description="The meals and amount for the period appear here." />
                            ) : preview.total_meals === 0 ? (
                                <EmptyState icon={FileText} title="No confirmed meals" description="There is nothing to bill for this client in that period." />
                            ) : (
                                <>
                                    {preview.overlaps && (
                                        <div className="px-4 pt-4 sm:px-6">
                                            <Alert tone="error">This period overlaps bill {preview.overlaps}. Meals can only be billed once.</Alert>
                                        </div>
                                    )}
                                    <BillLinesTable lines={preview.lines} totalMeals={preview.total_meals} totalAmount={preview.total_amount} />
                                    <p className="border-t border-slate-100 px-4 py-3 text-sm text-slate-500 sm:px-6">
                                        {formatNumber(preview.lunch_meals)} lunch · {formatNumber(preview.dinner_meals)} dinner · payable by{' '}
                                        {formatDate(dueDate)}
                                    </p>
                                </>
                            )}
                        </div>
                    </Card>
                </div>

                <FormActions>
                    <Button as={Link} href={route('bills.index')} variant="secondary">
                        Cancel
                    </Button>
                    <Button type="submit" loading={processing} disabled={!preview || preview.total_meals === 0 || Boolean(preview.overlaps) || loadingPreview}>
                        Generate bill
                    </Button>
                </FormActions>
            </form>
        </>
    );
}
