<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductDescription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductDescription>
 */
class ProductDescriptionFactory extends Factory
{
    protected $model = ProductDescription::class;

    /**
     * Realistic product categories for a rental marketplace
     */
    private const CATEGORIES = [
        'Electronics',
        'Tools & Equipment',
        'Furniture',
        'Outdoor & Sports',
        'Vehicles',
        'Party & Events',
        'Photography',
        'Home & Garden',
        'Musical Instruments',
        'Gaming',
    ];

    /**
     * Realistic product titles by category
     */
    private const PRODUCT_TITLES = [
        'Electronics' => ['MacBook Pro 16"', 'Sony A7III Camera', 'DJI Mavic 3 Drone', 'iPad Pro 12.9"', 'PlayStation 5'],
        'Tools & Equipment' => ['Power Drill Set', 'Ladder Extension 24ft', 'Pressure Washer 3000PSI', 'Chainsaw Professional', 'Generator 5000W'],
        'Furniture' => ['Leather Sofa 3-Seater', 'Dining Table Set', 'Office Desk Standing', 'Queen Size Bed Frame', 'Bookshelf Oak Wood'],
        'Outdoor & Sports' => ['Mountain Bike 27.5"', 'Camping Tent 6-Person', 'Kayak Inflatable', 'Ski Equipment Complete Set', 'BBQ Grill Propane'],
        'Vehicles' => ['Tesla Model 3 2023', 'Toyota Camry 2022', 'Moving Truck 20ft', 'Electric Scooter', 'Cargo Van'],
        'Party & Events' => ['Bounce House Commercial', 'Sound System PA', 'Projector 4K', 'Tables & Chairs Set', 'Photo Booth'],
        'Photography' => ['Canon EOS R5', 'Studio Lighting Kit', 'Gimbal Stabilizer', 'Lens Kit Professional', 'Green Screen Setup'],
        'Home & Garden' => ['Lawn Mower Riding', 'Carpet Cleaner Pro', 'Leaf Blower Commercial', 'Hedge Trimmer Electric', 'Patio Heater'],
        'Musical Instruments' => ['Yamaha Digital Piano', 'Fender Electric Guitar', 'Roland Drum Kit', 'PA Speaker System', 'DJ Controller'],
        'Gaming' => ['Gaming PC RTX 4090', 'VR Headset Meta Quest 3', 'Racing Simulator Setup', 'Gaming Chair Pro', 'Streaming Equipment'],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = fake()->randomElement(array_keys(self::PRODUCT_TITLES));
        $title = fake()->randomElement(self::PRODUCT_TITLES[$category]);

        return [
            'product_id' => Product::factory(),
            'title' => $title,
            'description' => $this->generateRealisticDescription($title, $category),
            'product_images' => $this->generateImagePaths(),
            'categories' => $this->generateCategories($category),
        ];
    }

    /**
     * Generate a realistic product description
     */
    private function generateRealisticDescription(string $title, string $category): string
    {
        $descriptions = [
            "High-quality {$title} available for rent. Perfect condition, well-maintained, and ready to use.",
            "Rent this premium {$title} for your next project or event. Includes all necessary accessories and instructions.",
            "Professional-grade {$title} in excellent condition. Ideal for both beginners and experts.",
            "Top-rated {$title} available at competitive rates. Flexible rental periods with delivery options available.",
            "Like-new {$title} with complete accessories. Perfect for weekend projects or special occasions.",
        ];

        $features = [
            "• Latest model with all features\n• Sanitized and fully tested\n• Free delivery within city limits",
            "• Professional quality\n• Insurance included\n• 24/7 customer support",
            "• Recently serviced\n• Instruction manual included\n• Flexible pickup/return times",
            "• Excellent reviews\n• Competitive pricing\n• Available for short or long-term rental",
            "• Premium condition\n• Complete package\n• Easy online booking",
        ];

        return fake()->randomElement($descriptions) . "\n\n" . fake()->randomElement($features);
    }

    /**
     * Generate realistic image paths
     */
    private function generateImagePaths(): array
    {
        $count = fake()->numberBetween(2, 5);
        $images = [];
        
        for ($i = 0; $i < $count; $i++) {
            $images[] = 'products/' . fake()->uuid() . '.jpg';
        }
        
        return $images;
    }

    /**
     * Generate categories array
     */
    private function generateCategories(string $primaryCategory): array
    {
        $categories = [$primaryCategory];
        
        // Add 1-2 additional relevant categories
        $additionalCount = fake()->numberBetween(0, 2);
        $availableCategories = array_diff(self::CATEGORIES, [$primaryCategory]);
        
        for ($i = 0; $i < $additionalCount; $i++) {
            $categories[] = fake()->randomElement($availableCategories);
        }
        
        return array_unique($categories);
    }

    /**
     * For a specific category
     */
    public function category(string $category): static
    {
        return $this->state(function (array $attributes) use ($category) {
            $title = fake()->randomElement(self::PRODUCT_TITLES[$category] ?? self::PRODUCT_TITLES['Electronics']);
            
            return [
                'title' => $title,
                'description' => $this->generateRealisticDescription($title, $category),
                'categories' => $this->generateCategories($category),
            ];
        });
    }

    /**
     * Product for a specific product
     */
    public function forProduct(Product|int $product): static
    {
        $productId = $product instanceof Product ? $product->id : $product;
        
        return $this->state(fn (array $attributes) => [
            'product_id' => $productId,
        ]);
    }
}
