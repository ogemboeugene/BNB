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
use App\Services\ImageUploadService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="BNB",
 *     type="object",
 *     title="BNB",
 *     description="BNB (Bed and Breakfast) property model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Cozy Downtown Loft"),
 *     @OA\Property(property="description", type="string", example="Beautiful apartment in the heart of the city..."),
 *     @OA\Property(property="location", type="string", example="New York, NY"),
 *     @OA\Property(property="latitude", type="number", format="float", example=40.7589),
 *     @OA\Property(property="longitude", type="number", format="float", example=-73.9851),
 *     @OA\Property(property="price_per_night", type="number", format="float", example=150.00),
 *     @OA\Property(property="max_guests", type="integer", example=4),
 *     @OA\Property(property="bedrooms", type="integer", example=2),
 *     @OA\Property(property="bathrooms", type="integer", example=1),
 *     @OA\Property(property="amenities", type="array", @OA\Items(type="string", example="WiFi")),
 *     @OA\Property(property="house_rules", type="string", example="No smoking, No pets"),
 *     @OA\Property(property="availability", type="boolean", example=true),
 *     @OA\Property(property="featured", type="boolean", example=false),
 *     @OA\Property(property="average_rating", type="number", format="float", example=4.5),
 *     @OA\Property(property="total_reviews", type="integer", example=23),
 *     @OA\Property(property="view_count", type="integer", example=450),
 *     @OA\Property(property="images", type="array", @OA\Items(type="string", example="https://example.com/image.jpg")),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-08T10:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-08T10:00:00.000000Z"),
 *     @OA\Property(property="user", ref="#/components/schemas/User")
 * )
 */

/**
 * Class BNBController
 * 
 * REST API controller for managing BNB (Bed and Breakfast) resources.
 * This controller follows RESTful conventions and implements proper
 * error handling, logging, and response formatting.
 * 
 * @package App\Http\Controllers\Api\V1
 */
#[OA\Tag(
    name: 'BNBs',
    description: 'BNB Management endpoints'
)]
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

    /**
     * Get the image upload service instance (lazy-loaded)
     * 
     * @return ImageUploadService
     */
    private function getImageUploadService(): ImageUploadService
    {
        return app(ImageUploadService::class);
    }

    #[OA\Get(
        path: '/bnbs',
        summary: 'List all BNBs',
        description: 'Retrieve a paginated list of BNBs with optional filtering and sorting',
        tags: ['BNBs'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'Page number for pagination',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, default: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                description: 'Number of items per page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 15)
            ),
            new OA\Parameter(
                name: 'availability',
                description: 'Filter by availability status',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'boolean')
            ),
            new OA\Parameter(
                name: 'location',
                description: 'Filter by location (partial match)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', maxLength: 255)
            ),
            new OA\Parameter(
                name: 'min_price',
                description: 'Minimum price per night',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'number', minimum: 0)
            ),
            new OA\Parameter(
                name: 'max_price',
                description: 'Maximum price per night',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'number', minimum: 0)
            ),
            new OA\Parameter(
                name: 'name',
                description: 'Filter by name (partial match)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', maxLength: 100)
            ),
            new OA\Parameter(
                name: 'sort_by',
                description: 'Field to sort by',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['id', 'name', 'location', 'price_per_night', 'availability', 'created_at', 'updated_at'],
                    default: 'created_at'
                )
            ),
            new OA\Parameter(
                name: 'sort_direction',
                description: 'Sort direction',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'desc')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of BNBs with pagination metadata',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/BNB')),
                        new OA\Property(
                            property: 'meta',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer'),
                                new OA\Property(property: 'last_page', type: 'integer'),
                                new OA\Property(property: 'per_page', type: 'integer'),
                                new OA\Property(property: 'total', type: 'integer')
                            ],
                            type: 'object'
                        )
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))
        ]
    )]
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
            'sort_by' => 'nullable|string|in:id,name,location,price_per_night,availability,created_at,updated_at,distance,average_rating',
            'sort_direction' => 'nullable|string|in:asc,desc',
            // New geolocation and advanced filters
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.1|max:100', // km
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:50',
            'min_guests' => 'nullable|integer|min:1|max:20',
            'bedrooms' => 'nullable|integer|min:1|max:10',
            'bathrooms' => 'nullable|integer|min:1|max:10',
            'min_rating' => 'nullable|numeric|between:0,5',
            'check_in' => 'nullable|date|after_or_equal:today',
            'check_out' => 'nullable|date|after:check_in',
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortDirection = $validated['sort_direction'] ?? 'desc';

        // Build query with advanced filtering
        $query = \App\Models\BNB::query();

        // Basic filters
        if (isset($validated['availability'])) {
            $availability = filter_var($validated['availability'], FILTER_VALIDATE_BOOLEAN);
            $query->where('availability', $availability);
        }

        if (!empty($validated['location'])) {
            $query->byLocation($validated['location']);
        }

        if (!empty($validated['name'])) {
            $query->where('name', 'LIKE', "%{$validated['name']}%");
        }

        // Price range filtering
        if (isset($validated['min_price']) && isset($validated['max_price'])) {
            if ($validated['min_price'] > $validated['max_price']) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'min_price' => ['Minimum price cannot be greater than maximum price']
                    ]
                ], 422);
            }
            $query->priceRange($validated['min_price'], $validated['max_price']);
        } elseif (isset($validated['min_price'])) {
            $query->where('price_per_night', '>=', $validated['min_price']);
        } elseif (isset($validated['max_price'])) {
            $query->where('price_per_night', '<=', $validated['max_price']);
        }

        // Guest capacity filtering
        if (!empty($validated['min_guests'])) {
            $query->forGuests($validated['min_guests']);
        }

        // Room filtering
        if (!empty($validated['bedrooms'])) {
            $query->where('bedrooms', '>=', $validated['bedrooms']);
        }

        if (!empty($validated['bathrooms'])) {
            $query->where('bathrooms', '>=', $validated['bathrooms']);
        }

        // Rating filtering
        if (!empty($validated['min_rating'])) {
            $query->minRating($validated['min_rating']);
        }

        // Amenities filtering
        if (!empty($validated['amenities'])) {
            $query->withAmenities($validated['amenities']);
        }

        // Date availability filtering
        if (!empty($validated['check_in']) && !empty($validated['check_out'])) {
            $query->availableOnDates($validated['check_in'], $validated['check_out']);
        }

        // Geolocation proximity filtering
        if (!empty($validated['latitude']) && !empty($validated['longitude'])) {
            $radius = $validated['radius'] ?? 10; // Default 10km radius
            $query->nearby($validated['latitude'], $validated['longitude'], $radius);
            
            // If sorting by distance, it's already handled by the nearby scope
            if ($sortBy === 'distance') {
                $sortBy = null; // Prevent duplicate ordering
            }
        }

        // Apply sorting
        if ($sortBy) {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Track view analytics if this is a search
        if ($request->hasAny(['latitude', 'longitude', 'location', 'amenities', 'min_price', 'max_price'])) {
            // Log search analytics
            Log::info('BNB Search performed', [
                'filters' => $validated,
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        $bnbs = $query->paginate($perPage);

        // Transform to resource collection with additional metadata
        $response = new BNBCollection($bnbs);
        
        // Add search metadata
        $searchMeta = [
            'filters_applied' => count(array_filter($validated, function($value) {
                return $value !== null && $value !== '';
            })),
            'total_results' => $bnbs->total(),
        ];

        if (!empty($validated['latitude']) && !empty($validated['longitude'])) {
            $searchMeta['search_center'] = [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'radius_km' => $validated['radius'] ?? 10,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'BNBs retrieved successfully',
            'data' => $response->collection,
            'meta' => array_merge($bnbs->toArray(), $searchMeta),
        ]);
    }

    #[OA\Post(
        path: '/bnbs',
        summary: 'Create a new BNB',
        description: 'Create a new BNB listing with optional image upload (requires authentication)',
        security: [['sanctum' => []]],
        tags: ['BNBs'],
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        required: ['name', 'location', 'price_per_night'],
                        properties: [
                            new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Cozy Downtown Apartment'),
                            new OA\Property(property: 'location', type: 'string', maxLength: 255, example: 'New York, NY'),
                            new OA\Property(property: 'price_per_night', type: 'number', minimum: 0, example: 150.00),
                            new OA\Property(property: 'availability', type: 'boolean', default: true, example: true)
                        ]
                    )
                ),
                new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(
                        required: ['name', 'location', 'price_per_night'],
                        properties: [
                            new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Cozy Downtown Apartment'),
                            new OA\Property(property: 'location', type: 'string', maxLength: 255, example: 'New York, NY'),
                            new OA\Property(property: 'price_per_night', type: 'number', minimum: 0, example: 150.00),
                            new OA\Property(property: 'availability', type: 'boolean', default: true, example: true),
                            new OA\Property(property: 'image', type: 'string', format: 'binary', description: 'BNB image file (JPG, PNG, WEBP, GIF - max 10MB)')
                        ]
                    )
                )
            ]
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'BNB created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/BNB')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function store(StoreBNBRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            // Handle image upload if provided
            if ($request->hasFile('image')) {
                $imageUploadService = $this->getImageUploadService();
                $imageResult = $imageUploadService->uploadImage(
                    $request->file('image'),
                    $imageUploadService->generatePublicId('bnb')
                );
                
                if ($imageResult['success']) {
                    $validated['image_url'] = $imageResult['url'];
                } else {
                    throw new \App\Exceptions\ApiException(
                        $imageResult['error'],
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        'IMAGE_UPLOAD_ERROR'
                    );
                }
            }
            
            $bnb = $this->bnbRepository->create($validated);

            return response()->json([
                'data' => [
                    'id' => $bnb->id,
                    'name' => $bnb->name,
                    'location' => $bnb->location,
                    'price_per_night' => $bnb->price_per_night,
                    'availability' => $bnb->availability,
                    'image_url' => $bnb->image_url,
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

    #[OA\Get(
        path: '/bnbs/{id}',
        summary: 'Get a specific BNB',
        description: 'Retrieve details of a specific BNB by ID',
        tags: ['BNBs'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'BNB ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', minimum: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'BNB details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/BNB')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'BNB not found', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 422, description: 'Invalid ID format', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))
        ]
    )]
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
                    'image_url' => $bnb->image_url,
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

    #[OA\Put(
        path: '/bnbs/{id}',
        summary: 'Update a BNB',
        description: 'Update an existing BNB listing (requires authentication)',
        security: [['sanctum' => []]],
        tags: ['BNBs'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'BNB ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', minimum: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Updated Apartment Name'),
                            new OA\Property(property: 'location', type: 'string', maxLength: 255, example: 'Los Angeles, CA'),
                            new OA\Property(property: 'price_per_night', type: 'number', minimum: 0, example: 175.50),
                            new OA\Property(property: 'availability', type: 'boolean', example: false),
                            new OA\Property(property: 'image_url', type: 'string', format: 'url', example: 'https://res.cloudinary.com/demo/image/upload/sample.jpg')
                        ]
                    )
                ),
                new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Updated Apartment Name'),
                            new OA\Property(property: 'location', type: 'string', maxLength: 255, example: 'Los Angeles, CA'),
                            new OA\Property(property: 'price_per_night', type: 'number', minimum: 0, example: 175.50),
                            new OA\Property(property: 'availability', type: 'boolean', example: false),
                            new OA\Property(
                                property: 'image',
                                type: 'string',
                                format: 'binary',
                                description: 'New image file for the BNB (jpeg, jpg, png, webp, gif up to 10MB)'
                            )
                        ]
                    )
                )
            ]
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'BNB updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/BNB')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'BNB not found', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))
        ]
    )]
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
            
            // Handle image upload if provided
            if ($request->hasFile('image')) {
                // Get the current BNB to extract old image public ID
                $currentBnb = $this->bnbRepository->findById((int) $id);
                $oldPublicId = null;
                
                if ($currentBnb && $currentBnb->image_url) {
                    $oldPublicId = $this->getImageUploadService()->extractPublicIdFromUrl($currentBnb->image_url);
                }
                
                $imageResult = $this->getImageUploadService()->updateImage(
                    $request->file('image'),
                    $oldPublicId,
                    $this->getImageUploadService()->generatePublicId('bnb')
                );
                
                if ($imageResult['success']) {
                    $validated['image_url'] = $imageResult['url'];
                } else {
                    throw new \App\Exceptions\ApiException(
                        $imageResult['error'],
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        'IMAGE_UPLOAD_ERROR'
                    );
                }
            }
            
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
                    'image_url' => $bnb->image_url,
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
    #[OA\Delete(
        path: '/bnbs/{id}',
        summary: 'Delete a BNB',
        description: 'Delete a BNB listing (requires admin privileges)',
        security: [['sanctum' => []]],
        tags: ['BNBs'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'BNB ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', minimum: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'BNB deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'BNB deleted successfully')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 403, description: 'Forbidden - Admin privileges required', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'BNB not found', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 422, description: 'Invalid ID format', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))
        ]
    )]
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

    /**
     * Search for nearby BNBs based on coordinates.
     */
    public function searchNearby(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.1|max:100',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $radius = $validated['radius'] ?? 10; // Default 10km
        $limit = $validated['limit'] ?? 20;

        $bnbs = \App\Models\BNB::query()
            ->available()
            ->nearby($validated['latitude'], $validated['longitude'], $radius)
            ->take($limit)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Nearby BNBs retrieved successfully',
            'data' => BNBResource::collection($bnbs),
            'meta' => [
                'search_center' => [
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                ],
                'radius_km' => $radius,
                'total_results' => $bnbs->count(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/bnbs/search/map",
     *     operationId="getBnbsForMap",
     *     tags={"BNBs"},
     *     summary="Get BNBs for map display",
     *     description="Retrieve BNBs formatted for map display with coordinates and optimized for zoom levels.",
     *     @OA\Parameter(
     *         name="bounds[north]",
     *         in="query",
     *         description="Northern boundary latitude",
     *         required=false,
     *         @OA\Schema(type="number", format="float", minimum=-90, maximum=90, example=40.7828)
     *     ),
     *     @OA\Parameter(
     *         name="bounds[south]",
     *         in="query",
     *         description="Southern boundary latitude",
     *         required=false,
     *         @OA\Schema(type="number", format="float", minimum=-90, maximum=90, example=40.7489)
     *     ),
     *     @OA\Parameter(
     *         name="bounds[east]",
     *         in="query",
     *         description="Eastern boundary longitude",
     *         required=false,
     *         @OA\Schema(type="number", format="float", minimum=-180, maximum=180, example=-73.9441)
     *     ),
     *     @OA\Parameter(
     *         name="bounds[west]",
     *         in="query",
     *         description="Western boundary longitude",
     *         required=false,
     *         @OA\Schema(type="number", format="float", minimum=-180, maximum=180, example=-74.0059)
     *     ),
     *     @OA\Parameter(
     *         name="zoom_level",
     *         in="query",
     *         description="Map zoom level (1-20) for result optimization",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=20, example=12)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="BNBs for map display retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Map data retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Cozy Downtown Loft"),
     *                 @OA\Property(property="latitude", type="number", format="float", example=40.7589),
     *                 @OA\Property(property="longitude", type="number", format="float", example=-73.9851),
     *                 @OA\Property(property="price_per_night", type="number", format="float", example=150.00),
     *                 @OA\Property(property="average_rating", type="number", format="float", example=4.5),
     *                 @OA\Property(property="total_reviews", type="integer", example=23),
     *                 @OA\Property(property="image_url", type="string", example="https://example.com/image.jpg")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     )
     * )
     *
     * Get BNBs formatted for map display.
     */
    public function getForMap(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bounds' => 'nullable|array',
            'bounds.north' => 'required_with:bounds|numeric|between:-90,90',
            'bounds.south' => 'required_with:bounds|numeric|between:-90,90',
            'bounds.east' => 'required_with:bounds|numeric|between:-180,180',
            'bounds.west' => 'required_with:bounds|numeric|between:-180,180',
            'zoom_level' => 'nullable|integer|min:1|max:20',
        ]);

        $query = \App\Models\BNB::query()
            ->available()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        // Filter by map bounds if provided
        if (!empty($validated['bounds'])) {
            $bounds = $validated['bounds'];
            $query->whereBetween('latitude', [$bounds['south'], $bounds['north']])
                  ->whereBetween('longitude', [$bounds['west'], $bounds['east']]);
        }

        // Limit results based on zoom level to prevent overcrowding
        $zoomLevel = $validated['zoom_level'] ?? 10;
        $limit = $zoomLevel < 10 ? 50 : ($zoomLevel < 15 ? 100 : 200);
        
        $bnbs = $query->take($limit)->get();

        $mapData = $bnbs->map(function ($bnb) {
            return [
                'id' => $bnb->id,
                'name' => $bnb->name,
                'latitude' => $bnb->latitude,
                'longitude' => $bnb->longitude,
                'price_per_night' => $bnb->price_per_night,
                'average_rating' => $bnb->average_rating,
                'image_url' => $bnb->image_url,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Map data retrieved successfully',
            'data' => $mapData,
            'meta' => [
                'total_results' => $mapData->count(),
                'zoom_level' => $zoomLevel,
                'bounds' => $validated['bounds'] ?? null,
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/dashboard/analytics",
     *     operationId="getUserAnalytics",
     *     tags={"Analytics"},
     *     summary="Get user dashboard analytics",
     *     description="Retrieve analytics data for the authenticated user including BNB statistics, views, and ratings.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Analytics data retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Analytics retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_bnbs", type="integer", example=5),
     *                 @OA\Property(property="active_bnbs", type="integer", example=4),
     *                 @OA\Property(property="total_views", type="integer", example=1250),
     *                 @OA\Property(property="average_rating", type="number", format="float", example=4.3),
     *                 @OA\Property(property="total_reviews", type="integer", example=87)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     *
     * Get analytics data for user dashboard.
     */
    public function getAnalytics(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        // Get user's BNBs if they're a host
        $userBnbs = \App\Models\BNB::query()->where('user_id', $user->id)->get();
        
        $analytics = [
            'total_bnbs' => $userBnbs->count(),
            'active_bnbs' => $userBnbs->where('availability', true)->count(),
            'total_views' => $userBnbs->sum('view_count'),
            'average_rating' => $userBnbs->avg('average_rating'),
            'total_reviews' => $userBnbs->sum('total_reviews'),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Analytics retrieved successfully',
            'data' => $analytics,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/bnbs/{id}/analytics",
     *     operationId="getBnbAnalytics",
     *     tags={"Analytics"},
     *     summary="Get analytics for a specific BNB",
     *     description="Retrieve detailed analytics data for a specific BNB property including views, bookings, ratings, and revenue metrics.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="BNB ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Analytics period",
     *         required=false,
     *         @OA\Schema(type="string", enum={"7days", "30days", "90days", "1year"}, example="30days")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="BNB analytics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="BNB analytics retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="views", type="object",
     *                     @OA\Property(property="total", type="integer", example=450),
     *                     @OA\Property(property="period", type="integer", example=125),
     *                     @OA\Property(property="daily_average", type="number", format="float", example=4.2)
     *                 ),
     *                 @OA\Property(property="bookings", type="object",
     *                     @OA\Property(property="total", type="integer", example=23),
     *                     @OA\Property(property="period", type="integer", example=8),
     *                     @OA\Property(property="conversion_rate", type="number", format="float", example=6.4)
     *                 ),
     *                 @OA\Property(property="ratings", type="object",
     *                     @OA\Property(property="average", type="number", format="float", example=4.7),
     *                     @OA\Property(property="total_reviews", type="integer", example=19),
     *                     @OA\Property(property="rating_distribution", type="object",
     *                         @OA\Property(property="5", type="integer", example=12),
     *                         @OA\Property(property="4", type="integer", example=5),
     *                         @OA\Property(property="3", type="integer", example=2),
     *                         @OA\Property(property="2", type="integer", example=0),
     *                         @OA\Property(property="1", type="integer", example=0)
     *                     )
     *                 ),
     *                 @OA\Property(property="revenue", type="object",
     *                     @OA\Property(property="total", type="number", format="float", example=3450.00),
     *                     @OA\Property(property="period", type="number", format="float", example=1200.00),
     *                     @OA\Property(property="average_per_booking", type="number", format="float", example=150.00)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="BNB not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="BNB not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied - User doesn't own this BNB",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Access denied")
     *         )
     *     )
     * )
     *
     * Get analytics for a specific BNB.
     */
    public function getBnbAnalytics(Request $request, $id): JsonResponse
    {
        $bnb = $this->bnbRepository->findById((int) $id);
        
        if (!$bnb) {
            throw new BNBNotFoundException($id);
        }

        // Check if user owns this BNB or is admin
        if ($bnb->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view analytics for this BNB',
            ], 403);
        }

        $analytics = [
            'total_views' => $bnb->view_count,
            'total_reviews' => $bnb->total_reviews,
            'average_rating' => $bnb->average_rating,
            'last_30_days_views' => 0, // Placeholder - would need analytics table implementation
            'conversion_rate' => 0, // Placeholder - would need booking data
        ];

        return response()->json([
            'success' => true,
            'message' => 'BNB analytics retrieved successfully',
            'data' => $analytics,
        ]);
    }

    /**
     * Get featured BNBs.
     * 
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/api/v1/bnbs/featured',
        summary: 'Get featured BNBs',
        description: 'Retrieve a list of featured BNB properties that are highlighted on the platform',
        tags: ['BNBs'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Featured BNBs retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        'success' => new OA\Property(property: 'success', type: 'boolean', example: true),
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Featured BNBs retrieved successfully'),
                        'data' => new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/BNB')
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'No featured BNBs found',
                content: new OA\JsonContent(
                    properties: [
                        'success' => new OA\Property(property: 'success', type: 'boolean', example: false),
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'No featured BNBs available at the moment')
                    ]
                )
            )
        ]
    )]
    public function featured(): JsonResponse
    {
        try {
            $featuredBnbs = $this->bnbRepository->getFeatured();
            
            if ($featuredBnbs->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No featured BNBs available at the moment'
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Featured BNBs retrieved successfully',
                'data' => BNBResource::collection($featuredBnbs)
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving featured BNBs: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving featured BNBs'
            ], 500);
        }
    }
}
