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
        return [
            'name' => $this->faker->words(3, true) . ' ' . $this->faker->randomElement(['House', 'Apartment', 'Villa', 'Condo', 'Studio']),
            'location' => $this->faker->city . ', ' . $this->faker->state,
            'price_per_night' => $this->faker->randomFloat(2, 50, 500),
            'availability' => $this->faker->boolean(80), // 80% chance of being available
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
