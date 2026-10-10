import Badge from '@/components/ui/badge';

/**
 * Lunch / dinner badges for the meals an employee normally takes.
 */
export default function MealFlags({ employee }) {
    if (!employee.lunch_enabled && !employee.dinner_enabled) {
        return <span className="text-sm text-slate-400">No meals</span>;
    }

    return (
        <span className="inline-flex flex-wrap gap-1">
            {employee.lunch_enabled && <Badge tone="amber">Lunch</Badge>}
            {employee.dinner_enabled && <Badge tone="violet">Dinner</Badge>}
        </span>
    );
}
