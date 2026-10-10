import Badge from '@/components/ui/badge';

const tones = { lunch: 'amber', dinner: 'violet' };
const labels = { lunch: 'Lunch', dinner: 'Dinner' };

export default function MealTypeBadges({ types = [] }) {
    return (
        <span className="inline-flex flex-wrap gap-1">
            {types.map((type) => (
                <Badge key={type} tone={tones[type] ?? 'gray'}>
                    {labels[type] ?? type}
                </Badge>
            ))}
        </span>
    );
}
