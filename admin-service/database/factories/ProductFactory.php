<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Category;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::query()->inRandomOrder()->value('id')
                ?? Category::factory(),

            'sku' => strtoupper(fake()->bothify('SKU-#####')),
            'name' => ucfirst($name),
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(10, 2000),

            'description' => fake()->paragraphs(3, true),
            'short_description' => fake()->sentence(),

            'price' => fake()->randomFloat(2, 10, 500),
            'cost_price' => fake()->optional()->randomFloat(2, 5, 300),

            'is_active' => fake()->boolean(90),

            'meta_title' => fake()->optional()->sentence(6),
            'meta_description' => fake()->optional()->sentence(12),
            'meta_keywords' => fake()->optional()->words(6, true),
        ];
    }
}