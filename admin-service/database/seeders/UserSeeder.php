<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'password' => Hash::make('password'),
                'role_id' => 1,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@test.com'],
            [
                'first_name' => 'Demo',
                'last_name' => 'Customer',
                'password' => Hash::make('password'),
                'role_id' => 2,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}