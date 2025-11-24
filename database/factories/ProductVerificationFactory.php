<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductVerification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVerification>
 */
class ProductVerificationFactory extends Factory
{
    protected $model = ProductVerification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'verification_status' => 'pending',
            'notes' => null,
            'reviewed_by' => null,
            'submitted_at' => now(),
            'reviewed_at' => null,
        ];
    }

    /**
     * Pending verification
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'pending',
            'notes' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);
    }

    /**
     * Verified status
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'verified',
            'notes' => 'Product verified and approved for listing.',
            'reviewed_by' => Admin::where('role', 'super')->first()?->id ?? 1,
            'reviewed_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Rejected status
     */
    public function rejected(): static
    {
        $rejectionReasons = [
            'Product images do not match description.',
            'Insufficient product information provided.',
            'Product appears to be in poor condition.',
            'Pricing does not align with product quality.',
            'Product category inappropriate for platform.',
        ];

        return $this->state(fn (array $attributes) => [
            'verification_status' => 'rejected',
            'notes' => fake()->randomElement($rejectionReasons),
            'reviewed_by' => Admin::where('role', 'super')->first()?->id ?? 1,
            'reviewed_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * For a specific product
     */
    public function forProduct(Product|int $product): static
    {
        $productId = $product instanceof Product ? $product->id : $product;
        
        return $this->state(fn (array $attributes) => [
            'product_id' => $productId,
        ]);
    }

    /**
     * Reviewed by specific admin
     */
    public function reviewedBy(Admin|int $admin): static
    {
        $adminId = $admin instanceof Admin ? $admin->id : $admin;
        
        return $this->state(fn (array $attributes) => [
            'reviewed_by' => $adminId,
            'reviewed_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}
