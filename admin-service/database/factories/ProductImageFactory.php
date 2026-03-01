<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProductImage;
use App\Models\Product;

class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'url' => fake()->imageUrl(640, 480, 'products'),
            'is_main' => false,
            'sort_order' => fake()->numberBetween(0, 5),
        ];
    }
}