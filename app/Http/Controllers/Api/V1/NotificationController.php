<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

/**
 * Class NotificationController
 * 
 * Handles user notification management including retrieving notifications,
 * marking them as read, and deleting notifications.
 * 
 * @package App\Http\Controllers\Api\V1
 */
#[OA\Tag(
    name: 'Notifications',
    description: 'Notification management endpoints'
)]
class NotificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/notifications",
     *     operationId="getUserNotifications",
     *     tags={"Notifications"},
     *     summary="Get user notifications",
     *     description="Retrieve paginated list of user's notifications, ordered by most recent first.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notifications retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="string", example="uuid-string"),
     *                     @OA\Property(property="type", type="string", example="App\\Notifications\\BookingConfirmation"),
     *                     @OA\Property(property="data", type="object",
     *                         @OA\Property(property="title", type="string", example="New Review"),
     *                         @OA\Property(property="message", type="string", example="You received a new review")
     *                     ),
     *                     @OA\Property(property="read_at", type="string", format="date-time", nullable=true, example=null),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-08T10:00:00.000000Z")
     *                 )),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=42)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     *
     * Get user's notifications.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => auth()->user()->notifications()->latest()->paginate(15),
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/notifications/{id}/mark-read",
     *     operationId="markNotificationAsRead",
     *     tags={"Notifications"},
     *     summary="Mark notification as read",
     *     description="Mark a specific notification as read for the authenticated user.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Notification ID",
     *         required=true,
     *         @OA\Schema(type="string", example="uuid-string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification marked as read",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notification marked as read")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Notification not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     *
     * Mark notification as read.
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/notifications/{id}",
     *     operationId="deleteNotification",
     *     tags={"Notifications"},
     *     summary="Delete notification",
     *     description="Delete a specific notification for the authenticated user.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Notification ID",
     *         required=true,
     *         @OA\Schema(type="string", example="uuid-string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notification deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Notification not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     *
     * Delete notification.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        auth()->user()->notifications()->findOrFail($id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully',
        ]);
    }
}
