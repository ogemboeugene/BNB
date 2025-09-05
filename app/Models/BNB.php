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
 * @property float $price_per_night
 * @property bool $availability
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
        'price_per_night',
        'availability',
    ];

    /**
     * The attributes that should be cast to native types.
     * 
     * Ensures data integrity and proper type handling.
     */
    protected $casts = [
        'price_per_night' => 'decimal:2',
        'availability' => 'boolean',
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
    ];

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
