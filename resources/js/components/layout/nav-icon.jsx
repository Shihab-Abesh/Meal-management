import {
    Activity,
    Banknote,
    BarChart3,
    BookOpen,
    Bike,
    Building2,
    CalendarDays,
    ChefHat,
    Circle,
    CircleCheck,
    FileText,
    ClipboardCheck,
    ClipboardList,
    Clock,
    History,
    LayoutDashboard,
    ReceiptText,
    Settings,
    ShieldCheck,
    Store,
    Tag,
    Truck,
    UserCog,
    Users,
    Utensils,
    UtensilsCrossed,
    Wallet,
} from 'lucide-react';

/**
 * Icon names used by server-built navigation and dashboard cards.
 * Register an icon here before using its name in PHP.
 */
const icons = {
    activity: Activity,
    banknote: Banknote,
    bike: Bike,
    building: Building2,
    calendar: CalendarDays,
    'book-open': BookOpen,
    chart: BarChart3,
    'chef-hat': ChefHat,
    'circle-check': CircleCheck,
    'file-text': FileText,
    clipboard: ClipboardList,
    'clipboard-check': ClipboardCheck,
    clock: Clock,
    history: History,
    'layout-dashboard': LayoutDashboard,
    receipt: ReceiptText,
    settings: Settings,
    shield: ShieldCheck,
    store: Store,
    tag: Tag,
    truck: Truck,
    'user-cog': UserCog,
    users: Users,
    utensils: Utensils,
    'utensils-crossed': UtensilsCrossed,
    wallet: Wallet,
};

export default function NavIcon({ name, className }) {
    const Icon = icons[name] ?? Circle;

    return <Icon className={className} aria-hidden="true" />;
}
