<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'status' => fake()->randomElement(['available', 'rented', 'maintenance']),
        ];
    }

    /**
     * Product with available status
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'available',
        ]);
    }

    /**
     * Product with rented status
     */
    public function rented(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rented',
        ]);
    }

    /**
     * Product with maintenance status
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'maintenance',
        ]);
    }

    /**
     * Product owned by a specific user
     */
    public function ownedBy(User|int $owner): static
    {
        $ownerId = $owner instanceof User ? $owner->id : $owner;
        
        return $this->state(fn (array $attributes) => [
            'owner_id' => $ownerId,
        ]);
    }
}
