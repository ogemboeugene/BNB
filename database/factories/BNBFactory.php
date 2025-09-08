<?php

namespace Database\Factories;

use App\Models\BNB;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * BNB Model Factory
 * 
 * Generates fake BNB data for testing and seeding purposes.
 * Creates realistic property listings with comprehensive attributes.
 * 
 * @extends Factory<BNB>
 */
class BNBFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BNB::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amenities = $this->faker->randomElements([
            'wifi', 'parking', 'pool', 'gym', 'spa', 'restaurant', 'bar', 
            'room_service', 'concierge', 'pet_friendly', 'smoking_allowed',
            'air_conditioning', 'heating', 'kitchen', 'balcony', 'garden'
        ], $this->faker->numberBetween(2, 8));

        return [
            'name' => $this->faker->words(3, true) . ' ' . $this->faker->randomElement(['House', 'Apartment', 'Villa', 'Condo', 'Studio']),
            'location' => $this->faker->city . ', ' . $this->faker->state,
            'description' => $this->faker->paragraphs(3, true),
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'max_guests' => $this->faker->numberBetween(1, 8),
            'bedrooms' => $this->faker->numberBetween(1, 4),
            'bathrooms' => $this->faker->numberBetween(1, 3),
            'amenities' => $amenities,
            'price_per_night' => $this->faker->randomFloat(2, 50, 500),
            'availability' => $this->faker->boolean(80), // 80% chance of being available
            'average_rating' => $this->faker->randomFloat(2, 3.0, 5.0),
            'total_reviews' => $this->faker->numberBetween(0, 100),
            'view_count' => $this->faker->numberBetween(0, 1000),
            'image_url' => 'https://res.cloudinary.com/demo/image/upload/sample.jpg',
            'featured' => false,
        ];
    }

    /**
     * Create a featured BNB.
     *
     * @return Factory
     */
    public function featured(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'featured' => true,
                'price_per_night' => $this->faker->randomFloat(2, 200, 800), // Higher prices for featured
                'average_rating' => $this->faker->randomFloat(2, 4.0, 5.0), // Higher ratings for featured
                'total_reviews' => $this->faker->numberBetween(20, 100),
                'availability' => true,
            ];
        });
    }

    /**
     * Create a luxury BNB.
     *
     * @return Factory
     */
    public function luxury(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'price_per_night' => $this->faker->randomFloat(2, 300, 1000),
                'max_guests' => $this->faker->numberBetween(4, 12),
                'bedrooms' => $this->faker->numberBetween(2, 6),
                'bathrooms' => $this->faker->numberBetween(2, 4),
                'amenities' => ['wifi', 'parking', 'pool', 'gym', 'spa', 'restaurant', 'bar', 'room_service', 'concierge'],
                'average_rating' => $this->faker->randomFloat(2, 4.2, 5.0),
                'total_reviews' => $this->faker->numberBetween(30, 150),
            ];
        });
    }

    /**
     * Create a budget-friendly BNB.
     *
     * @return Factory
     */
    public function budget(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'price_per_night' => $this->faker->randomFloat(2, 30, 100),
                'max_guests' => $this->faker->numberBetween(1, 4),
                'bedrooms' => $this->faker->numberBetween(1, 2),
                'bathrooms' => 1,
                'amenities' => ['wifi', 'heating'],
                'average_rating' => $this->faker->randomFloat(2, 3.0, 4.5),
                'total_reviews' => $this->faker->numberBetween(5, 50),
            ];
        });
    }

    /**
     * Create an available BNB.
     *
     * @return Factory
     */
    public function available(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'availability' => true,
            ];
        });
    }

    /**
     * Create an unavailable BNB.
     *
     * @return Factory
     */
    public function unavailable(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'availability' => false,
            ];
        });
    }
}
