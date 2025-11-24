<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with comprehensive test data.
     * 
     * This seeder orchestrates the creation of a complete, realistic dataset
     * for testing the rental marketplace API, including:
     * - Admin accounts (super admins and moderators)
     * - User accounts (customers and sellers)
     * - Products with descriptions and verifications
     * - User verifications (identity, business, address)
     * - Product reviews and ratings
     * 
     * All seeders are relationship-aware and create data in the correct order
     * to maintain referential integrity.
     */
    public function run(): void
    {
        $this->command->info('========================================');
        $this->command->info('Starting Database Seeding');
        $this->command->info('========================================');
        $this->command->newLine();

        // Step 1: Seed admins (required first for verification reviews)
        $this->command->info('Step 1/5: Seeding admins...');
        $this->call(AdminSeeder::class);
        $this->command->newLine();

        // Step 2: Seed users (required for products and reviews)
        $this->command->info('Step 2/5: Seeding users...');
        $this->call(UserSeeder::class);
        $this->command->newLine();

        // Step 3: Seed products with descriptions and verifications
        $this->command->info('Step 3/5: Seeding products...');
        $this->call(ProductSeeder::class);
        $this->command->newLine();

        // Step 4: Seed user verifications
        $this->command->info('Step 4/5: Seeding user verifications...');
        $this->call(UserVerificationSeeder::class);
        $this->command->newLine();

        // Step 5: Seed reviews (requires products and users)
        $this->command->info('Step 5/5: Seeding reviews...');
        $this->call(ReviewSeeder::class);
        $this->command->newLine();

        $this->command->info('========================================');
        $this->command->info('Database Seeding Completed Successfully!');
        $this->command->info('========================================');
        $this->command->newLine();

        $this->displaySummary();
    }

    /**
     * Display a summary of seeded data
     */
    private function displaySummary(): void
    {
        $this->command->info('📊 Seeding Summary:');
        $this->command->info('------------------------------------------');
        $this->command->table(
            ['Entity', 'Count'],
            [
                ['Admins', \App\Models\Admin::count()],
                ['Users', \App\Models\User::count()],
                ['Products', \App\Models\Product::count()],
                ['Product Descriptions', \App\Models\ProductDescription::count()],
                ['Product Verifications', \App\Models\ProductVerification::count()],
                ['User Verifications', \App\Models\UserVerification::count()],
                ['Reviews', \App\Models\Review::count()],
            ]
        );
        
        $this->command->newLine();
        $this->command->info('🔐 Test Credentials:');
        $this->command->info('------------------------------------------');
        $this->command->info('Super Admin:');
        $this->command->info('  Email: super@admin.com');
        $this->command->info('  Password: password123');
        $this->command->newLine();
        $this->command->info('Test Customer:');
        $this->command->info('  Email: customer@test.com');
        $this->command->info('  Password: password123');
        $this->command->newLine();
        $this->command->info('Test Seller:');
        $this->command->info('  Email: seller@test.com');
        $this->command->info('  Password: password123');
        $this->command->newLine();
    }
}
