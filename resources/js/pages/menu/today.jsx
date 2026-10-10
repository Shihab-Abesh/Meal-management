import { Head, router } from '@inertiajs/react';
import { UtensilsCrossed } from 'lucide-react';
import { useState } from 'react';
import Avatar from '@/components/ui/avatar';
import Badge from '@/components/ui/badge';
import Card, { CardBody } from '@/components/ui/card';
import DateNavigator from '@/components/ui/date-navigator';
import EmptyState from '@/components/ui/empty-state';
import PageHeader from '@/components/ui/page-header';
import { useApp } from '@/hooks/use-app';
import { formatLongDate } from '@/lib/format';
import { cn } from '@/lib/utils';

const accents = { lunch: 'bg-amber-500', dinner: 'bg-violet-500' };

export default function TodayMenu({ date, today, meals, priceLabel }) {
    const { money } = useApp();
    const [loading, setLoading] = useState(false);

    function go(value) {
        router.get(
            route('menu.today'),
            value === today ? {} : { date: value },
            { preserveScroll: true, onStart: () => setLoading(true), onFinish: () => setLoading(false) },
        );
    }

    const title = date === today ? "Today's menu" : 'Menu';

    return (
        <>
            <Head title={title} />

            <PageHeader title={title} description={formatLongDate(date)} />

            <DateNavigator value={date} today={today} onChange={go} className="mb-6" />

            <div className={cn('grid gap-6 transition-opacity lg:grid-cols-2', loading && 'opacity-60')} aria-busy={loading || undefined}>
                {meals.map((meal) => (
                    <Card key={meal.meal_type} as="section" aria-labelledby={`meal-${meal.meal_type}`} className="overflow-hidden">
                        <div className={cn('h-1.5', accents[meal.meal_type] ?? 'bg-slate-300')} aria-hidden="true" />
                        <div className="flex items-start justify-between gap-3 px-4 pt-4 sm:px-6">
                            <div>
                                <h2 id={`meal-${meal.meal_type}`} className="text-lg font-semibold text-slate-900">
                                    {meal.meal_type_label}
                                </h2>
                                {meal.menu?.notes && <p className="mt-0.5 text-sm text-slate-500">{meal.menu.notes}</p>}
                            </div>
                            {meal.price && meal.menu && (
                                <div className="text-right">
                                    <p className="text-xs text-slate-500">{priceLabel}</p>
                                    <p className="text-lg font-bold text-slate-900 tabular-nums">{money(meal.price)}</p>
                                </div>
                            )}
                        </div>

                        {meal.menu ? (
                            <CardBody>
                                <ul className="divide-y divide-slate-100">
                                    {meal.menu.items.map((item) => (
                                        <li key={item.id} className="flex items-center gap-3 py-3">
                                            <Avatar name={item.name} src={item.image_url} size="md" rounded="xl" className="ring-1 ring-slate-200" />
                                            <div className="min-w-0 flex-1">
                                                <p className="font-medium text-slate-900">{item.name}</p>
                                                {item.description && <p className="text-sm text-slate-500">{item.description}</p>}
                                            </div>
                                            <Badge className="max-sm:hidden">{item.category_label}</Badge>
                                        </li>
                                    ))}
                                </ul>
                            </CardBody>
                        ) : (
                            <EmptyState
                                icon={UtensilsCrossed}
                                title="Menu not published yet"
                                description={`The ${meal.meal_type_label.toLowerCase()} menu for this day has not been added.`}
                            />
                        )}
                    </Card>
                ))}
            </div>
        </>
    );
}
