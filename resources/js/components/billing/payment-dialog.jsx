import { useForm } from '@inertiajs/react';
import { Wallet } from 'lucide-react';
import Button from '@/components/ui/button';
import ChoiceChips from '@/components/ui/choice-chips';
import DateInput from '@/components/ui/date-input';
import FormField from '@/components/ui/form-field';
import Input from '@/components/ui/input';
import Modal from '@/components/ui/modal';
import MoneyInput from '@/components/ui/money-input';
import { useApp } from '@/hooks/use-app';

/**
 * Record money received against a bill. Defaults to the full amount due.
 */
export default function PaymentDialog({ open, onClose, bill, methods, today }) {
    const { money } = useApp();
    const { data, setData, post, processing, errors, reset } = useForm({
        payment_date: today,
        amount: bill.due_amount,
        method: 'cash',
        reference: '',
        note: '',
    });
    const formId = 'payment-form';

    function submit(event) {
        event.preventDefault();
        post(route('bills.payments.store', bill.id), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                onClose();
            },
        });
    }

    return (
        <Modal
            open={open}
            onClose={onClose}
            title="Record payment"
            description={`${bill.bill_number} · ${money(bill.due_amount)} due`}
            footer={
                <>
                    <Button variant="secondary" onClick={onClose} className="w-full sm:w-auto">
                        Cancel
                    </Button>
                    <Button type="submit" form={formId} icon={Wallet} loading={processing} className="w-full sm:w-auto">
                        Save payment
                    </Button>
                </>
            }
        >
            <form id={formId} onSubmit={submit} noValidate className="space-y-5">
                <div className="grid grid-cols-2 gap-3">
                    <FormField name="amount" label="Amount" error={errors.amount} required>
                        <MoneyInput value={data.amount} onChange={(event) => setData('amount', event.target.value)} />
                    </FormField>
                    <FormField name="payment_date" label="Date" error={errors.payment_date} required>
                        <DateInput value={data.payment_date} max={today} onChange={(event) => setData('payment_date', event.target.value)} />
                    </FormField>
                </div>
                <FormField name="method" label="Method" error={errors.method} required>
                    <ChoiceChips options={methods} value={data.method} onChange={(value) => setData('method', value)} />
                </FormField>
                <FormField
                    name="reference"
                    label="Reference"
                    hint={data.method === 'cash' ? 'Optional, e.g. receipt number.' : 'Transaction ID or cheque number.'}
                    error={errors.reference}
                >
                    <Input autoComplete="off" value={data.reference} onChange={(event) => setData('reference', event.target.value)} />
                </FormField>
                <FormField name="note" label="Note" error={errors.note}>
                    <Input value={data.note} onChange={(event) => setData('note', event.target.value)} />
                </FormField>
            </form>
        </Modal>
    );
}
