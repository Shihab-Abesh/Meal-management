<?php

namespace App\Notifications;

use App\Models\Bill;
use App\Services\BillingService;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BillIssued extends Notification
{
    public function __construct(private readonly Bill $bill) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $period = app(BillingService::class)->periodLabel($this->bill->period_start->toDateString(), $this->bill->period_end->toDateString());
        $symbol = config('catering.currency.symbol');

        return (new MailMessage)
            ->subject("New bill {$this->bill->bill_number} for {$period}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your meal bill for {$period} is ready.")
            ->line("{$this->bill->total_meals} meals · Total {$symbol}".number_format((float) $this->bill->total_amount, 2))
            ->line('Please pay by '.$this->bill->due_date->format('d M Y').'.')
            ->action('View bill', route('billing.statement', ['month' => $this->bill->period_start->format('Y-m')]));
    }
}
