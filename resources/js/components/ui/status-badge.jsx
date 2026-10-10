import Badge from '@/components/ui/badge';

/**
 * Colour for each status value used across the app. Add new statuses here
 * (e.g. order and payment statuses) so they look the same everywhere.
 */
const statusTones = {
    active: 'green',
    inactive: 'gray',
    archived: 'amber',
    confirmed: 'green',
    cancelled: 'red',
    // Orders
    pending: 'amber',
    preparing: 'amber',
    ready: 'violet',
    assigned: 'blue',
    picked_up: 'violet',
    on_the_way: 'blue',
    delivered: 'green',
    // Bills
    unpaid: 'amber',
    partially_paid: 'blue',
    paid: 'green',
    overdue: 'red',
};

function humanize(value) {
    return value.replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());
}

export default function StatusBadge({ status, label, className }) {
    if (!status) {
        return null;
    }

    return (
        <Badge tone={statusTones[status] ?? 'gray'} dot className={className}>
            {label ?? humanize(status)}
        </Badge>
    );
}
