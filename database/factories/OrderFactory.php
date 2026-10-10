<?php

namespace Database\Factories;

use App\Enums\MealType;
use App\Enums\OrderStatus;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'order_date' => now()->toDateString(),
            'meal_type' => MealType::Lunch,
            'quantity' => 10,
            'amount' => '1200.00',
            'status' => OrderStatus::Confirmed,
            'delivery_address' => fake()->streetAddress(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Order $order) {
            $order->update(['order_number' => Order::numberFor($order->id, $order->order_date->toDateString())]);
        });
    }

    public function status(OrderStatus $status): static
    {
        return $this->state(['status' => $status]);
    }
}
