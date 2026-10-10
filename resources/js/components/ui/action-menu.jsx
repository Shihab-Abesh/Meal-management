import { EllipsisVertical } from 'lucide-react';
import Dropdown from '@/components/ui/dropdown';

/**
 * "More actions" menu for a row or page. Children are DropdownItem entries.
 */
export default function ActionMenu({ label = 'More actions', children }) {
    return (
        <Dropdown
            label={label}
            buttonClassName="size-11 justify-center text-slate-500 hover:bg-slate-100 hover:text-slate-700"
            trigger={<EllipsisVertical className="size-5" aria-hidden="true" />}
        >
            {children}
        </Dropdown>
    );
}
