<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'id' => 1,
                'label' => 'Administrator',
            ]
        );

        Role::firstOrCreate(
            ['name' => 'customer'],
            [
                'id' => 2,
                'label' => 'Customer',
            ]
        );
    }
}