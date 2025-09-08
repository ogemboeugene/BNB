<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BNB;
use App\Models\Review;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Class AdminController
 * 
 * Handles administrative operations including user management,
 * platform statistics, and system-wide analytics.
 * 
 * @package App\Http\Controllers\Api\V1
 */
#[OA\Tag(
    name: 'Admin',
    description: 'Administrative endpoints (Admin role required)'
)]
class AdminController extends Controller
{
    /**
     * @OA\Get(
     *     path="/admin/users",
     *     operationId="getAdminUsers",
     *     tags={"Admin"},
     *     summary="Get all users (Admin only)",
     *     description="Retrieve paginated list of all platform users with their details.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Users retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Users retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="role", type="string", example="user"),
     *                     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-08T10:00:00.000000Z")
     *                 )),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=75)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied (Admin role required)",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Access denied")
     *         )
     *     )
     * )
     *
     * Get all users (Admin only).
     */
    public function getUsers(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $users = User::latest()->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully',
            'data' => $users,
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/admin/users/{user}/role",
     *     operationId="updateUserRole",
     *     tags={"Admin"},
     *     summary="Update user role (Admin only)",
     *     description="Update the role of a specific user. Allows promotion to admin or demotion to regular user.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"role"},
     *             @OA\Property(property="role", type="string", enum={"admin", "user"}, example="admin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User role updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User role updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="role", type="string", example="admin")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied (Admin role required)",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Access denied")
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
     * Update user role (Admin only).
     */
    public function updateUserRole(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'role' => 'required|string|in:admin,user'
        ]);

        $user->role = $validated['role'];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User role updated successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/admin/stats",
     *     operationId="getAdminStats",
     *     tags={"Admin"},
     *     summary="Get platform statistics (Admin only)",
     *     description="Retrieve comprehensive platform statistics including user counts, property metrics, and system health.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Admin statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Admin statistics retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="users", type="object",
     *                     @OA\Property(property="total_users", type="integer", example=150),
     *                     @OA\Property(property="admin_users", type="integer", example=3),
     *                     @OA\Property(property="verified_users", type="integer", example=120),
     *                     @OA\Property(property="new_users_this_month", type="integer", example=25)
     *                 ),
     *                 @OA\Property(property="properties", type="object",
     *                     @OA\Property(property="total_bnbs", type="integer", example=45),
     *                     @OA\Property(property="active_bnbs", type="integer", example=38),
     *                     @OA\Property(property="featured_bnbs", type="integer", example=6),
     *                     @OA\Property(property="average_rating", type="number", format="float", example=4.2)
     *                 ),
     *                 @OA\Property(property="reviews", type="object",
     *                     @OA\Property(property="total_reviews", type="integer", example=234),
     *                     @OA\Property(property="verified_reviews", type="integer", example=180),
     *                     @OA\Property(property="average_rating", type="number", format="float", example=4.3)
     *                 ),
     *                 @OA\Property(property="support", type="object",
     *                     @OA\Property(property="total_tickets", type="integer", example=67),
     *                     @OA\Property(property="open_tickets", type="integer", example=12),
     *                     @OA\Property(property="resolved_tickets", type="integer", example=55)
     *                 )
             )
         )
     ),
     @OA\Response(
         response=403,
         description="Access denied (Admin role required)",
         @OA\JsonContent(
             @OA\Property(property="success", type="boolean", example=false),
             @OA\Property(property="message", type="string", example="Access denied")
         )
     )
 )

 /**
  * Get platform statistics (Admin only).
  */
    public function getStats(): JsonResponse
    {
        $stats = [
            'users' => [
                'total_users' => User::count(),
                'admin_users' => User::where('role', 'admin')->count(),
                'verified_users' => User::whereNotNull('email_verified_at')->count(),
                'new_users_this_month' => User::whereMonth('created_at', now()->month)->count(),
            ],
            'properties' => [
                'total_bnbs' => BNB::count(),
                'active_bnbs' => BNB::where('availability', true)->count(),
                'featured_bnbs' => BNB::where('featured', true)->count(),
                'average_rating' => BNB::avg('average_rating'),
            ],
            'reviews' => [
                'total_reviews' => Review::count(),
                'verified_reviews' => Review::where('is_verified', true)->count(),
                'average_rating' => Review::avg('rating'),
            ],
            'support' => [
                'total_tickets' => SupportTicket::count(),
                'open_tickets' => SupportTicket::where('status', 'open')->count(),
                'resolved_tickets' => SupportTicket::where('status', 'resolved')->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Admin statistics retrieved successfully',
            'data' => $stats,
        ]);
    }
}
