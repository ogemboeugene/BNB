<?php

namespace App\Repositories;

use App\Models\BNB;
use App\Repositories\Contracts\BNBRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Class BNBRepository
 * 
 * Concrete implementation of the BNBRepositoryInterface.
 * This class handles all data access operations for BNB entities,
 * following the Repository Pattern and SOLID principles.
 * 
 * Features:
 * - Caching for better performance
 * - Comprehensive logging for audit trails
 * - Query optimization
 * - Error handling
 * - Data validation
 * 
 * @package App\Repositories
 */
class BNBRepository implements BNBRepositoryInterface
{
    /**
     * The BNB model instance.
     */
    protected BNB $model;

    /**
     * Cache key prefix for BNB-related cache entries.
     */
    protected string $cachePrefix = 'bnb_';

    /**
     * Default cache TTL in minutes.
     */
    protected int $cacheTtl = 60;

    /**
     * BNBRepository constructor.
     * 
     * @param BNB $model The BNB model instance
     */
    public function __construct(BNB $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function getAll(?int $perPage = null, array $filters = []): LengthAwarePaginator|Collection
    {
        $cacheKey = $this->cachePrefix . 'all_' . md5(serialize([$perPage, $filters]));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($perPage, $filters) {
            $query = $this->buildFilterQuery($filters);
            
            if ($perPage) {
                return $query->paginate($perPage);
            }

            return $query->get();
        });
    }

    /**
     * {@inheritDoc}
     */
    public function findById(int $id): ?BNB
    {
        $cacheKey = $this->cachePrefix . 'find_' . $id;

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($id) {
            return $this->model->find($id);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): BNB
    {
        try {
            $bnb = $this->model->create($data);
            
            // Clear related cache entries
            $this->clearRelatedCache();
            
            Log::info('BNB created successfully', [
                'id' => $bnb->id,
                'name' => $bnb->name,
                'data' => $data
            ]);

            return $bnb;
        } catch (\Exception $e) {
            Log::error('Failed to create BNB', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): ?BNB
    {
        try {
            $bnb = $this->findById($id);
            
            if (!$bnb) {
                Log::warning('Attempted to update non-existent BNB', ['id' => $id]);
                return null;
            }

            $bnb->update($data);
            
            // Clear related cache entries
            $this->clearRelatedCache();
            Cache::forget($this->cachePrefix . 'find_' . $id);
            
            Log::info('BNB updated successfully', [
                'id' => $id,
                'data' => $data
            ]);

            return $bnb->fresh();
        } catch (\Exception $e) {
            Log::error('Failed to update BNB', [
                'id' => $id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        try {
            $bnb = $this->findById($id);
            
            if (!$bnb) {
                Log::warning('Attempted to delete non-existent BNB', ['id' => $id]);
                return false;
            }

            $result = $bnb->delete();
            
            // Clear related cache entries
            $this->clearRelatedCache();
            Cache::forget($this->cachePrefix . 'find_' . $id);
            
            Log::info('BNB soft deleted successfully', ['id' => $id]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to delete BNB', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function restore(int $id): bool
    {
        try {
            $bnb = $this->model->withTrashed()->find($id);
            
            if (!$bnb) {
                Log::warning('Attempted to restore non-existent BNB', ['id' => $id]);
                return false;
            }

            $result = $bnb->restore();
            
            // Clear related cache entries
            $this->clearRelatedCache();
            
            Log::info('BNB restored successfully', ['id' => $id]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to restore BNB', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function forceDelete(int $id): bool
    {
        try {
            $bnb = $this->model->withTrashed()->find($id);
            
            if (!$bnb) {
                Log::warning('Attempted to force delete non-existent BNB', ['id' => $id]);
                return false;
            }

            $result = $bnb->forceDelete();
            
            // Clear related cache entries
            $this->clearRelatedCache();
            Cache::forget($this->cachePrefix . 'find_' . $id);
            
            Log::warning('BNB permanently deleted', ['id' => $id]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to force delete BNB', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailable(?int $perPage = null): LengthAwarePaginator|Collection
    {
        $cacheKey = $this->cachePrefix . 'available_' . ($perPage ?? 'all');

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($perPage) {
            $query = $this->model->available()->orderBy('name');
            
            if ($perPage) {
                return $query->paginate($perPage);
            }

            return $query->get();
        });
    }

    /**
     * {@inheritDoc}
     */
    public function searchByLocation(string $location, ?int $perPage = null): LengthAwarePaginator|Collection
    {
        $cacheKey = $this->cachePrefix . 'location_' . md5($location . ($perPage ?? 'all'));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($location, $perPage) {
            $query = $this->model->byLocation($location)->orderBy('name');
            
            if ($perPage) {
                return $query->paginate($perPage);
            }

            return $query->get();
        });
    }

    /**
     * {@inheritDoc}
     */
    public function filterByPriceRange(float $minPrice, float $maxPrice, ?int $perPage = null): LengthAwarePaginator|Collection
    {
        $cacheKey = $this->cachePrefix . 'price_range_' . md5("{$minPrice}_{$maxPrice}" . ($perPage ?? 'all'));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($minPrice, $maxPrice, $perPage) {
            $query = $this->model->priceRange($minPrice, $maxPrice)->orderBy('price_per_night');
            
            if ($perPage) {
                return $query->paginate($perPage);
            }

            return $query->get();
        });
    }

    /**
     * {@inheritDoc}
     */
    public function updateAvailability(int $id, bool $availability): bool
    {
        try {
            $bnb = $this->findById($id);
            
            if (!$bnb) {
                Log::warning('Attempted to update availability for non-existent BNB', ['id' => $id]);
                return false;
            }

            $result = $bnb->update(['availability' => $availability]);
            
            // Clear related cache entries
            $this->clearRelatedCache();
            Cache::forget($this->cachePrefix . 'find_' . $id);
            
            Log::info('BNB availability updated', [
                'id' => $id,
                'availability' => $availability
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to update BNB availability', [
                'id' => $id,
                'availability' => $availability,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getWithFilters(
        array $filters = [],
        string $sortBy = 'created_at',
        string $sortDirection = 'desc',
        ?int $perPage = null
    ): LengthAwarePaginator {
        $page = request()->get('page', 1);
        $cacheKey = $this->cachePrefix . 'filtered_' . md5(serialize([$filters, $sortBy, $sortDirection, $perPage, $page]));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($filters, $sortBy, $sortDirection, $perPage) {
            $query = $this->buildFilterQuery($filters)->orderBy($sortBy, $sortDirection);
            
            // Always paginate, use default of 15 if no perPage specified
            $perPage = $perPage ?: 15;
            return $query->paginate($perPage);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function exists(int $id): bool
    {
        $cacheKey = $this->cachePrefix . 'exists_' . $id;

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($id) {
            return $this->model->where('id', $id)->exists();
        });
    }

    /**
     * {@inheritDoc}
     */
    public function count(array $filters = []): int
    {
        $cacheKey = $this->cachePrefix . 'count_' . md5(serialize($filters));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($filters) {
            return $this->buildFilterQuery($filters)->count();
        });
    }

    /**
     * Build a query with applied filters.
     * 
     * @param array $filters The filters to apply
     * @return Builder
     */
    protected function buildFilterQuery(array $filters): Builder
    {
        $query = $this->model->newQuery();

        // Apply availability filter
        if (isset($filters['availability'])) {
            $query->where('availability', $filters['availability']);
        }

        // Apply location filter
        if (isset($filters['location']) && !empty($filters['location'])) {
            $query->byLocation($filters['location']);
        }

        // Apply price range filter
        if (isset($filters['min_price']) && isset($filters['max_price'])) {
            $query->priceRange($filters['min_price'], $filters['max_price']);
        }

        // Apply name search filter
        if (isset($filters['name']) && !empty($filters['name'])) {
            $query->where('name', 'LIKE', "%{$filters['name']}%");
        }

        return $query;
    }

    /**
     * Clear related cache entries.
     * 
     * This method removes cached data that might be affected by
     * data modifications to ensure cache consistency.
     */
    protected function clearRelatedCache(): void
    {
        $patterns = [
            $this->cachePrefix . 'all_*',
            $this->cachePrefix . 'available_*',
            $this->cachePrefix . 'location_*',
            $this->cachePrefix . 'price_range_*',
            $this->cachePrefix . 'filtered_*',
            $this->cachePrefix . 'count_*',
        ];

        foreach ($patterns as $pattern) {
            Cache::flush(); // For simplicity, we flush all cache
            // In production, you might want to implement a more granular cache invalidation strategy
        }
    }

    /**
     * Get featured BNBs.
     * 
     * @return Collection
     */
    public function getFeatured(): Collection
    {
        $cacheKey = $this->cachePrefix . 'featured';
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            Log::info('Fetching featured BNBs from database');
            
            try {
                // Check if featured column exists
                $hasFeatureColumn = \Schema::hasColumn('bnbs', 'featured');
                
                if ($hasFeatureColumn) {
                    return $this->model
                        ->where('featured', true)
                        ->where('availability', true)
                        ->orderBy('average_rating', 'desc')
                        ->orderBy('total_reviews', 'desc')
                        ->limit(10)
                        ->get();
                } else {
                    // Fallback: return top-rated available BNBs
                    Log::warning('Featured column not found, returning top-rated BNBs as featured');
                    return $this->model
                        ->where('availability', true)
                        ->orderBy('average_rating', 'desc')
                        ->orderBy('total_reviews', 'desc')
                        ->limit(10)
                        ->get();
                }
            } catch (\Exception $e) {
                Log::error('Error fetching featured BNBs: ' . $e->getMessage());
                // Return empty collection on error
                return collect();
            }
        });
    }
}