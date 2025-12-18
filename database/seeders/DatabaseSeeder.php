<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '1234567890',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        // Farmer
        User::factory()->create([
            'name' => 'Farmer User',
            'email' => 'farmer@example.com',
            'phone' => '0987654321',
            'role' => 'farmer',
            'password' => bcrypt('password'),
        ]);

        // Customer
        User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'phone' => '1122334455',
            'role' => 'customer',
            'password' => bcrypt('password'),
        ]);

        // Delivery Boy
        User::factory()->create([
            'name' => 'Delivery Boy',
            'email' => 'delivery@example.com',
            'phone' => '5544332211',
            'role' => 'delivery_boy',
            'password' => bcrypt('password'),
        ]);
    }
}
