import { Eye, EyeOff } from 'lucide-react';
import { useState } from 'react';
import Input from '@/components/ui/input';
import { cn } from '@/lib/utils';

export default function PasswordInput({ className, ...props }) {
    const [visible, setVisible] = useState(false);
    const Icon = visible ? EyeOff : Eye;

    return (
        <div className="relative">
            <Input type={visible ? 'text' : 'password'} className={cn('pr-12', className)} {...props} />
            <button
                type="button"
                onClick={() => setVisible((value) => !value)}
                className="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-lg text-slate-400 hover:text-slate-600 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-primary-600"
                aria-label={visible ? 'Hide password' : 'Show password'}
                aria-pressed={visible}
            >
                <Icon className="size-5" aria-hidden="true" />
            </button>
        </div>
    );
}
