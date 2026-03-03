<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethods;
use App\Models\PaymentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number' => 'ORD-' . strtoupper(fake()->unique()->bothify('??####')),
            'user_id' => User::query()->inRandomOrder()->value('id') ?? User::factory(),
            'status_id' => OrderStatus::query()->inRandomOrder()->value('id') ?? 1,
            'payment_status_id' => PaymentStatus::query()->inRandomOrder()->value('id') ?? 1,
            'payment_method_id' => PaymentMethods::query()->inRandomOrder()->value('id') ?? 1,
            'shipping_address' => fake()->address(),
            'billing_address' => fake()->address(),
            'subtotal' => 0,
            'tax_amount' => 0,
            'shipping_amount' => fake()->randomFloat(2, 5, 20),
            'discount_amount' => 0,
            'total_amount' => 0,
            'currency' => 'USD',
            'notes' => fake()->optional()->sentence(),
            'customer_notes' => fake()->optional()->sentence(),
            'shipped_at' => null,
            'delivered_at' => null,
            'cancelled_at' => null,
            'created_at' => $createdAt = fake()->dateTimeBetween('-2 years', 'now'),
            'updated_at' => fake()->dateTimeBetween($createdAt, 'now'),
        ];
    }
}
