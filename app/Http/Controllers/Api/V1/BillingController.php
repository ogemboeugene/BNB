<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Class BillingController
 * 
 * Handles billing and payment operations for BNB bookings.
 * This includes managing payment records, generating invoices,
 * and tracking payment history.
 * 
 * @package App\Http\Controllers\Api\V1
 */
#[OA\Tag(
    name: 'Billing',
    description: 'Billing and payment management endpoints'
)]
class BillingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/user/bills",
     *     operationId="getUserBills",
     *     tags={"Billing"},
     *     summary="Get user's billing history",
     *     description="Retrieve paginated billing history for the authenticated user including payment records and booking details.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by payment status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "paid", "cancelled", "refunded"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bills retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Bills retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Bill")),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=42)
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
     * Get user's billing history.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'nullable|string|in:pending,paid,cancelled,refunded',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        // Mock data for demonstration since we don't have a bills table yet
        $mockBills = [
            [
                'id' => 1,
                'booking_reference' => 'BK-20250908-001',
                'amount' => 450.00,
                'currency' => 'USD',
                'status' => 'paid',
                'payment_method' => 'credit_card',
                'payment_date' => '2025-09-08T14:30:00.000000Z',
                'check_in' => '2025-09-15',
                'check_out' => '2025-09-18',
                'nights' => 3,
                'guests' => 2,
                'bnb' => ['id' => 1, 'name' => 'Cozy Downtown Loft'],
                'created_at' => '2025-09-08T10:00:00.000000Z',
            ],
            [
                'id' => 2,
                'booking_reference' => 'BK-20250901-002',
                'amount' => 300.00,
                'currency' => 'USD',
                'status' => 'pending',
                'payment_method' => null,
                'payment_date' => null,
                'check_in' => '2025-09-22',
                'check_out' => '2025-09-24',
                'nights' => 2,
                'guests' => 1,
                'bnb' => ['id' => 2, 'name' => 'Mountain View Cabin'],
                'created_at' => '2025-09-01T10:00:00.000000Z',
            ],
        ];

        // Filter by status if provided
        if (!empty($validated['status'])) {
            $mockBills = array_filter($mockBills, function ($bill) use ($validated) {
                return $bill['status'] === $validated['status'];
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'Bills retrieved successfully',
            'data' => [
                'data' => array_values($mockBills),
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => $validated['per_page'] ?? 15,
                'total' => count($mockBills),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/bills/{id}",
     *     operationId="getBillDetails",
     *     tags={"Billing"},
     *     summary="Get bill details",
     *     description="Retrieve detailed information about a specific bill including payment details and booking information.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Bill ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bill details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Bill details retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Bill")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bill not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Bill not found")
     *         )
     *     )
     * )
     *
     * Get bill details.
     */
    public function show($id): JsonResponse
    {
        // Mock data for demonstration
        if ($id != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Bill not found',
            ], 404);
        }

        $mockBill = [
            'id' => 1,
            'booking_reference' => 'BK-20250908-001',
            'amount' => 450.00,
            'currency' => 'USD',
            'status' => 'paid',
            'payment_method' => 'credit_card',
            'payment_date' => '2025-09-08T14:30:00.000000Z',
            'check_in' => '2025-09-15',
            'check_out' => '2025-09-18',
            'nights' => 3,
            'guests' => 2,
            'bnb' => [
                'id' => 1,
                'name' => 'Cozy Downtown Loft',
                'location' => 'New York, NY',
                'price_per_night' => 150.00,
            ],
            'user' => [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ],
            'breakdown' => [
                'subtotal' => 450.00,
                'cleaning_fee' => 25.00,
                'service_fee' => 35.00,
                'taxes' => 40.00,
                'total' => 550.00,
            ],
            'created_at' => '2025-09-08T10:00:00.000000Z',
            'updated_at' => '2025-09-08T14:30:00.000000Z',
        ];

        return response()->json([
            'success' => true,
            'message' => 'Bill details retrieved successfully',
            'data' => $mockBill,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/bills/{id}/pay",
     *     operationId="payBill",
     *     tags={"Billing"},
     *     summary="Process payment for a bill",
     *     description="Process payment for a pending bill using the specified payment method.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Bill ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"payment_method"},
     *             @OA\Property(property="payment_method", type="string", enum={"credit_card", "debit_card", "paypal", "bank_transfer"}, example="credit_card"),
     *             @OA\Property(property="payment_token", type="string", example="tok_1234567890abcdef")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment processed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment processed successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="payment_id", type="string", example="pay_1234567890"),
     *                 @OA\Property(property="status", type="string", example="paid"),
     *                 @OA\Property(property="paid_at", type="string", format="date-time", example="2025-09-08T14:30:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Payment failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Payment processing failed")
     *         )
     *     )
     * )
     *
     * Process payment for a bill.
     */
    public function pay(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => 'required|string|in:credit_card,debit_card,paypal,bank_transfer',
            'payment_token' => 'nullable|string',
        ]);

        // Mock payment processing
        if ($id != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Bill not found',
            ], 404);
        }

        // Simulate payment processing
        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully',
            'data' => [
                'payment_id' => 'pay_' . uniqid(),
                'status' => 'paid',
                'paid_at' => now()->toISOString(),
            ],
        ]);
    }
}
