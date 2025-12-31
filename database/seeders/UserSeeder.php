<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Default Owner (only if doesn't exist)
        if (!User::where('username', 'owner')->exists()) {
            User::create([
                'name' => 'Hostel Owner',
                'username' => 'owner',
                'email' => 'owner@hostel.com',
                'password' => Hash::make('Admin@3345'),
                'role' => 'owner',
            ]);
        }
    }
}

