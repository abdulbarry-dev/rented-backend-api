<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding reviews...');

        // Get all products with verified status
        $products = Product::whereHas('verification', function ($query) {
            $query->where('verification_status', 'verified');
        })->get();

        if ($products->isEmpty()) {
            $this->command->warn('No verified products found. Skipping review seeding.');
            return;
        }

        // Get all customer users
        $customers = User::where('role', 'customer')->get();

        if ($customers->isEmpty()) {
            $this->command->warn('No customers found. Creating customers first...');
            $customers = User::factory()->customer()->count(10)->create();
        }

        $totalReviews = 0;
        $createdCombinations = []; // Track user-product combinations

        // Add reviews to products (60% of products have reviews)
        foreach ($products as $product) {
            if (fake()->boolean(60)) {
                $reviewCount = fake()->numberBetween(1, 5);
                
                // Get random customers for this product
                $reviewers = $customers->random(min($reviewCount, $customers->count()));
                
                foreach ($reviewers as $reviewer) {
                    // Ensure user doesn't review their own product
                    if ($reviewer->id === $product->owner_id) {
                        continue;
                    }

                    // Check if this combination already exists
                    $combinationKey = "{$reviewer->id}-{$product->id}";
                    if (isset($createdCombinations[$combinationKey])) {
                        continue;
                    }

                    // Create review with weighted ratings (more positive reviews)
                    $rating = fake()->randomElement([
                        5, 5, 5, 5, 5, // 50% 5-star
                        4, 4, 4,       // 30% 4-star
                        3,             // 10% 3-star
                        2,             // 5% 2-star
                        1,             // 5% 1-star
                    ]);

                    Review::factory()
                        ->forProduct($product)
                        ->byUser($reviewer)
                        ->rating($rating)
                        ->create();

                    $createdCombinations[$combinationKey] = true;
                    $totalReviews++;
                }
            }
        }

        // Create specific test scenarios

        // 1. Product with excellent reviews only
        $topProduct = $products->random();
        $topReviewers = $customers->random(min(5, $customers->count()));
        
        foreach ($topReviewers as $reviewer) {
            $combinationKey = "{$reviewer->id}-{$topProduct->id}";
            
            if ($reviewer->id !== $topProduct->owner_id && !isset($createdCombinations[$combinationKey])) {
                Review::factory()
                    ->forProduct($topProduct)
                    ->byUser($reviewer)
                    ->rating(5)
                    ->create();
                $createdCombinations[$combinationKey] = true;
                $totalReviews++;
            }
        }

        // 2. Product with mixed reviews
        $mixedProduct = $products->random();
        $ratings = [5, 4, 3, 2, 1];
        
        foreach ($ratings as $rating) {
            $reviewer = $customers->random();
            $combinationKey = "{$reviewer->id}-{$mixedProduct->id}";
            
            if ($reviewer->id !== $mixedProduct->owner_id && !isset($createdCombinations[$combinationKey])) {
                Review::factory()
                    ->forProduct($mixedProduct)
                    ->byUser($reviewer)
                    ->rating($rating)
                    ->create();
                $createdCombinations[$combinationKey] = true;
                $totalReviews++;
            }
        }

        $this->command->info("Review seeding completed! Total reviews: {$totalReviews}");
        $this->command->info("  - 5 stars: " . Review::where('review_rate', 5)->count());
        $this->command->info("  - 4 stars: " . Review::where('review_rate', 4)->count());
        $this->command->info("  - 3 stars: " . Review::where('review_rate', 3)->count());
        $this->command->info("  - 2 stars: " . Review::where('review_rate', 2)->count());
        $this->command->info("  - 1 star: " . Review::where('review_rate', 1)->count());
    }
}
