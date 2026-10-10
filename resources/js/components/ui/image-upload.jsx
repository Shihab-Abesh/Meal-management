import { ImageUp, Trash2 } from 'lucide-react';
import { useEffect, useId, useState } from 'react';
import Avatar from '@/components/ui/avatar';
import Button from '@/components/ui/button';
import { FieldError } from '@/components/ui/form-field';

/**
 * Image picker with live preview. The parent owns the selected File and
 * whether the existing image should be removed.
 */
export default function ImageUpload({
    label,
    name,
    file,
    currentUrl,
    removed = false,
    onSelect,
    onRemove,
    accept,
    hint,
    error,
}) {
    const inputId = useId();
    const [previewUrl, setPreviewUrl] = useState(null);

    useEffect(() => {
        if (!file) {
            setPreviewUrl(null);

            return;
        }

        const url = URL.createObjectURL(file);
        setPreviewUrl(url);

        return () => URL.revokeObjectURL(url);
    }, [file]);

    const shownUrl = previewUrl ?? (removed ? null : currentUrl);

    return (
        <div>
            <span className="block text-sm font-medium text-slate-700">{label}</span>
            <div className="mt-2 flex flex-col gap-4 sm:flex-row sm:items-center">
                <Avatar name={name} src={shownUrl} size="xl" rounded="xl" className="ring-1 ring-slate-200" />
                <div className="flex flex-wrap gap-2">
                    <input
                        id={inputId}
                        type="file"
                        accept={accept}
                        className="peer sr-only"
                        aria-invalid={Boolean(error) || undefined}
                        onChange={(event) => {
                            onSelect(event.target.files?.[0] ?? null);
                            event.target.value = '';
                        }}
                    />
                    <Button
                        as="label"
                        htmlFor={inputId}
                        variant="secondary"
                        icon={ImageUp}
                        className="cursor-pointer peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-primary-600"
                    >
                        {shownUrl ? 'Change image' : 'Upload image'}
                    </Button>
                    {shownUrl && (
                        <Button variant="ghost" icon={Trash2} onClick={onRemove} className="text-red-600 hover:text-red-700">
                            Remove
                        </Button>
                    )}
                </div>
            </div>
            {hint && <p className="mt-2 text-xs text-slate-500">{hint}</p>}
            <FieldError message={error} />
        </div>
    );
}
