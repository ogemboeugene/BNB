<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bnb_id',
        'rating',
        'comment',
        'feedback_categories',
        'is_verified',
        'stay_date',
    ];

    protected $casts = [
        'rating' => 'integer',
        'feedback_categories' => 'array',
        'is_verified' => 'boolean',
        'stay_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who wrote this review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the BNB this review is for.
     */
    public function bnb()
    {
        return $this->belongsTo(BNB::class);
    }

    /**
     * Scope to get verified reviews only.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to filter by rating.
     */
    public function scopeByRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope to filter by minimum rating.
     */
    public function scopeMinRating($query, int $minRating)
    {
        return $query->where('rating', '>=', $minRating);
    }

        /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Update BNB rating when review is created/updated/deleted
        // Note: Rating updates can be triggered manually or via a job queue for better performance
        /*
        static::created(function ($review) {
            \Illuminate\Support\Facades\DB::afterCommit(function () use ($review) {
                $review->bnb()->first()?->updateRating();
            });
        });

        static::updated(function ($review) {
            \Illuminate\Support\Facades\DB::afterCommit(function () use ($review) {
                $review->bnb()->first()?->updateRating();
            });
        });

        static::deleted(function ($review) {
            \Illuminate\Support\Facades\DB::afterCommit(function () use ($review) {
                $review->bnb()->first()?->updateRating();
            });
        });
        */
    }
}
