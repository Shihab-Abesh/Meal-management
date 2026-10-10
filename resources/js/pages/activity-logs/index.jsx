import { Head } from '@inertiajs/react';
import { Activity, ChevronDown, SearchX } from 'lucide-react';
import { useState } from 'react';
import Badge from '@/components/ui/badge';
import Card from '@/components/ui/card';
import DateInput from '@/components/ui/date-input';
import EmptyState from '@/components/ui/empty-state';
import FilterPanel, { FilterField } from '@/components/ui/filter-panel';
import PageHeader from '@/components/ui/page-header';
import Pagination from '@/components/ui/pagination';
import SearchInput from '@/components/ui/search-input';
import Select from '@/components/ui/select';
import { useFilters } from '@/hooks/use-filters';
import { formatDateTime } from '@/lib/format';
import { cn } from '@/lib/utils';

const FILTER_KEYS = ['module', 'user', 'client'];

/**
 * The audit trail. Each entry shows who, what, which record and when; the
 * stored details (old and new values, IP address) open on demand.
 */
export default function ActivityLogs({ logs, filters, modules, users, clients }) {
    const { values, set, setMany, reset, loading } = useFilters(route('activity-logs.index'), filters);
    const activeFilters = FILTER_KEYS.filter((key) => values[key]).length;

    return (
        <>
            <Head title="Activity log" />

            <PageHeader title="Activity log" description="Who did what and when. Entries cannot be edited or deleted." />

            <Card>
                <div className="flex flex-col gap-3 border-b border-slate-100 p-4 sm:px-6 md:flex-row md:flex-wrap md:items-end">
                    <div className="grid grid-cols-2 gap-3 md:flex">
                        <FilterField label="From" htmlFor="filter-from">
                            <DateInput id="filter-from" value={values.from} max={values.to} onChange={(event) => event.target.value && setMany({ from: event.target.value })} />
                        </FilterField>
                        <FilterField label="To" htmlFor="filter-to">
                            <DateInput id="filter-to" value={values.to} min={values.from} onChange={(event) => event.target.value && setMany({ to: event.target.value })} />
                        </FilterField>
                    </div>
                    <div className="flex gap-2 md:contents">
                        <SearchInput value={values.search} onChange={(value) => set('search', value, { debounce: true })} placeholder="Search descriptions…" className="min-w-0 flex-1 md:max-w-xs" />
                        <FilterPanel activeCount={activeFilters} onReset={() => reset(FILTER_KEYS)}>
                            <FilterField label="Module" htmlFor="filter-module">
                                <Select id="filter-module" placeholder="All modules" options={modules} value={values.module} onChange={(event) => set('module', event.target.value)} />
                            </FilterField>
                            <FilterField label="User" htmlFor="filter-user">
                                <Select id="filter-user" placeholder="Everyone" options={users} value={values.user} onChange={(event) => set('user', event.target.value)} />
                            </FilterField>
                            <FilterField label="Client" htmlFor="filter-client">
                                <Select id="filter-client" placeholder="All clients" options={clients} value={values.client} onChange={(event) => set('client', event.target.value)} />
                            </FilterField>
                        </FilterPanel>
                    </div>
                </div>

                <div className={cn('transition-opacity', loading && 'opacity-60')} aria-busy={loading || undefined}>
                    {logs.data.length === 0 ? (
                        activeFilters > 0 || values.search ? (
                            <EmptyState icon={SearchX} title="No matching activity" description="Try another search, filter or date range." />
                        ) : (
                            <EmptyState icon={Activity} title="No activity in this period" />
                        )
                    ) : (
                        <ol className="divide-y divide-slate-100">
                            {logs.data.map((log) => (
                                <LogEntry key={log.id} log={log} />
                            ))}
                        </ol>
                    )}
                </div>
                <Pagination meta={logs.meta} links={logs.links} />
            </Card>
        </>
    );
}

function LogEntry({ log }) {
    const [open, setOpen] = useState(false);
    const hasDetails = Boolean(log.properties || log.ip_address || log.record);

    return (
        <li className="px-4 py-3 sm:px-6">
            <div className="flex items-start gap-3">
                <div className="min-w-0 flex-1">
                    <p className="text-sm text-slate-900">{log.description}</p>
                    <p className="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                        <Badge tone="gray">{log.module_label}</Badge>
                        <span>{log.user ? `${log.user.name} (${log.user.role})` : 'System'}</span>
                        <span aria-hidden="true">·</span>
                        <time dateTime={log.created_at}>{formatDateTime(log.created_at)}</time>
                        {log.client && (
                            <>
                                <span aria-hidden="true">·</span>
                                <span>{log.client}</span>
                            </>
                        )}
                    </p>
                </div>
                {hasDetails && (
                    <button
                        type="button"
                        aria-expanded={open}
                        onClick={() => setOpen((value) => !value)}
                        className="-mr-2 flex size-11 shrink-0 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus-visible:outline-2 focus-visible:outline-primary-600"
                    >
                        <span className="sr-only">{open ? 'Hide details' : 'Show details'}</span>
                        <ChevronDown className={cn('size-5 transition-transform', open && 'rotate-180')} aria-hidden="true" />
                    </button>
                )}
            </div>
            {open && (
                <dl className="mt-3 grid gap-x-6 gap-y-2 rounded-lg bg-slate-50 p-3 text-xs sm:grid-cols-[auto_1fr]">
                    {log.record && (
                        <>
                            <dt className="font-medium text-slate-500">Record</dt>
                            <dd className="text-slate-800">{log.record}</dd>
                        </>
                    )}
                    {log.ip_address && (
                        <>
                            <dt className="font-medium text-slate-500">IP address</dt>
                            <dd className="text-slate-800">{log.ip_address}</dd>
                        </>
                    )}
                    {log.properties && (
                        <>
                            <dt className="font-medium text-slate-500">Details</dt>
                            <dd className="min-w-0">
                                <pre className="overflow-x-auto font-mono text-[11px] break-words whitespace-pre-wrap text-slate-800">
                                    {JSON.stringify(log.properties, null, 2)}
                                </pre>
                            </dd>
                        </>
                    )}
                </dl>
            )}
        </li>
    );
}
