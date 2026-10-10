<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification
{
    public function __construct(private readonly Payment $payment) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $symbol = config('catering.currency.symbol');
        $bill = $this->payment->bill;

        return (new MailMessage)
            ->subject("Payment received for bill {$bill->bill_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("We received {$symbol}".number_format((float) $this->payment->amount, 2)." by {$this->payment->method->label()} on ".$this->payment->payment_date->format('d M Y').'.')
            ->line("Still due on this bill: {$symbol}".number_format((float) $bill->due_amount, 2))
            ->action('View billing', route('billing.index'))
            ->line('Thank you!');
    }
}
