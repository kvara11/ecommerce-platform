<?php

namespace Database\Seeders;

use App\Models\PaymentMethods;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentMethods::firstOrCreate(
            ['name' => 'cash'],
            [
                'id' => 1,
                'label' => 'Cash',
            ]
        );

        PaymentMethods::firstOrCreate(
            ['name' => 'card'],
            [
                'id' => 2,
                'label' => 'Card',
            ]
        );

        PaymentMethods::firstOrCreate(
            ['name' => 'paypal'],
            [
                'id' => 3,
                'label' => 'Paypal',
            ]
        );
    }
}
