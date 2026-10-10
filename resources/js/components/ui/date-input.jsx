import Input from '@/components/ui/input';
import { cn } from '@/lib/utils';

/**
 * Date picker. Uses the native control, which gives the best picker on
 * phones (wheel / calendar sheet). Value is "YYYY-MM-DD".
 */
export default function DateInput({ type = 'date', className, ...props }) {
    return <Input type={type} className={cn('min-w-0 appearance-none', className)} {...props} />;
}
