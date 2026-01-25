<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create specific test users
        User::create([
            'name' => 'Hihi Mei',
            'email' => 'hihi@mail.com',
            'password' => Hash::make('password'),
            'address' => '123 Main St',
            'city' => 'New York',
            'country' => 'USA',
            'zip_code' => '10001',
            'phone_number' => '+1234567890',
            'profile_completed' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@mail.com',
            'password' => Hash::make('password'),
            'address' => '456 Oak Ave',
            'city' => 'Los Angeles',
            'country' => 'USA',
            'zip_code' => '90001',
            'phone_number' => '+1234567891',
            'profile_completed' => true,
            'email_verified_at' => now(),
        ]);

        // Create random users using factory
        User::factory(8)->profileCompleted()->create();
        User::factory(5)->create(); // Some with incomplete profiles
    }
}