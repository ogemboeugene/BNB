<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="SupportTicket",
 *     type="object",
 *     title="SupportTicket",
 *     description="Support ticket model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="subject", type="string", example="Issue with booking"),
 *     @OA\Property(property="message", type="string", example="I'm having trouble with my booking..."),
 *     @OA\Property(property="status", type="string", enum={"open", "in_progress", "resolved", "closed"}, example="open"),
 *     @OA\Property(property="priority", type="string", enum={"low", "medium", "high", "urgent"}, example="medium"),
 *     @OA\Property(property="category", type="string", example="booking"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-08T10:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-08T10:00:00.000000Z"),
 *     @OA\Property(property="user", type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="John Doe"),
 *         @OA\Property(property="email", type="string", example="john@example.com")
 *     )
 * )
 */

class SupportTicketController extends Controller
{
    /**
     * @OA\Get(
     *     path="/support/tickets",
     *     operationId="getUserSupportTickets",
     *     tags={"Support"},
     *     summary="Get user's support tickets",
     *     description="Retrieve paginated list of support tickets for the authenticated user with filtering options.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by ticket status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"open", "in_progress", "resolved", "closed"}, example="open")
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filter by ticket priority",
     *         required=false,
     *         @OA\Schema(type="string", enum={"low", "medium", "high", "urgent"}, example="high")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Support tickets retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Support tickets retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SupportTicket")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=2),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=23)
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
     * Get user's support tickets.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SupportTicket::where('user_id', Auth::id())
            ->with('assignedTo:id,name')
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority if provided
        if ($request->has('priority')) {
            $query->byPriority($request->priority);
        }

        $tickets = $query->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Support tickets retrieved successfully',
            'data' => $tickets->items(),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/support/tickets",
     *     operationId="createSupportTicket",
     *     tags={"Support"},
     *     summary="Create a new support ticket",
     *     description="Submit a new support ticket with auto-generated ticket number and priority handling.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"subject", "message"},
     *             @OA\Property(property="subject", type="string", maxLength=255, example="Login Issue", description="Ticket subject"),
     *             @OA\Property(property="message", type="string", maxLength=2000, example="I'm having trouble accessing my account", description="Detailed message"),
     *             @OA\Property(property="priority", type="string", enum={"low", "medium", "high", "urgent"}, example="medium", description="Ticket priority (default: medium)"),
     *             @OA\Property(property="category", type="string", enum={"technical", "billing", "general", "booking", "account"}, example="technical", description="Ticket category (default: general)"),
     *             @OA\Property(property="attachments", type="array", @OA\Items(type="string", format="url"), example={"https://example.com/file1.jpg"}, description="Optional file attachments (max 5)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Support ticket created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Support ticket created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/SupportTicket")
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
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     *
     * Create a new support ticket.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'category' => 'sometimes|in:technical,billing,general,booking,account',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'string|url',
        ]);

        $ticket = SupportTicket::create([
            'ticket_number' => SupportTicket::generateTicketNumber(),
            'user_id' => Auth::id(),
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'priority' => $validated['priority'] ?? 'medium',
            'category' => $validated['category'] ?? 'general',
            'status' => 'open',
            'attachments' => $validated['attachments'] ?? null,
        ]);

        $ticket->load('user:id,name');

        // Send notification to admins (could be implemented later)
        // Notification::send(User::admins()->get(), new SupportTicketCreated($ticket));

        return response()->json([
            'success' => true,
            'message' => 'Support ticket created successfully',
            'data' => $ticket,
        ], 201);
    }

    /**
     * Get a specific support ticket.
     */
    public function show(SupportTicket $ticket): JsonResponse
    {
        // Check if user owns this ticket or is admin
        if ($ticket->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this ticket',
            ], 403);
        }

        $ticket->load(['user:id,name', 'assignedTo:id,name']);

        return response()->json([
            'success' => true,
            'message' => 'Support ticket retrieved successfully',
            'data' => $ticket,
        ]);
    }

    /**
     * Update support ticket (for users to add more info or close).
     */
    public function update(Request $request, SupportTicket $ticket): JsonResponse
    {
        // Check if user owns this ticket
        if ($ticket->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this ticket',
            ], 403);
        }

        // Users can only update certain fields
        $validated = $request->validate([
            'message' => 'sometimes|string|max:2000',
            'status' => 'sometimes|in:open,closed', // Users can only open or close
            'priority' => 'sometimes|in:low,medium,high,urgent',
        ]);

        // If closing ticket, set resolved_at
        if (isset($validated['status']) && $validated['status'] === 'closed') {
            $validated['resolved_at'] = now();
        }

        $ticket->update($validated);
        $ticket->load(['user:id,name', 'assignedTo:id,name']);

        return response()->json([
            'success' => true,
            'message' => 'Support ticket updated successfully',
            'data' => $ticket,
        ]);
    }
}
