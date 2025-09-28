<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class AdminFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Admin::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password_hash' => Hash::make('password123'), // Default password for testing
            'role' => $this->faker->randomElement(['moderator', 'super']),
            'status' => 'pending', // Default status for new admins
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
            'avatar_path' => null,
        ];
    }

    /**
     * Indicate that the admin is a super admin.
     */
    public function super(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'super',
            'status' => 'active',
            'approved_by' => null, // Super admins don't need approval
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the admin is a moderator.
     */
    public function moderator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'moderator',
            'status' => 'pending', // Moderators need approval by default
        ]);
    }

    /**
     * Indicate that the admin is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'approved_by' => 1, // Assume approved by first super admin
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the admin is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the admin is banned.
     */
    public function banned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'banned',
            'approved_by' => 1, // Assume banned by first super admin
            'approved_at' => now(),
            'rejection_reason' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the admin has been approved by a specific admin.
     */
    public function approvedBy(int $approverId): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'approved_by' => $approverId,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Create a super admin with specific credentials (for testing).
     */
    public function testSuperAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Test Super Admin',
            'email' => 'super@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'super',
            'status' => 'active',
            'approved_by' => null,
            'approved_at' => now(),
        ]);
    }

    /**
     * Create a moderator admin with specific credentials (for testing).
     */
    public function testModerator(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Test Moderator',
            'email' => 'mod@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'moderator',
            'status' => 'active',
            'approved_by' => 1,
            'approved_at' => now(),
        ]);
    }

    /**
     * Create an admin with an avatar.
     */
    public function withAvatar(): static
    {
        return $this->state(fn (array $attributes) => [
            'avatar_path' => 'avatars/admins/' . fake()->uuid() . '.jpg',
        ]);
    }
}
