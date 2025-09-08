<?php

namespace App\Http\Controllers\Api\Schemas;

/**
 * @OA\Schema(
 *     schema="Bill",
 *     type="object",
 *     title="Bill",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="booking_reference", type="string", example="BK-20250908-001"),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="bnb_id", type="integer", example=1),
 *     @OA\Property(property="amount", type="string", example="450.00"),
 *     @OA\Property(property="tax_amount", type="string", example="67.50"),
 *     @OA\Property(property="total_amount", type="string", example="517.50"),
 *     @OA\Property(property="currency", type="string", example="USD"),
 *     @OA\Property(property="status", type="string", enum={"pending", "paid", "cancelled", "refunded"}, example="paid"),
 *     @OA\Property(property="payment_method", type="string", example="Credit Card"),
 *     @OA\Property(property="transaction_id", type="string", example="txn_1234567890"),
 *     @OA\Property(property="check_in_date", type="string", format="date", example="2025-08-15"),
 *     @OA\Property(property="check_out_date", type="string", format="date", example="2025-08-18"),
 *     @OA\Property(property="nights", type="integer", example=3),
 *     @OA\Property(property="guests", type="integer", example=2),
 *     @OA\Property(property="paid_at", type="string", format="date-time", nullable=true, example="2025-08-10T14:30:00.000000Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-10T10:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-10T14:30:00.000000Z"),
 *     @OA\Property(property="bnb", type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Downtown Apartment"),
 *         @OA\Property(property="location", type="string", example="New York, NY")
 *     )
 * )
 */
class BillSchema
{
    // This class is only used for OpenAPI schema definitions
}