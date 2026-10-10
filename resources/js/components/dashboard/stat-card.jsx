import NavIcon from '@/components/layout/nav-icon';
import Card from '@/components/ui/card';
import { useApp } from '@/hooks/use-app';
import { formatNumber } from '@/lib/format';
import { cn } from '@/lib/utils';

export default function StatCard({ stat, className }) {
    const { money } = useApp();
    const value = stat.format === 'currency' ? money(stat.value) : formatNumber(stat.value);

    return (
        // Full-height flex column so values line up across a row even when a label wraps.
        <Card className={cn('flex h-full flex-col p-4 sm:p-5', className)}>
            <div className="flex items-start justify-between gap-3">
                <p className="text-sm font-medium text-slate-500">{stat.label}</p>
                <span className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600">
                    <NavIcon name={stat.icon} className="size-5" />
                </span>
            </div>
            <div className="mt-auto pt-2">
                <p className="text-2xl font-bold tracking-tight text-slate-900 tabular-nums sm:text-3xl">{value}</p>
                <p className="mt-1 text-xs text-slate-500">{stat.hint}</p>
            </div>
        </Card>
    );
}
