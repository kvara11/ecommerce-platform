<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Inventory;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::factory()
            ->count(50)
            ->create()
            ->each(function ($product) {

                Inventory::factory()->create([
                    'product_id' => $product->id,
                ]);

                ProductImage::factory()->create([
                    'product_id' => $product->id,
                    'is_main' => true,
                    'sort_order' => 0,
                ]);

                ProductImage::factory()
                    ->count(fake()->numberBetween(2, 4))
                    ->create([
                        'product_id' => $product->id,
                        'is_main' => false,
                    ]);
            });
    }
}
