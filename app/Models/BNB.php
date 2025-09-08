<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Class BNB
 * 
 * Represents a Bed and Breakfast property in the management system.
 * This model handles all BNB-related data operations with soft deletes
 * for data recovery and immutability concerns.
 * 
 * @package App\Models
 * 
 * @property int $id
 * @property string $name
 * @property string $location
 * @property float $latitude
 * @property float $longitude
 * @property array $amenities
 * @property string $description
 * @property int $max_guests
 * @property int $bedrooms
 * @property int $bathrooms
 * @property float $price_per_night
 * @property bool $availability
 * @property float $average_rating
 * @property int $total_reviews
 * @property int $view_count
 * @property string|null $image_url
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class BNB extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'bnbs';

    /**
     * The attributes that are mass assignable.
     * 
     * Following security best practices by explicitly defining
     * which fields can be mass assigned to prevent mass assignment vulnerabilities.
     */
    protected $fillable = [
        'name',
        'location',
        'latitude',
        'longitude',
        'amenities',
        'description',
        'max_guests',
        'bedrooms',
        'bathrooms',
        'price_per_night',
        'availability',
        'image_url',
    ];

    /**
     * The attributes that should be cast to native types.
     * 
     * Ensures data integrity and proper type handling.
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'amenities' => 'array',
        'max_guests' => 'integer',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'price_per_night' => 'decimal:2',
        'availability' => 'boolean',
        'average_rating' => 'decimal:2',
        'total_reviews' => 'integer',
        'view_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * 
     * Protects sensitive information when the model is serialized to JSON.
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * The model's default values for attributes.
     */
    protected $attributes = [
        'availability' => true,
        'max_guests' => 1,
        'bedrooms' => 1,
        'bathrooms' => 1,
        'average_rating' => 0,
        'total_reviews' => 0,
        'view_count' => 0,
    ];

    /**
     * Define relationship with reviews.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Define relationship with availability calendar.
     */
    public function availabilityCalendar()
    {
        return $this->hasMany(Availability::class);
    }

    /**
     * Define relationship with analytics events.
     */
    public function analytics()
    {
        return $this->morphMany(Analytics::class, 'trackable');
    }

    /**
     * Scope a query to only include available BNBs.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('availability', true);
    }

    /**
     * Scope a query to only include unavailable BNBs.
     */
    public function scopeUnavailable(Builder $query): Builder
    {
        return $query->where('availability', false);
    }

    /**
     * Scope a query to filter by price range.
     */
    public function scopePriceRange(Builder $query, float $min, float $max): Builder
    {
        return $query->whereBetween('price_per_night', [$min, $max]);
    }

    /**
     * Scope a query to search by location.
     */
    public function scopeByLocation(Builder $query, string $location): Builder
    {
        return $query->where('location', 'LIKE', "%{$location}%");
    }

    /**
     * Scope a query to filter by amenities.
     */
    public function scopeWithAmenities(Builder $query, array $amenities): Builder
    {
        foreach ($amenities as $amenity) {
            $query->whereJsonContains('amenities', $amenity);
        }
        return $query;
    }

    /**
     * Scope a query to filter by guest capacity.
     */
    public function scopeForGuests(Builder $query, int $guestCount): Builder
    {
        return $query->where('max_guests', '>=', $guestCount);
    }

    /**
     * Scope a query to filter by minimum rating.
     */
    public function scopeMinRating(Builder $query, float $rating): Builder
    {
        return $query->where('average_rating', '>=', $rating);
    }

    /**
     * Scope a query to find nearby BNBs using bounding box (SQLite compatible).
     */
    public function scopeNearby(Builder $query, float $latitude, float $longitude, float $radiusKm = 10): Builder
    {
        // Calculate bounding box coordinates
        // 1 degree of latitude = ~111 km
        // 1 degree of longitude = ~111 km * cos(latitude)
        $latDelta = $radiusKm / 111;
        $lngDelta = $radiusKm / (111 * cos(deg2rad($latitude)));
        
        $minLat = $latitude - $latDelta;
        $maxLat = $latitude + $latDelta;
        $minLng = $longitude - $lngDelta;
        $maxLng = $longitude + $lngDelta;
        
        return $query
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [$minLat, $maxLat])
            ->whereBetween('longitude', [$minLng, $maxLng])
            ->orderByRaw('
                ABS(latitude - ?) + ABS(longitude - ?)
            ', [$latitude, $longitude]);
    }

    /**
     * Scope a query to filter by date availability.
     */
    public function scopeAvailableOnDates(Builder $query, string $checkIn, string $checkOut): Builder
    {
        return $query->whereDoesntHave('availabilityCalendar', function ($subQuery) use ($checkIn, $checkOut) {
            $subQuery->whereBetween('date', [$checkIn, $checkOut])
                     ->where('is_available', false);
        });
    }

    /**
     * Get the formatted price per night.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price_per_night, 2);
    }

    /**
     * Check if the BNB is available.
     */
    public function isAvailable(): bool
    {
        return $this->availability === true;
    }

    /**
     * Mark the BNB as available.
     */
    public function markAsAvailable(): bool
    {
        return $this->update(['availability' => true]);
    }

    /**
     * Mark the BNB as unavailable.
     */
    public function markAsUnavailable(): bool
    {
        return $this->update(['availability' => false]);
    }

    /**
     * Increment view count.
     */
    public function incrementViewCount(): bool
    {
        return $this->increment('view_count');
    }

    /**
     * Update average rating and review count.
     */
    public function updateRating(): void
    {
        $this->update([
            'average_rating' => $this->reviews()->avg('rating') ?? 0,
            'total_reviews' => $this->reviews()->count(),
        ]);
    }

    /**
     * Get distance to a coordinate point.
     */
    public function getDistanceToPoint(float $latitude, float $longitude): float
    {
        if (!$this->latitude || !$this->longitude) {
            return 0;
        }

        $earthRadius = 6371; // km

        $latDelta = deg2rad($latitude - $this->latitude);
        $lonDelta = deg2rad($longitude - $this->longitude);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if BNB is available on specific dates.
     */
    public function isAvailableOnDates(string $checkIn, string $checkOut): bool
    {
        return !$this->availabilityCalendar()
                    ->whereBetween('date', [$checkIn, $checkOut])
                    ->where('is_available', false)
                    ->exists();
    }

    /**
     * Boot the model.
     * 
     * Sets up model events for logging and audit trail.
     */
    protected static function boot()
    {
        parent::boot();

        // Log model events for audit trail
        static::created(function ($model) {
            logger()->info("BNB created: {$model->name} (ID: {$model->id})");
        });

        static::updated(function ($model) {
            logger()->info("BNB updated: {$model->name} (ID: {$model->id})");
        });

        static::deleted(function ($model) {
            logger()->info("BNB deleted: {$model->name} (ID: {$model->id})");
        });
    }
}
