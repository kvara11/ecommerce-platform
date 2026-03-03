<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Inventory;
use App\Models\Product;

/**
 * @extends Factory<\App\Models\Inventory>
 */
class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(0, 200);
        $reserved = fake()->numberBetween(0, min(10, $quantity));

        return [
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'reserved_quantity' => $reserved,
            'low_stock_threshold' => 10,
            'last_restocked_at' => fake()->dateTimeThisYear(),
        ];
    }
}