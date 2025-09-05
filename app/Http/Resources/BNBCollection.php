<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class BNBCollection
 * 
 * API resource collection for transforming paginated BNB data.
 * Provides consistent structure for collections with metadata.
 * 
 * @package App\Http\Resources
 */
class BNBCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'summary' => [
                'total_items' => $this->when($this->resource instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator, 
                    $this->resource->total(), 
                    $this->collection->count()
                ),
                'current_page_items' => $this->collection->count(),
                'available_count' => $this->collection->where('availability', true)->count(),
                'unavailable_count' => $this->collection->where('availability', false)->count(),
                'average_price' => round($this->collection->avg('price_per_night'), 2),
                'price_range' => [
                    'min' => $this->collection->min('price_per_night'),
                    'max' => $this->collection->max('price_per_night'),
                ],
            ],
            'filters' => [
                'applied' => $request->only(['location', 'min_price', 'max_price', 'availability', 'name']),
                'suggestions' => $this->when($this->collection->isNotEmpty(), [
                    'locations' => $this->collection->pluck('location')->unique()->filter()->values(),
                ], []),
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
                'resource_type' => 'bnb_collection',
            ],
            'links' => [
                'self' => $request->url(),
                'create' => route('api.v1.bnbs.store'),
            ],
        ];
    }
}
