<?php

namespace App\Notifications\Vendor;

use Illuminate\Notifications\Notification;

/**
 * An in-app alert for the vendor team, shown under the bell.
 *
 * The text is written when the event happens, like a message, so it still
 * reads correctly after the order or bill changes again. Each subclass is
 * one kind of alert; its category decides who may switch it off.
 */
abstract class VendorAlert extends Notification
{
    /**
     * @param  array<string, mixed>  $meta  ids and values the alert is about
     */
    public function __construct(
        protected readonly string $title,
        protected readonly string $body,
        protected readonly string $url,
        protected readonly array $meta = [],
    ) {}

    /**
     * "orders", "deliveries" or "billing": the preference that controls it.
     */
    abstract public static function category(): string;

    /**
     * Short machine name sent to the frontend (icon and colour).
     */
    abstract public static function kind(): string;

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => static::kind(),
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            ...$this->meta,
        ];
    }
}
