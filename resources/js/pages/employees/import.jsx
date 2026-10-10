import { Head, useForm, usePage } from '@inertiajs/react';
import { CircleAlert, CircleCheck, Copy, Download, FileSpreadsheet, Upload } from 'lucide-react';
import { useId } from 'react';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import FormField, { FieldError } from '@/components/ui/form-field';
import PageHeader from '@/components/ui/page-header';
import Select from '@/components/ui/select';
import { formatNumber } from '@/lib/format';
import { cn } from '@/lib/utils';

export default function ImportEmployees({ clients, selectedClient, columns, maxRows }) {
    const { flash } = usePage();
    const result = flash?.importResult;
    const fileInputId = useId();
    const { data, setData, post, processing, errors, progress, reset } = useForm({
        client_id: selectedClient ? String(selectedClient) : '',
        file: null,
    });

    function submit(event) {
        event.preventDefault();
        post(route('employees.import.store'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => reset('file'),
        });
    }

    return (
        <>
            <Head title="Import employees" />

            <PageHeader
                title="Import employees"
                description="Add many employees at once from a spreadsheet saved as CSV."
                back={{ href: route('employees.index', clients && data.client_id ? { client: data.client_id } : {}), label: 'Employees' }}
            />

            {result && <ImportResult result={result} />}

            <div className="grid gap-6 lg:grid-cols-3">
                <Card as="form" onSubmit={submit} noValidate className="lg:col-span-2">
                    <CardHeader title="Upload file" />
                    <CardBody className="space-y-5">
                        {clients && (
                            <FormField name="client_id" label="Import into client" error={errors.client_id} required>
                                <Select
                                    placeholder="Choose a client"
                                    options={clients}
                                    value={data.client_id}
                                    onChange={(event) => setData('client_id', event.target.value)}
                                />
                            </FormField>
                        )}

                        <div>
                            <input
                                id={fileInputId}
                                type="file"
                                accept=".csv,text/csv"
                                className="peer sr-only"
                                onChange={(event) => setData('file', event.target.files?.[0] ?? null)}
                            />
                            <label
                                htmlFor={fileInputId}
                                className={cn(
                                    'flex cursor-pointer flex-col items-center gap-2 rounded-xl border-2 border-dashed px-4 py-8 text-center transition-colors peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-primary-600 hover:bg-slate-50',
                                    errors.file ? 'border-red-300' : 'border-slate-300',
                                )}
                            >
                                <FileSpreadsheet className="size-8 text-slate-400" aria-hidden="true" />
                                {data.file ? (
                                    <span className="text-sm font-medium break-all text-slate-900">{data.file.name}</span>
                                ) : (
                                    <span className="text-sm font-medium text-slate-900">Tap to choose a CSV file</span>
                                )}
                                <span className="text-xs text-slate-500">CSV, up to 2 MB and {formatNumber(maxRows)} rows</span>
                            </label>
                            <div className="mt-1.5">
                                <FieldError message={errors.file} />
                            </div>
                        </div>

                        {progress && (
                            <progress value={progress.percentage} max="100" className="h-1.5 w-full accent-primary-600">
                                {progress.percentage}%
                            </progress>
                        )}

                        <Button type="submit" icon={Upload} loading={processing} disabled={!data.file} className="w-full sm:w-auto">
                            Import employees
                        </Button>
                    </CardBody>
                </Card>

                <Card className="h-fit">
                    <CardHeader title="File format" />
                    <CardBody className="space-y-4 text-sm text-slate-600">
                        <p>
                            The first row must contain column names. <strong>employee_id</strong> and <strong>name</strong> are
                            required; other columns are optional.
                        </p>
                        <div className="flex flex-wrap gap-1.5">
                            {columns.map((column) => (
                                <code key={column} className="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-700">
                                    {column}
                                </code>
                            ))}
                        </div>
                        <ul className="list-disc space-y-1 pl-5 text-xs">
                            <li>lunch / dinner: yes or no (default: lunch yes, dinner no)</li>
                            <li>meal_preference: regular, vegetarian, no_beef, no_fish or diabetic</li>
                            <li>joining_date: YYYY-MM-DD or DD/MM/YYYY</li>
                            <li>Existing employee IDs are skipped, never overwritten.</li>
                        </ul>
                        <Button as="a" href={route('employees.import.template')} variant="secondary" icon={Download} className="w-full">
                            Download template
                        </Button>
                    </CardBody>
                </Card>
            </div>
        </>
    );
}

function ImportResult({ result }) {
    const summary = [
        { label: 'Rows in file', value: result.total, color: 'text-slate-900' },
        { label: 'Imported', value: result.imported, color: 'text-emerald-700' },
        { label: 'Duplicates skipped', value: result.duplicates.length, color: 'text-amber-700' },
        { label: 'Failed', value: result.failed.length, color: 'text-red-700' },
    ];

    return (
        <Card className="mb-6">
            <CardHeader
                title="Import result"
                description={`Imported into ${result.client}.`}
                actions={
                    result.imported > 0 ? (
                        <CircleCheck className="size-6 text-emerald-600" aria-hidden="true" />
                    ) : (
                        <CircleAlert className="size-6 text-amber-500" aria-hidden="true" />
                    )
                }
            />
            <CardBody className="space-y-6">
                <dl className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    {summary.map((item) => (
                        <div key={item.label} className="rounded-lg bg-slate-50 p-3">
                            <dt className="text-xs text-slate-500">{item.label}</dt>
                            <dd className={cn('mt-1 text-xl font-bold tabular-nums', item.value > 0 ? item.color : 'text-slate-400')}>
                                {formatNumber(item.value)}
                            </dd>
                        </div>
                    ))}
                </dl>

                {result.failed.length > 0 && (
                    <section>
                        <h3 className="flex items-center gap-2 text-sm font-semibold text-red-700">
                            <CircleAlert className="size-4" aria-hidden="true" />
                            Validation errors
                        </h3>
                        <ul className="mt-2 divide-y divide-slate-100 rounded-lg ring-1 ring-slate-200">
                            {result.failed.map((row) => (
                                <li key={row.row} className="px-4 py-3 text-sm">
                                    <p className="font-medium text-slate-900">
                                        Row {row.row}
                                        {(row.name || row.employee_id) && (
                                            <span className="font-normal text-slate-500">
                                                {' '}
                                                · {[row.employee_id, row.name].filter(Boolean).join(' – ')}
                                            </span>
                                        )}
                                    </p>
                                    <ul className="mt-1 list-disc space-y-0.5 pl-5 text-red-700">
                                        {row.errors.map((error) => (
                                            <li key={error}>{error}</li>
                                        ))}
                                    </ul>
                                </li>
                            ))}
                        </ul>
                    </section>
                )}

                {result.duplicates.length > 0 && (
                    <section>
                        <h3 className="flex items-center gap-2 text-sm font-semibold text-amber-700">
                            <Copy className="size-4" aria-hidden="true" />
                            Duplicates skipped
                        </h3>
                        <ul className="mt-2 divide-y divide-slate-100 rounded-lg ring-1 ring-slate-200">
                            {result.duplicates.map((row) => (
                                <li key={row.row} className="flex flex-wrap justify-between gap-x-4 gap-y-1 px-4 py-3 text-sm">
                                    <span className="font-medium text-slate-900">
                                        Row {row.row} · {row.employee_id} – {row.name}
                                    </span>
                                    <span className="text-slate-500">{row.reason}</span>
                                </li>
                            ))}
                        </ul>
                    </section>
                )}
            </CardBody>
        </Card>
    );
}
