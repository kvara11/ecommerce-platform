<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;

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
                ProductImage::factory()->create([
                    'product_id' => $product->id,
                    'url' => fake()->imageUrl(640, 480, 'products'),
                    'is_main' => true,
                    'sort_order' => 0,
                ]);

                ProductImage::factory()
                    ->count(fake()->numberBetween(2, 4))
                    ->create();
            });
    }
}
