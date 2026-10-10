import { CalendarCheck, ChevronLeft, ChevronRight } from 'lucide-react';
import Button from '@/components/ui/button';
import DateInput from '@/components/ui/date-input';
import { parseDate } from '@/lib/format';
import { cn } from '@/lib/utils';

function pad(number) {
    return String(number).padStart(2, '0');
}

export function toDateValue(date) {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

export function shiftDate(value, days) {
    const date = parseDate(value);
    date.setDate(date.getDate() + days);

    return toDateValue(date);
}

export function shiftMonth(value, months) {
    const [year, month] = value.split('-').map(Number);
    const date = new Date(year, month - 1 + months, 1);

    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}`;
}

/**
 * Previous / picker / next control for a day ("YYYY-MM-DD") or, with
 * mode="month", a month ("YYYY-MM"). Large buttons for thumbs.
 */
export default function DateNavigator({ value, onChange, mode = 'day', today, label = 'Date', min, max, className }) {
    const isMonth = mode === 'month';
    const step = (direction) => (isMonth ? shiftMonth(value, direction) : shiftDate(value, direction));
    const todayValue = today ? (isMonth ? today.slice(0, 7) : today) : null;

    return (
        <div className={cn('flex items-center gap-2', className)}>
            <Button
                variant="secondary"
                size="icon"
                aria-label={isMonth ? 'Previous month' : 'Previous day'}
                disabled={min && step(-1) < min}
                onClick={() => onChange(step(-1))}
            >
                <ChevronLeft className="size-5" aria-hidden="true" />
            </Button>
            <DateInput
                type={isMonth ? 'month' : 'date'}
                aria-label={label}
                value={value}
                min={min}
                max={max}
                onChange={(event) => event.target.value && onChange(event.target.value)}
                className="flex-1 text-center sm:w-44 sm:flex-none"
            />
            <Button
                variant="secondary"
                size="icon"
                aria-label={isMonth ? 'Next month' : 'Next day'}
                disabled={max && step(1) > max}
                onClick={() => onChange(step(1))}
            >
                <ChevronRight className="size-5" aria-hidden="true" />
            </Button>
            {todayValue && value !== todayValue && (
                // Icon only on phones so the date field keeps its room.
                <Button
                    variant="ghost"
                    onClick={() => onChange(todayValue)}
                    aria-label={isMonth ? 'This month' : 'Today'}
                    className="px-2.5 sm:px-3"
                >
                    <CalendarCheck className="size-5 sm:hidden" aria-hidden="true" />
                    <span className="hidden sm:inline">{isMonth ? 'This month' : 'Today'}</span>
                </Button>
            )}
        </div>
    );
}
