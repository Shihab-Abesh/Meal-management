import { Children, cloneElement, isValidElement } from 'react';
import { cn } from '@/lib/utils';

export function Label({ htmlFor, required = false, className, children }) {
    return (
        <label htmlFor={htmlFor} className={cn('block text-sm font-medium text-slate-700', className)}>
            {children}
            {required && (
                <span className="ml-0.5 text-red-500" aria-hidden="true">
                    *
                </span>
            )}
        </label>
    );
}

export function FieldError({ id, message }) {
    if (!message) {
        return null;
    }

    return (
        <p id={id} role="alert" className="text-sm text-red-600">
            {message}
        </p>
    );
}

/**
 * Label + control + hint + error, wired together for screen readers.
 * The single child control receives id, invalid, required and
 * aria-describedby automatically.
 */
export default function FormField({ name, label, error, hint, required = false, action, className, children }) {
    const hintId = hint ? `${name}-hint` : null;
    const errorId = error ? `${name}-error` : null;
    const describedBy = [errorId, hintId].filter(Boolean).join(' ') || undefined;

    const control = Children.only(children);

    return (
        <div className={cn('space-y-1.5', className)}>
            {(label || action) && (
                <div className="flex items-center justify-between gap-3">
                    {label && (
                        <Label htmlFor={name} required={required}>
                            {label}
                        </Label>
                    )}
                    {action}
                </div>
            )}
            {isValidElement(control)
                ? cloneElement(control, {
                      id: control.props.id ?? name,
                      name: control.props.name ?? name,
                      invalid: Boolean(error),
                      required: control.props.required ?? required,
                      'aria-describedby': describedBy,
                  })
                : control}
            {hint && (
                <p id={hintId} className="text-xs text-slate-500">
                    {hint}
                </p>
            )}
            <FieldError id={errorId} message={error} />
        </div>
    );
}
