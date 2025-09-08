<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\BNB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="Review",
 *     type="object",
 *     title="Review",
 *     description="Review model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="bnb_id", type="integer", example=1),
 *     @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=5),
 *     @OA\Property(property="comment", type="string", example="Amazing place to stay!"),
 *     @OA\Property(property="is_verified", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-08T10:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-08T10:00:00.000000Z"),
 *     @OA\Property(property="user", type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="John Doe"),
 *         @OA\Property(property="email", type="string", example="john@example.com")
 *     ),
 *     @OA\Property(property="bnb", type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="title", type="string", example="Beautiful Beach House"),
 *         @OA\Property(property="location", type="string", example="Miami, FL")
 *     )
 * )
 */

class ReviewController extends Controller
{
    /**
     * @OA\Get(
     *     path="/bnbs/{id}/reviews",
     *     operationId="getBNBReviews",
     *     tags={"Reviews"},
     *     summary="Get reviews for a specific BNB",
     *     description="Retrieve paginated reviews for a BNB property with user information and BNB details.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="BNB property ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reviews retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Reviews retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Review")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=42),
     *                 @OA\Property(property="bnb_info", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Downtown Apartment"),
     *                     @OA\Property(property="average_rating", type="number", format="float", example=4.2),
     *                     @OA\Property(property="total_reviews", type="integer", example=42)
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
     *     )
     * )
     *
     * Get reviews for a specific BNB.
     */
    public function index(Request $request, $id): JsonResponse
    {
        $bnb = BNB::findOrFail($id);
        
        $reviews = Review::where('bnb_id', $id)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return response()->json([
            'success' => true,
            'message' => 'Reviews retrieved successfully',
            'data' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'bnb_info' => [
                    'id' => $bnb->id,
                    'name' => $bnb->name,
                    'average_rating' => $bnb->average_rating,
                    'total_reviews' => $bnb->total_reviews,
                ],
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/bnbs/{id}/reviews",
     *     operationId="createBNBReview",
     *     tags={"Reviews"},
     *     summary="Create a new review for a BNB",
     *     description="Submit a review and rating for a BNB property. Users can only review each property once.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="BNB property ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rating", "comment"},
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=5, description="Rating from 1 to 5 stars"),
     *             @OA\Property(property="comment", type="string", maxLength=1000, example="Amazing property! Clean and comfortable.", description="Review comment"),
     *             @OA\Property(property="feedback_categories", type="array", @OA\Items(type="string"), example={"cleanliness", "communication"}, description="Optional feedback categories"),
     *             @OA\Property(property="stay_date", type="string", format="date", example="2025-08-15", description="Date of stay (optional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Review created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Review created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Review")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or duplicate review",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You have already reviewed this BNB")
     *         )
     *     )
     * )
     *
     * Store a new review for a BNB.
     */
    public function store(Request $request, $id): JsonResponse
    {
        $bnb = BNB::findOrFail($id);
        
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'feedback_categories' => 'nullable|array',
            'feedback_categories.*' => 'string|max:50',
            'stay_date' => 'nullable|date|before_or_equal:today',
        ]);

        // Check if user already reviewed this BNB
        $existingReview = Review::where('user_id', Auth::id())
            ->where('bnb_id', $id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this BNB',
            ], 422);
        }

        $review = Review::create([
            'user_id' => Auth::id(),
            'bnb_id' => $id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'feedback_categories' => $validated['feedback_categories'] ?? null,
            'stay_date' => $validated['stay_date'] ?? null,
            'is_verified' => false, // Would be set to true after booking verification
        ]);

        $review->load('user:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Review created successfully',
            'data' => $review,
        ], 201);
    }

    /**
     * Update a review.
     */
    public function update(Request $request, Review $review): JsonResponse
    {
        // Check if user owns this review
        if ($review->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this review',
            ], 403);
        }

        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'sometimes|string|max:1000',
            'feedback_categories' => 'nullable|array',
            'feedback_categories.*' => 'string|max:50',
        ]);

        $review->update($validated);
        $review->load('user:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => $review,
        ]);
    }

    /**
     * Delete a review.
     */
    public function destroy(Review $review): JsonResponse
    {
        // Check if user owns this review or is admin
        if ($review->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this review',
            ], 403);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }
}
