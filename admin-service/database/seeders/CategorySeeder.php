<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $electronics = Category::updateOrCreate(
            ['slug' => 'electronics'],
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'sort_order' => 1,
                'is_active' => true,
                'parent_id' => null,
            ]
        );

        $clothing = Category::updateOrCreate(
            ['slug' => 'clothing'],
            [
                'name' => 'Clothing',
                'slug' => 'clothing',
                'sort_order' => 2,
                'is_active' => true,
                'parent_id' => null,
            ]
        );

        $books = Category::updateOrCreate(
            ['slug' => 'books'],
            [
                'name' => 'Books',
                'slug' => 'books',
                'sort_order' => 3,
                'is_active' => true,
                'parent_id' => null,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'smartphones'],
            [
                'name' => 'Smartphones',
                'slug' => 'smartphones',
                'parent_id' => $electronics->id,
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'laptops'],
            [
                'name' => 'Laptops',
                'slug' => 'laptops',
                'parent_id' => $electronics->id,
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'mens-clothing'],
            [
                'name' => "Men's Clothing",
                'slug' => 'mens-clothing',
                'parent_id' => $clothing->id,
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'womens-clothing'],
            [
                'name' => "Women's Clothing",
                'slug' => 'womens-clothing',
                'parent_id' => $clothing->id,
                'sort_order' => 2,
                'is_active' => true,
            ]
        );
    }
}