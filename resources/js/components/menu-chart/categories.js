/**
 * Menu item categories in the order a plate is read (rice first, then
 * curries, sides, drinks, dessert), each with a dot colour. The item name is
 * always written next to the dot, so colour only helps scanning.
 */
export const CATEGORIES = [
    { value: 'main_course', label: 'Main course', dot: 'bg-amber-500' },
    { value: 'protein', label: 'Protein', dot: 'bg-rose-500' },
    { value: 'side_dish', label: 'Side dish', dot: 'bg-emerald-500' },
    { value: 'beverage', label: 'Beverage', dot: 'bg-sky-500' },
    { value: 'dessert', label: 'Dessert', dot: 'bg-fuchsia-400' },
    { value: 'other', label: 'Other', dot: 'bg-slate-400' },
];

const byValue = Object.fromEntries(CATEGORIES.map((category, index) => [category.value, { ...category, index }]));

export function categoryDot(value) {
    return byValue[value]?.dot ?? 'bg-slate-400';
}

export function categoryIndex(value) {
    return byValue[value]?.index ?? CATEGORIES.length;
}
