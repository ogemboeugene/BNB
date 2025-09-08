<?php

namespace App\Repositories\Contracts;

use App\Models\BNB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface BNBRepositoryInterface
 * 
 * Defines the contract for BNB data access operations.
 * This interface follows the Repository Pattern and SOLID principles,
 * particularly the Dependency Inversion Principle by depending on
 * abstractions rather than concrete implementations.
 * 
 * @package App\Repositories\Contracts
 */
interface BNBRepositoryInterface
{
    /**
     * Get all BNBs with optional pagination.
     * 
     * @param int|null $perPage Number of items per page (null for no pagination)
     * @param array $filters Optional filters to apply
     * @return LengthAwarePaginator|Collection
     */
    public function getAll(?int $perPage = null, array $filters = []): LengthAwarePaginator|Collection;

    /**
     * Find a BNB by its ID.
     * 
     * @param int $id The BNB ID
     * @return BNB|null
     */
    public function findById(int $id): ?BNB;

    /**
     * Create a new BNB.
     * 
     * @param array $data The BNB data
     * @return BNB
     */
    public function create(array $data): BNB;

    /**
     * Update an existing BNB.
     * 
     * @param int $id The BNB ID
     * @param array $data The updated data
     * @return BNB|null
     */
    public function update(int $id, array $data): ?BNB;

    /**
     * Delete a BNB (soft delete).
     * 
     * @param int $id The BNB ID
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Restore a soft-deleted BNB.
     * 
     * @param int $id The BNB ID
     * @return bool
     */
    public function restore(int $id): bool;

    /**
     * Permanently delete a BNB.
     * 
     * @param int $id The BNB ID
     * @return bool
     */
    public function forceDelete(int $id): bool;

    /**
     * Get available BNBs.
     * 
     * @param int|null $perPage Number of items per page
     * @return LengthAwarePaginator|Collection
     */
    public function getAvailable(?int $perPage = null): LengthAwarePaginator|Collection;

    /**
     * Search BNBs by location.
     * 
     * @param string $location The location to search for
     * @param int|null $perPage Number of items per page
     * @return LengthAwarePaginator|Collection
     */
    public function searchByLocation(string $location, ?int $perPage = null): LengthAwarePaginator|Collection;

    /**
     * Filter BNBs by price range.
     * 
     * @param float $minPrice Minimum price
     * @param float $maxPrice Maximum price
     * @param int|null $perPage Number of items per page
     * @return LengthAwarePaginator|Collection
     */
    public function filterByPriceRange(float $minPrice, float $maxPrice, ?int $perPage = null): LengthAwarePaginator|Collection;

    /**
     * Update BNB availability status.
     * 
     * @param int $id The BNB ID
     * @param bool $availability The availability status
     * @return bool
     */
    public function updateAvailability(int $id, bool $availability): bool;

    /**
     * Get BNBs with optional filters and sorting.
     * 
     * @param array $filters Filters to apply
     * @param string $sortBy Column to sort by
     * @param string $sortDirection Sort direction (asc|desc)
     * @param int|null $perPage Number of items per page
     * @return LengthAwarePaginator
     */
    public function getWithFilters(
        array $filters = [],
        string $sortBy = 'created_at',
        string $sortDirection = 'desc',
        ?int $perPage = null
    ): LengthAwarePaginator;

    /**
     * Check if a BNB exists by ID.
     * 
     * @param int $id The BNB ID
     * @return bool
     */
    public function exists(int $id): bool;

    /**
     * Get the total count of BNBs.
     * 
     * @param array $filters Optional filters to apply
     * @return int
     */
    public function count(array $filters = []): int;

    /**
     * Get featured BNBs.
     * 
     * @return Collection
     */
    public function getFeatured(): Collection;
}