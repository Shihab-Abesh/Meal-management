import { useApp } from '@/hooks/use-app';
import { formatNumber } from '@/lib/format';

const labels = { lunch: 'Lunch', dinner: 'Dinner' };

/**
 * Meal × price lines of a bill (or bill preview) with the total row.
 * lines: [{ meal_type, meal_type_label?, unit_price, quantity, amount }]
 */
export default function BillLinesTable({ lines, totalMeals, totalAmount }) {
    const { money } = useApp();

    return (
        <table className="min-w-full text-sm">
            <thead>
                <tr className="text-left text-xs font-semibold tracking-wide text-slate-500 uppercase">
                    <th scope="col" className="py-3 pr-2 pl-4 sm:pl-6">Meal</th>
                    <th scope="col" className="px-2 py-3 text-right sm:px-4">Qty</th>
                    <th scope="col" className="px-2 py-3 text-right sm:px-4">Price</th>
                    <th scope="col" className="py-3 pr-4 pl-2 text-right sm:pr-6">Amount</th>
                </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
                {lines.map((line) => (
                    <tr key={`${line.meal_type}-${line.unit_price}`}>
                        <td className="py-3 pr-2 pl-4 sm:pl-6">{line.meal_type_label ?? labels[line.meal_type] ?? line.meal_type}</td>
                        <td className="px-2 py-3 text-right tabular-nums sm:px-4">{formatNumber(line.quantity)}</td>
                        <td className="px-2 py-3 text-right tabular-nums sm:px-4">{money(line.unit_price)}</td>
                        <td className="py-3 pr-4 pl-2 text-right font-medium tabular-nums sm:pr-6">{money(line.amount)}</td>
                    </tr>
                ))}
            </tbody>
            <tfoot>
                <tr className="border-t border-slate-200 font-semibold text-slate-900">
                    <td className="py-3 pr-2 pl-4 sm:pl-6">Total</td>
                    <td className="px-2 py-3 text-right tabular-nums sm:px-4">{formatNumber(totalMeals)}</td>
                    <td />
                    <td className="py-3 pr-4 pl-2 text-right text-base tabular-nums sm:pr-6">{money(totalAmount)}</td>
                </tr>
            </tfoot>
        </table>
    );
}
