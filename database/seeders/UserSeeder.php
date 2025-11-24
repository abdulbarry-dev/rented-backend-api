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
        $this->command->info('Seeding users...');

        // Prevent duplicate seeding
        if (User::count() > 0) {
            $this->command->info('Users already exist. Skipping base user creation.');
        } else {
            // Create test users with known credentials
            $this->createTestUsers();

            // Create realistic customer users (60% of total)
            $customerCount = 15;
            User::factory()
                ->customer()
                ->count($customerCount)
                ->create();

            $this->command->info("Created {$customerCount} customer users");

            // Create realistic seller users (40% of total)
            $sellerCount = 10;
            User::factory()
                ->seller()
                ->count($sellerCount)
                ->create();

            $this->command->info("Created {$sellerCount} seller users");

            // Create some inactive users for testing
            User::factory()
                ->customer()
                ->inactive()
                ->count(2)
                ->create();

            User::factory()
                ->seller()
                ->inactive()
                ->count(1)
                ->create();

            $this->command->info('Created inactive users for testing');
        }

        $totalUsers = User::count();
        $this->command->info("User seeding completed! Total users: {$totalUsers}");
        $this->command->info("  - Customers: " . User::where('role', 'customer')->count());
        $this->command->info("  - Sellers: " . User::where('role', 'seller')->count());
        $this->command->info("  - Active: " . User::where('is_active', true)->count());
        $this->command->info("  - Inactive: " . User::where('is_active', false)->count());
    }

    /**
     * Create test users with known credentials for API testing
     */
    private function createTestUsers(): void
    {
        // Test customer user
        User::create([
            'full_name' => 'John Customer',
            'email' => 'customer@test.com',
            'phone' => '+1234567890',
            'gender' => 'H',
            'avatar_path' => null,
            'email_verified_at' => now(),
            'password_hash' => Hash::make('password123'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->command->info('Created test customer: customer@test.com');

        // Test seller user
        User::create([
            'full_name' => 'Jane Seller',
            'email' => 'seller@test.com',
            'phone' => '+1234567891',
            'gender' => 'F',
            'avatar_path' => null,
            'email_verified_at' => now(),
            'password_hash' => Hash::make('password123'),
            'role' => 'seller',
            'is_active' => true,
        ]);

        $this->command->info('Created test seller: seller@test.com');

        // Unverified user for testing email verification
        User::create([
            'full_name' => 'Unverified User',
            'email' => 'unverified@test.com',
            'phone' => '+1234567892',
            'gender' => 'H',
            'avatar_path' => null,
            'email_verified_at' => null,
            'password_hash' => Hash::make('password123'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->command->info('Created unverified user: unverified@test.com');
    }
}
