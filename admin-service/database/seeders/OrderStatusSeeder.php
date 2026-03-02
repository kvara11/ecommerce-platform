<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OrderStatus::firstOrCreate(
            ['name' => 'pending'],
            [
                'id' => 1,
                'label' => 'Pending',
            ]
        );

        OrderStatus::firstOrCreate(
            ['name' => 'processing'],
            [
                'id' => 2,
                'label' => 'Processing',
            ]
        );

        OrderStatus::firstOrCreate(
            ['name' => 'confirmed'],
            [
                'id' => 3,
                'label' => 'Confirmed',
            ]
        );

        OrderStatus::firstOrCreate(
            ['name' => 'shipped'],
            [
                'id' => 4,
                'label' => 'Shipped',
            ]
        );

        OrderStatus::firstOrCreate(
            ['name' => 'delivered'],
            [
                'id' => 5,
                'label' => 'Delivered',
            ]
        );

        OrderStatus::firstOrCreate(
            ['name' => 'cancelled'],
            [
                'id' => 6,
                'label' => 'Cancelled',
            ]
        );

        OrderStatus::firstOrCreate(
            ['name' => 'refunded'],
            [
                'id' => 7,
                'label' => 'Refunded',
            ]
        );
    }
}
