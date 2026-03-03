<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::factory()
            ->count(50)
            ->create()
            ->each(function ($order) {
                $items = OrderItem::factory()
                    ->count(fake()->numberBetween(1, 5))
                    ->create(['order_id' => $order->id]);

                $subtotal = $items->sum('subtotal');
                $tax = $subtotal * 0.1; // 10% tax
                $discount = fake()->randomFloat(2, 0, (float)$subtotal * 0.1);
                $total = $subtotal + $tax + $order->shipping_amount - $discount;

                $order->update([
                    'subtotal' => $subtotal,
                    'tax_amount' => $tax,
                    'discount_amount' => $discount,
                    'total_amount' => $total,
                ]);
            });
    }
}
