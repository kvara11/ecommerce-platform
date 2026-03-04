<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customerIds = User::where('role_id', 2)->pluck('id')->toArray();

        Order::factory()
            ->count(50)
            ->create()
            ->each(function ($order) use ($customerIds) {

                $order->update(['user_id' => fake()->randomElement($customerIds)]);

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
