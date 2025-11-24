<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\User;
use App\Models\UserVerification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserVerification>
 */
class UserVerificationFactory extends Factory
{
    protected $model = UserVerification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'national_id',
            'verification_status' => 'pending',
            'image_paths' => $this->generateImagePaths('national_id'),
            'notes' => null,
            'reviewed_by' => null,
            'submitted_at' => fake()->dateTimeBetween('-2 months', 'now'),
            'reviewed_at' => null,
        ];
    }

    /**
     * Generate image paths based on verification type
     */
    private function generateImagePaths(string $type): array
    {
        $count = 2; // Front and back of national ID

        $images = [];
        for ($i = 0; $i < $count; $i++) {
            $images[] = "verifications/{$type}/" . fake()->uuid() . '.jpg';
        }
        
        return $images;
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
            'notes' => 'Verification approved. All documents are valid.',
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
            'Documents are unclear or unreadable.',
            'Submitted documents do not match user information.',
            'Expired documents provided.',
            'Additional documentation required.',
            'Documents appear to be altered or invalid.',
        ];

        return $this->state(fn (array $attributes) => [
            'verification_status' => 'rejected',
            'notes' => fake()->randomElement($rejectionReasons),
            'reviewed_by' => Admin::where('role', 'super')->first()?->id ?? 1,
            'reviewed_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Identity/National ID verification (default type)
     */
    public function identity(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'national_id',
            'image_paths' => $this->generateImagePaths('national_id'),
        ]);
    }

    /**
     * National ID verification (alias for identity)
     */
    public function nationalId(): static
    {
        return $this->identity();
    }

    /**
     * Business verification (uses national_id type - only type available)
     */
    public function business(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'national_id',
            'image_paths' => $this->generateImagePaths('national_id'),
        ]);
    }

    /**
     * Address verification (uses national_id type - only type available)
     */
    public function address(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'national_id',
            'image_paths' => $this->generateImagePaths('national_id'),
        ]);
    }

    /**
     * For a specific user
     */
    public function forUser(User|int $user): static
    {
        $userId = $user instanceof User ? $user->id : $user;
        
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
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
