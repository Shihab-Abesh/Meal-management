import { Field, Label, Switch, Description } from '@headlessui/react';
import { cn } from '@/lib/utils';

/**
 * On/off switch with a label, sized for touch (whole row is clickable).
 */
export default function Toggle({ checked, onChange, label, description, disabled = false, className }) {
    return (
        <Field disabled={disabled} className={cn('flex min-h-11 items-center justify-between gap-4', className)}>
            <span className="min-w-0">
                <Label className="block text-sm font-medium text-slate-700 data-disabled:opacity-60">{label}</Label>
                {description && <Description className="text-sm text-slate-500">{description}</Description>}
            </span>
            <Switch
                checked={checked}
                onChange={onChange}
                className="group relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full bg-slate-200 transition-colors duration-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 data-checked:bg-primary-600 data-disabled:cursor-not-allowed data-disabled:opacity-60"
            >
                <span
                    aria-hidden="true"
                    className="pointer-events-none mt-0.5 ml-0.5 inline-block size-6 rounded-full bg-white shadow ring-0 transition duration-200 group-data-checked:translate-x-5"
                />
            </Switch>
        </Field>
    );
}
