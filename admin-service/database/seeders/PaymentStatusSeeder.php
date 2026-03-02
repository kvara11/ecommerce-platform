<?php

namespace Database\Seeders;

use App\Models\PaymentStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentStatus::firstOrCreate(
            ['name' => 'pending'],
            [
                'id' => 1,
                'label' => 'Pending',
            ]
        );

        PaymentStatus::firstOrCreate(
            ['name' => 'paid'],
            [
                'id' => 2,
                'label' => 'Paid',
            ]
        );

        PaymentStatus::firstOrCreate(
            ['name' => 'failed'],
            [
                'id' => 3,
                'label' => 'Failed',
            ]
        );

        PaymentStatus::firstOrCreate(
            ['name' => 'refunded'],
            [
                'id' => 4,
                'label' => 'Refunded',
            ]
        );
    }
}