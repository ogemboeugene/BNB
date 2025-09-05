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
        $amenities = [
            'WiFi', 'Air Conditioning', 'Kitchen', 'Free Parking', 'Washer',
            'Dryer', 'TV', 'Coffee Maker', 'Microwave', 'Dishwasher',
            'Balcony', 'Garden', 'Pool', 'Hot Tub', 'Fireplace',
            'Gym', 'Elevator', 'Pet Friendly', 'Smoking Allowed', 'Wheelchair Accessible'
        ];

        $houseRules = [
            'No smoking indoors',
            'No pets allowed',
            'Check-in after 3 PM',
            'Check-out before 11 AM',
            'Quiet hours 10 PM - 8 AM',
            'Maximum 2 guests',
            'No parties or events',
            'Please remove shoes'
        ];

        $images = [
            'https://example.com/images/property1.jpg',
            'https://example.com/images/property2.jpg',
            'https://example.com/images/property3.jpg',
            'https://example.com/images/property4.jpg',
            'https://example.com/images/property5.jpg'
        ];

        $bedrooms = $this->faker->numberBetween(1, 5);
        $bathrooms = $this->faker->randomFloat(1, 1, $bedrooms + 1);
        $maxGuests = $bedrooms * 2;

        return [
            'name' => $this->faker->words(3, true) . ' ' . $this->faker->randomElement(['House', 'Apartment', 'Villa', 'Condo', 'Studio']),
            'description' => $this->faker->paragraphs(3, true),
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'country' => $this->faker->country,
            'postal_code' => $this->faker->postcode,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'price_per_night' => $this->faker->randomFloat(2, 50, 500),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP', 'CAD']),
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'max_guests' => $maxGuests,
            'is_available' => $this->faker->boolean(80), // 80% chance of being available
            'available_from' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'available_to' => $this->faker->optional()->dateTimeBetween('+1 month', '+1 year'),
            'amenities' => json_encode($this->faker->randomElements($amenities, $this->faker->numberBetween(3, 8))),
            'house_rules' => json_encode($this->faker->randomElements($houseRules, $this->faker->numberBetween(2, 5))),
            'images' => json_encode($this->faker->randomElements($images, $this->faker->numberBetween(1, 5))),
            'is_featured' => $this->faker->boolean(20), // 20% chance of being featured
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
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
                'is_featured' => true,
                'price_per_night' => $this->faker->randomFloat(2, 200, 800), // Higher prices for featured
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
                'is_available' => true,
                'available_from' => now(),
                'available_to' => $this->faker->dateTimeBetween('+1 month', '+1 year'),
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
                'is_available' => false,
                'available_from' => null,
                'available_to' => null,
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
                'name' => 'Luxury ' . $this->faker->words(2, true) . ' ' . $this->faker->randomElement(['Villa', 'Penthouse', 'Manor', 'Estate']),
                'price_per_night' => $this->faker->randomFloat(2, 300, 1000),
                'bedrooms' => $this->faker->numberBetween(3, 7),
                'bathrooms' => $this->faker->randomFloat(1, 3, 8),
                'max_guests' => $this->faker->numberBetween(6, 14),
                'is_featured' => true,
                'amenities' => json_encode([
                    'WiFi', 'Air Conditioning', 'Kitchen', 'Free Parking', 'Washer',
                    'Dryer', 'TV', 'Pool', 'Hot Tub', 'Fireplace', 'Gym', 'Spa',
                    'Chef', 'Concierge', 'Private Beach', 'Wine Cellar'
                ]),
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
                'name' => 'Cozy ' . $this->faker->words(2, true) . ' ' . $this->faker->randomElement(['Studio', 'Room', 'Apartment']),
                'price_per_night' => $this->faker->randomFloat(2, 25, 80),
                'bedrooms' => $this->faker->numberBetween(1, 2),
                'bathrooms' => 1,
                'max_guests' => $this->faker->numberBetween(1, 4),
                'amenities' => json_encode(['WiFi', 'Kitchen', 'TV']),
            ];
        });
    }
}
