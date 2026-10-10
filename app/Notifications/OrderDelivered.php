<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDelivered extends Notification
{
    public function __construct(private readonly Order $order) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $meal = mb_strtolower($this->order->meal_type->label());

        return (new MailMessage)
            ->subject("Your {$meal} has been delivered")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->order->quantity} {$meal} meals for ".$this->order->order_date->format('d M Y').' have been delivered.')
            ->line("Order {$this->order->order_number}");
    }
}
