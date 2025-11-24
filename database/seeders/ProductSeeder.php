<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductDescription;
use App\Models\ProductVerification;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding products...');

        // Get all seller users
        $sellers = User::where('role', 'seller')->get();

        if ($sellers->isEmpty()) {
            $this->command->warn('No sellers found. Creating sellers first...');
            $sellers = User::factory()->seller()->count(5)->create();
        }

        // Distribute products among sellers
        foreach ($sellers as $seller) {
            $productCount = fake()->numberBetween(2, 8);
            
            $this->command->info("Creating {$productCount} products for seller: {$seller->full_name}");

            for ($i = 0; $i < $productCount; $i++) {
                // Create product
                $product = Product::factory()
                    ->ownedBy($seller)
                    ->create([
                        'status' => fake()->randomElement([
                            'available',    // 50% available
                            'available',
                            'rented',       // 25% rented
                            'maintenance',  // 25% maintenance
                        ]),
                    ]);

                // Create product description
                ProductDescription::factory()
                    ->forProduct($product)
                    ->create();

                // Create product verification (70% verified, 20% pending, 10% rejected)
                $verificationStatus = fake()->randomElement([
                    'verified', 'verified', 'verified', 'verified', 'verified', 'verified', 'verified',
                    'pending', 'pending',
                    'rejected',
                ]);

                ProductVerification::factory()
                    ->forProduct($product)
                    ->{$verificationStatus}()
                    ->create();
            }
        }

        // Create some products with specific scenarios for testing

        // 1. Available product with high ratings
        $popularProduct = Product::factory()->available()->create();
        ProductDescription::factory()->forProduct($popularProduct)->create([
            'title' => 'Premium Camera Kit - Sony A7III',
            'categories' => ['Electronics', 'Photography'],
        ]);
        ProductVerification::factory()->forProduct($popularProduct)->verified()->create();

        // 2. Recently added product (pending verification)
        $newProduct = Product::factory()->available()->create();
        ProductDescription::factory()->forProduct($newProduct)->create([
            'title' => 'Brand New Gaming Setup',
            'categories' => ['Electronics', 'Gaming'],
        ]);
        ProductVerification::factory()->forProduct($newProduct)->pending()->create();

        // 3. Rejected product
        $rejectedProduct = Product::factory()->maintenance()->create();
        ProductDescription::factory()->forProduct($rejectedProduct)->create([
            'title' => 'Used Furniture Set',
            'categories' => ['Furniture'],
        ]);
        ProductVerification::factory()->forProduct($rejectedProduct)->rejected()->create();

        $totalProducts = Product::count();
        $this->command->info("Product seeding completed! Total products: {$totalProducts}");
        $this->command->info("  - Available: " . Product::where('status', 'available')->count());
        $this->command->info("  - Rented: " . Product::where('status', 'rented')->count());
        $this->command->info("  - Maintenance: " . Product::where('status', 'maintenance')->count());
    }
}
