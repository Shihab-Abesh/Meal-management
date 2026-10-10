import { useForm } from '@inertiajs/react';
import { Bike } from 'lucide-react';
import Button from '@/components/ui/button';
import FormField from '@/components/ui/form-field';
import Input from '@/components/ui/input';
import Modal from '@/components/ui/modal';
import Select from '@/components/ui/select';

/**
 * Choose (or change) the rider for an order, with an optional delivery time.
 */
export default function AssignRiderDialog({ open, onClose, order, riders }) {
    const { data, setData, post, processing, errors } = useForm({
        rider_id: order?.rider ? String(order.rider.id) : '',
        delivery_time: order?.delivery_time ?? '',
        notes: order?.delivery?.notes ?? '',
    });
    const formId = 'assign-rider-form';

    function submit(event) {
        event.preventDefault();
        post(route('orders.assign', order.id), { preserveScroll: true, onSuccess: onClose });
    }

    const options = riders.map((rider) => ({
        value: rider.value,
        label: rider.open ? `${rider.label} (${rider.open} on the road)` : rider.label,
    }));

    return (
        <Modal
            open={open}
            onClose={onClose}
            title={order?.rider ? 'Change rider' : 'Assign rider'}
            description={order && `${order.client?.name} · ${order.meal_type_label} · ${order.quantity} meals`}
            size="sm"
            footer={
                <>
                    <Button variant="secondary" onClick={onClose} className="w-full sm:w-auto">
                        Cancel
                    </Button>
                    <Button type="submit" form={formId} icon={Bike} loading={processing} className="w-full sm:w-auto">
                        Assign
                    </Button>
                </>
            }
        >
            <form id={formId} onSubmit={submit} noValidate className="space-y-5">
                <FormField name="rider_id" label="Rider" error={errors.rider_id} required>
                    <Select
                        placeholder={riders.length ? 'Choose a rider' : 'No active riders'}
                        options={options}
                        value={data.rider_id}
                        onChange={(event) => setData('rider_id', event.target.value)}
                    />
                </FormField>
                <FormField name="delivery_time" label="Deliver by" hint="Optional, e.g. 12:30." error={errors.delivery_time}>
                    <Input type="time" value={data.delivery_time} onChange={(event) => setData('delivery_time', event.target.value)} />
                </FormField>
                <FormField name="assign_notes" label="Note for the rider" error={errors.notes}>
                    <Input value={data.notes} onChange={(event) => setData('notes', event.target.value)} placeholder="e.g. Use the back gate" />
                </FormField>
            </form>
        </Modal>
    );
}
