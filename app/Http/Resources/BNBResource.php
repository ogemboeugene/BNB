<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class BNBResource
 * 
 * API resource for transforming BNB model data into a consistent JSON format.
 * Provides structured output for single BNB records with computed fields.
 * 
 * @package App\Http\Resources
 */
class BNBResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'location' => [
                'address' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
                'postal_code' => $this->postal_code,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'pricing' => [
                'price_per_night' => $this->price_per_night,
                'currency' => $this->currency ?? 'USD',
                'formatted_price' => $this->getFormattedPrice(),
            ],
            'capacity' => [
                'bedrooms' => $this->bedrooms,
                'bathrooms' => $this->bathrooms,
                'max_guests' => $this->max_guests,
            ],
            'features' => [
                'amenities' => $this->amenities ? json_decode($this->amenities, true) : [],
                'house_rules' => $this->house_rules ? json_decode($this->house_rules, true) : [],
            ],
            'availability' => [
                'is_available' => $this->is_available,
                'available_from' => $this->available_from?->toISOString(),
                'available_to' => $this->available_to?->toISOString(),
                'status' => $this->getAvailabilityStatus(),
            ],
            'media' => [
                'images' => $this->images ? json_decode($this->images, true) : [],
                'main_image' => $this->getMainImage(),
            ],
            'metadata' => [
                'created_at' => $this->created_at->toISOString(),
                'updated_at' => $this->updated_at->toISOString(),
                'last_modified' => $this->updated_at->diffForHumans(),
                'is_featured' => $this->is_featured ?? false,
            ],
            'links' => [
                'self' => route('api.v1.bnbs.show', $this->id),
                'update' => route('api.v1.bnbs.update', $this->id),
                'delete' => route('api.v1.bnbs.destroy', $this->id),
            ],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => 'v1',
                'timestamp' => now()->toISOString(),
                'resource_type' => 'bnb',
            ],
        ];
    }
}
