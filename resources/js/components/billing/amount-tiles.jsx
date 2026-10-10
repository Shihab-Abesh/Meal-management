import Card from '@/components/ui/card';
import { useApp } from '@/hooks/use-app';
import { cn } from '@/lib/utils';

const tones = {
    default: 'text-slate-900',
    good: 'text-emerald-700',
    warn: 'text-amber-700',
    bad: 'text-red-700',
};

/**
 * A row of money figures (billed / paid / due …).
 * tiles: [{ key, label, value, hint?, tone?, format? ('currency' | 'number') }]
 */
export default function AmountTiles({ tiles, className }) {
    const { money } = useApp();

    return (
        <section className={cn('grid grid-cols-2 gap-3 sm:gap-4', tiles.length >= 4 ? 'lg:grid-cols-4' : 'sm:grid-cols-3', className)}>
            {tiles.map((tile, index) => (
                <Card
                    key={tile.key}
                    className={cn('p-4 sm:p-5', index === tiles.length - 1 && tiles.length % 2 === 1 && 'col-span-2 sm:col-span-1')}
                >
                    <p className="text-sm font-medium text-slate-500">{tile.label}</p>
                    <p className={cn('mt-1 text-xl font-bold tracking-tight tabular-nums sm:text-2xl', tones[tile.tone ?? 'default'])}>
                        {tile.format === 'number' ? tile.value : money(tile.value)}
                    </p>
                    {tile.hint && <p className="mt-1 text-xs text-slate-500">{tile.hint}</p>}
                </Card>
            ))}
        </section>
    );
}
