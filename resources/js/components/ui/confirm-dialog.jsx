import Button from '@/components/ui/button';
import Modal from '@/components/ui/modal';

/**
 * Asks before a destructive or important action. The confirm button shows a
 * spinner and is disabled while `processing`, so it cannot be sent twice.
 */
export default function ConfirmDialog({
    open,
    onClose,
    onConfirm,
    title,
    description,
    confirmLabel = 'Confirm',
    cancelLabel = 'Cancel',
    tone = 'danger',
    processing = false,
    children,
}) {
    return (
        <Modal
            open={open}
            onClose={processing ? () => {} : onClose}
            title={title}
            size="sm"
            footer={
                <>
                    <Button variant="secondary" onClick={onClose} disabled={processing} className="w-full sm:w-auto">
                        {cancelLabel}
                    </Button>
                    <Button
                        variant={tone === 'danger' ? 'danger' : 'primary'}
                        onClick={onConfirm}
                        loading={processing}
                        className="w-full sm:w-auto"
                    >
                        {confirmLabel}
                    </Button>
                </>
            }
        >
            {description && <p className="text-sm text-slate-600">{description}</p>}
            {children}
        </Modal>
    );
}
