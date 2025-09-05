<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\BNBNotFoundException;
use App\Exceptions\InvalidBNBDataException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBNBRequest;
use App\Http\Requests\UpdateBNBRequest;
use App\Http\Resources\BNBResource;
use App\Http\Resources\BNBCollection;
use App\Repositories\Contracts\BNBRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BNBController
 * 
 * REST API controller for managing BNB (Bed and Breakfast) resources.
 * This controller follows RESTful conventions and implements proper
 * error handling, logging, and response formatting.
 * 
 * @package App\Http\Controllers\Api\V1
 */
class BNBController extends Controller
{
    use ApiResponseTrait;
    /**
     * The BNB repository instance.
     */
    protected BNBRepositoryInterface $bnbRepository;

    /**
     * BNBController constructor.
     * 
     * @param BNBRepositoryInterface $bnbRepository The BNB repository instance
     */
    public function __construct(BNBRepositoryInterface $bnbRepository)
    {
        $this->bnbRepository = $bnbRepository;
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            'availability' => 'nullable|in:true,false,1,0',
            'location' => 'nullable|string|max:255',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'name' => 'nullable|string|max:100',
            'sort_by' => 'nullable|string|in:id,name,location,price_per_night,availability,created_at,updated_at',
            'sort_direction' => 'nullable|string|in:asc,desc',
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortDirection = $validated['sort_direction'] ?? 'desc';

        // Build filters array
        $filters = array_filter([
            'availability' => isset($validated['availability']) ? filter_var($validated['availability'], FILTER_VALIDATE_BOOLEAN) : null,
            'location' => $validated['location'] ?? null,
            'min_price' => $validated['min_price'] ?? null,
            'max_price' => $validated['max_price'] ?? null,
            'name' => $validated['name'] ?? null,
        ], function ($value) {
            return $value !== null;
        });

        // Validate price range
        if (isset($filters['min_price']) && isset($filters['max_price']) && 
            $filters['min_price'] > $filters['max_price']) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'min_price' => ['Minimum price cannot be greater than maximum price']
                ]
            ], 422);
        }
        
        $bnbs = $this->bnbRepository->getWithFilters($filters, $sortBy, $sortDirection, $perPage);

        return response()->json([
            'data' => $bnbs->items(),
            'meta' => [
                'current_page' => $bnbs->currentPage(),
                'last_page' => $bnbs->lastPage(),
                'per_page' => $bnbs->perPage(),
                'total' => $bnbs->total(),
            ]
        ]);
    }

    public function store(StoreBNBRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $bnb = $this->bnbRepository->create($validated);

            return response()->json([
                'data' => [
                    'id' => $bnb->id,
                    'name' => $bnb->name,
                    'location' => $bnb->location,
                    'price_per_night' => $bnb->price_per_night,
                    'availability' => $bnb->availability,
                    'created_at' => $bnb->created_at,
                    'updated_at' => $bnb->updated_at,
                ]
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            throw new \App\Exceptions\ApiException(
                'An error occurred while creating the BNB',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'BNB_CREATE_ERROR'
            );
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new InvalidBNBDataException(
                    ['id' => ['The provided ID is not valid']],
                    'Invalid BNB ID format'
                );
            }

            $bnb = $this->bnbRepository->findById((int) $id);

            if (!$bnb) {
                throw new BNBNotFoundException($id);
            }

            return response()->json([
                'data' => [
                    'id' => $bnb->id,
                    'name' => $bnb->name,
                    'location' => $bnb->location,
                    'price_per_night' => $bnb->price_per_night,
                    'availability' => $bnb->availability,
                    'created_at' => $bnb->created_at,
                    'updated_at' => $bnb->updated_at,
                ]
            ]);

        } catch (BNBNotFoundException|InvalidBNBDataException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \App\Exceptions\ApiException(
                'An error occurred while retrieving the BNB',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'BNB_RETRIEVAL_ERROR'
            );
        }
    }

    public function update(UpdateBNBRequest $request, string $id): JsonResponse
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new InvalidBNBDataException(
                    ['id' => ['The provided ID is not valid']],
                    'Invalid BNB ID format'
                );
            }

            $validated = $request->validated();
            $bnb = $this->bnbRepository->update((int) $id, $validated);

            if (!$bnb) {
                throw new BNBNotFoundException($id);
            }

            return response()->json([
                'data' => [
                    'id' => $bnb->id,
                    'name' => $bnb->name,
                    'location' => $bnb->location,
                    'price_per_night' => $bnb->price_per_night,
                    'availability' => $bnb->availability,
                    'created_at' => $bnb->created_at,
                    'updated_at' => $bnb->updated_at,
                ]
            ]);

        } catch (BNBNotFoundException|InvalidBNBDataException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \App\Exceptions\ApiException(
                'An error occurred while updating the BNB',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'BNB_UPDATE_ERROR'
            );
        }
    }

    /**
     * Remove the specified BNB from storage (soft delete).
     * 
     * @param string $id The BNB ID
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            // Validate ID format
            if (!is_numeric($id) || $id <= 0) {
                throw new InvalidBNBDataException(
                    ['id' => ['The provided ID is not valid']],
                    'Invalid BNB ID format'
                );
            }

            $result = $this->bnbRepository->delete((int) $id);

            if (!$result) {
                throw new BNBNotFoundException($id);
            }

            Log::info('BNB deleted successfully', [
                'id' => $id,
                'user_id' => auth()->id()
            ]);

            return $this->successResponse(null, 'BNB deleted successfully');

        } catch (BNBNotFoundException|InvalidBNBDataException $e) {
            // Re-throw API exceptions to be handled by global handler
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to delete BNB', [
                'id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            throw new \App\Exceptions\ApiException(
                'An error occurred while deleting the BNB',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'BNB_DELETE_ERROR'
            );
        }
    }

    /**
     * Update the availability status of a BNB.
     * 
     * @param Request $request The HTTP request with availability data
     * @param string $id The BNB ID
     * @return JsonResponse
     */
    public function updateAvailability(Request $request, string $id): JsonResponse
    {
        try {
            // Validate ID format
            if (!is_numeric($id) || $id <= 0) {
                throw new InvalidBNBDataException(
                    ['id' => ['The provided ID is not valid']],
                    'Invalid BNB ID format'
                );
            }

            $validated = $request->validate([
                'availability' => 'required|boolean',
            ]);

            $result = $this->bnbRepository->updateAvailability((int) $id, $validated['availability']);

            if (!$result) {
                throw new BNBNotFoundException($id);
            }

            Log::info('BNB availability updated successfully', [
                'id' => $id,
                'availability' => $validated['availability'],
                'user_id' => auth()->id()
            ]);

            return $this->successResponse(null, 'BNB availability updated successfully');

        } catch (BNBNotFoundException|InvalidBNBDataException $e) {
            // Re-throw API exceptions to be handled by global handler
            throw $e;
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new InvalidBNBDataException(
                $e->errors(),
                'Validation failed'
            );
        } catch (\Exception $e) {
            Log::error('Failed to update BNB availability', [
                'id' => $id,
                'data' => $request->all(),
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            throw new \App\Exceptions\ApiException(
                'An error occurred while updating BNB availability',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'BNB_AVAILABILITY_UPDATE_ERROR'
            );
        }
    }
}
