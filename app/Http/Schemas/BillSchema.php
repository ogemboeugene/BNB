<?php

namespace App\Http\Schemas;

use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="Bill",
 *     type="object",
 *     title="Bill",
 *     description="Billing/Payment record model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="bnb_id", type="integer", example=1),
 *     @OA\Property(property="booking_reference", type="string", example="BK-20250908-001"),
 *     @OA\Property(property="amount", type="number", format="float", example=450.00),
 *     @OA\Property(property="currency", type="string", example="USD"),
 *     @OA\Property(property="status", type="string", enum={"pending", "paid", "cancelled", "refunded"}, example="paid"),
 *     @OA\Property(property="payment_method", type="string", example="credit_card"),
 *     @OA\Property(property="payment_date", type="string", format="date-time", nullable=true, example="2025-09-08T14:30:00.000000Z"),
 *     @OA\Property(property="check_in", type="string", format="date", example="2025-09-15"),
 *     @OA\Property(property="check_out", type="string", format="date", example="2025-09-18"),
 *     @OA\Property(property="nights", type="integer", example=3),
 *     @OA\Property(property="guests", type="integer", example=2),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-08T10:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-08T10:00:00.000000Z"),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="bnb", ref="#/components/schemas/BNB")
 * )
 */
class BillSchema
{
    // This class is used only for OpenAPI schema definition
}