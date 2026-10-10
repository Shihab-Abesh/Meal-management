<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\Bill;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\BillIssued;
use App\Notifications\OrderDelivered;
use App\Notifications\PaymentReceived;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as Notifications;
use Throwable;

/**
 * Emails to a client's own accounts, each switched on or off in the
 * notification settings. Called after the business action has been saved;
 * a mail failure is reported but never undoes or blocks that action.
 */
class Notifier
{
    public function __construct(private readonly Settings $settings) {}

    public function billIssued(Bill $bill): void
    {
        $this->sendToClient('bill_issued', $bill->client_id, fn () => new BillIssued($bill));
    }

    public function paymentReceived(Payment $payment): void
    {
        $this->sendToClient('payment_received', $payment->client_id, fn () => new PaymentReceived($payment));
    }

    public function orderDelivered(Order $order): void
    {
        $this->sendToClient('order_delivered', $order->client_id, fn () => new OrderDelivered($order));
    }

    /**
     * @param  callable(): Notification  $notification
     */
    private function sendToClient(string $event, int $clientId, callable $notification): void
    {
        if (! $this->settings->notifies($event)) {
            return;
        }

        $recipients = $this->clientAccounts($clientId);

        if ($recipients->isEmpty()) {
            return;
        }

        try {
            Notifications::send($recipients, $notification());
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * Active client accounts of the organization that have an email address.
     *
     * @return Collection<int, User>
     */
    private function clientAccounts(int $clientId): Collection
    {
        return User::query()
            ->where('role', UserRole::ClientAdmin)
            ->where('client_id', $clientId)
            ->where('status', RecordStatus::Active)
            ->whereNotNull('email')
            ->get();
    }
}
