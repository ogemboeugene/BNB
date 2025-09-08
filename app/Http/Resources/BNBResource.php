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
                'address' => $this->location,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'pricing' => [
                'price_per_night' => $this->price_per_night,
                'currency' => 'USD',
                'formatted_price' => '$' . number_format($this->price_per_night, 2),
            ],
            'capacity' => [
                'bedrooms' => $this->bedrooms,
                'bathrooms' => $this->bathrooms,
                'max_guests' => $this->max_guests,
            ],
            'features' => [
                'amenities' => $this->amenities ?? [],
            ],
            'availability' => [
                'is_available' => $this->availability,
                'status' => $this->availability ? 'available' : 'unavailable',
            ],
            'rating' => [
                'average_rating' => $this->average_rating,
                'total_reviews' => $this->total_reviews,
            ],
            'media' => [
                'image_url' => $this->image_url,
            ],
            'stats' => [
                'view_count' => $this->view_count,
            ],
            'metadata' => [
                'created_at' => $this->created_at->toISOString(),
                'updated_at' => $this->updated_at->toISOString(),
                'last_modified' => $this->updated_at->diffForHumans(),
            ],
            'links' => [
                'self' => route('api.v1.bnbs.show', $this->id),
                'update' => route('api.v1.bnbs.update', $this->id),
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
