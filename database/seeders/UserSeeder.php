<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 5 random users only if we don't have enough users yet
        $existingUsersCount = User::count();
        if ($existingUsersCount < 5) {
            $usersToCreate = 5 - $existingUsersCount;
            
            // Create users with different roles
            $customersToCreate = (int) ceil($usersToCreate / 2);
            $sellersToCreate = $usersToCreate - $customersToCreate;
            
            // Create customers
            if ($customersToCreate > 0) {
                User::factory($customersToCreate)->customer()->create();
            }
            
            // Create sellers  
            if ($sellersToCreate > 0) {
                User::factory($sellersToCreate)->seller()->create();
            }
        }

        // Create or update test customer user
        User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'phone' => '+1234567890',
                'avatar_path' => null,
                'email_verified_at' => now(),
                'password_hash' => Hash::make('password123'),
                'role' => 'customer',
                'is_active' => true,
            ]
        );

        // Create or update test seller user
        User::updateOrCreate(
            ['email' => 'seller@example.com'],
            [
                'first_name' => 'Test',
                'last_name' => 'Seller',
                'phone' => '+1234567891',
                'avatar_path' => null,
                'email_verified_at' => now(),
                'password_hash' => Hash::make('password123'),
                'role' => 'seller',
                'is_active' => true,
            ]
        );
    }
}
