import Input from '@/components/ui/input';
import { useApp } from '@/hooks/use-app';
import { cn } from '@/lib/utils';

/**
 * Amount field with the currency symbol in front and a decimal keypad on
 * phones.
 */
export default function MoneyInput({ className, ...props }) {
    const { currency } = useApp();

    return (
        <div className="relative">
            <span
                className="pointer-events-none absolute inset-y-0 left-0 flex w-9 items-center justify-center text-slate-500"
                aria-hidden="true"
            >
                {currency.symbol}
            </span>
            <Input type="number" inputMode="decimal" step="0.01" min="0" className={cn('pl-9 tabular-nums', className)} {...props} />
        </div>
    );
}
