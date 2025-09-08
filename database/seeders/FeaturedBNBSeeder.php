<?php

namespace Database\Seeders;

use App\Models\BNB;
use App\Models\User;
use Illuminate\Database\Seeder;

class FeaturedBNBSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the admin user
        $adminUser = User::where('email', 'admin@bnb.com')->first();
        
        if (!$adminUser) {
            $this->command->error('Admin user with email admin@bnb.com not found!');
            return;
        }

        $featuredProperties = [
            [
                'name' => 'Modern Kitchen Apartment',
                'location' => 'Nairobi, Kenya',
                'latitude' => -1.2921,
                'longitude' => 36.8219,
                'description' => 'Beautiful modern apartment with a fully equipped kitchen, perfect for families and long stays. Located in the heart of Nairobi with easy access to shopping centers and business districts.',
                'amenities' => ['wifi', 'kitchen', 'parking', 'air_conditioning', 'tv', 'washing_machine'],
                'max_guests' => 4,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'price_per_night' => 120.00,
                'availability' => true,
                'featured' => true,
                'average_rating' => 4.8,
                'total_reviews' => 24,
                'view_count' => 156,
                'image_url' => 'https://res.cloudinary.com/dqotqwtlp/image/upload/v1757334771/kitchen_kka8vc.png',
            ],
            [
                'name' => 'Elegant Dining Space',
                'location' => 'Westlands, Nairobi',
                'latitude' => -1.2676,
                'longitude' => 36.8108,
                'description' => 'Sophisticated apartment featuring an elegant dining area perfect for business dinners and special occasions. Premium location in Westlands with panoramic city views.',
                'amenities' => ['wifi', 'kitchen', 'parking', 'balcony', 'tv', 'elevator'],
                'max_guests' => 6,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'price_per_night' => 180.00,
                'availability' => true,
                'featured' => true,
                'average_rating' => 4.9,
                'total_reviews' => 31,
                'view_count' => 203,
                'image_url' => 'https://res.cloudinary.com/dqotqwtlp/image/upload/v1757334770/dinning_rkuppd.png',
            ],
            [
                'name' => 'Kileleshwa Garden Retreat',
                'location' => 'Kileleshwa, Nairobi',
                'latitude' => -1.2856,
                'longitude' => 36.7835,
                'description' => 'Serene garden retreat in the upscale Kileleshwa neighborhood. Perfect for those seeking tranquility while staying close to the city center. Features beautiful landscaped gardens.',
                'amenities' => ['wifi', 'garden', 'parking', 'security', 'gym', 'pool'],
                'max_guests' => 4,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'price_per_night' => 160.00,
                'availability' => true,
                'featured' => true,
                'average_rating' => 4.7,
                'total_reviews' => 18,
                'view_count' => 134,
                'image_url' => 'https://res.cloudinary.com/dqotqwtlp/image/upload/v1757334770/kileleshwa_lkbrnr.jpg',
            ],
            [
                'name' => 'Kilimani Executive Suite',
                'location' => 'Kilimani, Nairobi',
                'latitude' => -1.2912,
                'longitude' => 36.7869,
                'description' => 'Executive suite in the prestigious Kilimani area, ideal for business travelers and professionals. Features modern amenities and close proximity to UN offices and embassies.',
                'amenities' => ['wifi', 'office_space', 'parking', 'concierge', 'tv', 'air_conditioning'],
                'max_guests' => 2,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'price_per_night' => 140.00,
                'availability' => true,
                'featured' => true,
                'average_rating' => 4.6,
                'total_reviews' => 22,
                'view_count' => 98,
                'image_url' => 'https://res.cloudinary.com/dqotqwtlp/image/upload/v1757334770/kilimani_pbcnyg.jpg',
            ],
            [
                'name' => 'Luxury Bedroom Haven',
                'location' => 'Karen, Nairobi',
                'latitude' => -1.3197,
                'longitude' => 36.7076,
                'description' => 'Luxurious bedroom suite in the exclusive Karen area. Perfect for romantic getaways and special occasions. Features premium bedding and stunning views of the Ngong Hills.',
                'amenities' => ['wifi', 'luxury_bedding', 'parking', 'garden', 'fireplace', 'spa'],
                'max_guests' => 2,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'price_per_night' => 200.00,
                'availability' => true,
                'featured' => true,
                'average_rating' => 4.9,
                'total_reviews' => 16,
                'view_count' => 87,
                'image_url' => 'https://res.cloudinary.com/dqotqwtlp/image/upload/v1757334770/bedroom_kvlvu0.jpg',
            ],
            [
                'name' => 'Premium City View',
                'location' => 'Upper Hill, Nairobi',
                'latitude' => -1.2940,
                'longitude' => 36.8155,
                'description' => 'Premium apartment with breathtaking city skyline views. Located in Upper Hill business district, perfect for corporate travelers and those seeking luxury accommodations.',
                'amenities' => ['wifi', 'city_view', 'parking', 'gym', 'pool', 'business_center'],
                'max_guests' => 4,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'price_per_night' => 190.00,
                'availability' => true,
                'featured' => true,
                'average_rating' => 4.8,
                'total_reviews' => 28,
                'view_count' => 167,
                'image_url' => 'https://res.cloudinary.com/dqotqwtlp/image/upload/v1757334770/image_jtol8y.png',
            ],
        ];

        foreach ($featuredProperties as $property) {
            // Check if a property with the same name already exists
            $existingProperty = BNB::where('name', $property['name'])->first();
            
            if (!$existingProperty) {
                BNB::create($property);
                $this->command->info("Created featured BNB: {$property['name']}");
            } else {
                // Update existing property to be featured
                $existingProperty->update([
                    'featured' => true,
                    'image_url' => $property['image_url']
                ]);
                $this->command->info("Updated existing BNB to featured: {$property['name']}");
            }
        }

        $this->command->info('Featured BNBs seeding completed!');
    }
}
