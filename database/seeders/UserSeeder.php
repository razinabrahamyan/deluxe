<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user (kept distinct)
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create users: user1..user5
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => 'user' . $i,
                'email' => 'user' . $i . '@example.com',
                'password' => Hash::make('password'),
            ]);
        }
    }
}
