<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    /**
     * Realistic review comments by rating
     */
    private const REVIEW_COMMENTS = [
        5 => [
            'Excellent product! Exactly as described and in perfect condition.',
            'Amazing experience! The owner was very helpful and the product worked flawlessly.',
            'Highly recommend! Top quality and great value for money.',
            'Perfect! Will definitely rent again. Five stars!',
            'Outstanding service and product quality. Couldn\'t be happier!',
        ],
        4 => [
            'Very good product. Minor wear but still works great.',
            'Good experience overall. Product was as expected with slight usage marks.',
            'Solid rental. Product delivered on time and functioned well.',
            'Happy with the rental. Would recommend with minor reservations.',
            'Pretty good! Small issues but owner was responsive.',
        ],
        3 => [
            'Decent product. Met basic expectations but could be better.',
            'Average experience. Product worked but showed some age.',
            'It\'s okay. Does the job but not exceptional.',
            'Fair rental. Some minor issues but manageable.',
            'Acceptable condition. Served its purpose adequately.',
        ],
        2 => [
            'Below expectations. Product had several issues.',
            'Not great. Had to troubleshoot multiple problems.',
            'Disappointed with the condition. Older than expected.',
            'Mediocre experience. Would look elsewhere next time.',
            'Several problems encountered. Not worth the price.',
        ],
        1 => [
            'Very poor condition. Not as advertised.',
            'Terrible experience. Product barely functional.',
            'Would not recommend. Major issues throughout rental.',
            'Waste of money. Product was in bad shape.',
            'Extremely disappointed. Do not rent this.',
        ],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rating = fake()->numberBetween(1, 5);
        
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'review_rate' => $rating,
            'comment' => fake()->randomElement(self::REVIEW_COMMENTS[$rating]),
            'reviewed_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Create a review with specific rating
     */
    public function rating(int $rating): static
    {
        return $this->state(fn (array $attributes) => [
            'review_rate' => $rating,
            'comment' => fake()->randomElement(self::REVIEW_COMMENTS[$rating] ?? self::REVIEW_COMMENTS[3]),
        ]);
    }

    /**
     * Create a positive review (4-5 stars)
     */
    public function positive(): static
    {
        $rating = fake()->randomElement([4, 5]);
        
        return $this->state(fn (array $attributes) => [
            'review_rate' => $rating,
            'comment' => fake()->randomElement(self::REVIEW_COMMENTS[$rating]),
        ]);
    }

    /**
     * Create a negative review (1-2 stars)
     */
    public function negative(): static
    {
        $rating = fake()->randomElement([1, 2]);
        
        return $this->state(fn (array $attributes) => [
            'review_rate' => $rating,
            'comment' => fake()->randomElement(self::REVIEW_COMMENTS[$rating]),
        ]);
    }

    /**
     * Review by specific user
     */
    public function byUser(User|int $user): static
    {
        $userId = $user instanceof User ? $user->id : $user;
        
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }

    /**
     * Review for specific product
     */
    public function forProduct(Product|int $product): static
    {
        $productId = $product instanceof Product ? $product->id : $product;
        
        return $this->state(fn (array $attributes) => [
            'product_id' => $productId,
        ]);
    }

    /**
     * Recent review
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'reviewed_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}
