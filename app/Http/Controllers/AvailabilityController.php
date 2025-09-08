<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\BNB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

/**
 * Class AvailabilityController
 * 
 * Handles availability and calendar management for BNB properties.
 * Provides endpoints for checking availability, managing booking calendars,
 * and setting price overrides for specific dates.
 * 
 * @package App\Http\Controllers
 */
#[OA\Tag(
    name: 'Availability',
    description: 'Calendar and availability management endpoints'
)]
class AvailabilityController extends Controller
{
    /**
     * @OA\Get(
     *     path="/bnbs/{bnb}/availability",
     *     operationId="getBNBAvailabilityCalendar",
     *     tags={"Availability"},
     *     summary="Get availability calendar",
     *     description="Get availability calendar for a date range with pricing information.",
     *     @OA\Parameter(
     *         name="bnb",
     *         in="path",
     *         description="BNB property ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date for calendar",
     *         required=true,
     *         @OA\Schema(type="string", format="date", example="2025-12-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date for calendar",
     *         required=true,
     *         @OA\Schema(type="string", format="date", example="2025-12-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Availability calendar retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Availability calendar retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="bnb", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Downtown Apartment"),
     *                     @OA\Property(property="default_price", type="string", example="150.00"),
     *                     @OA\Property(property="default_availability", type="boolean", example=true)
     *                 ),
     *                 @OA\Property(property="calendar", type="array", @OA\Items(
     *                     @OA\Property(property="date", type="string", format="date", example="2025-12-01"),
     *                     @OA\Property(property="is_available", type="boolean", example=true),
     *                     @OA\Property(property="price_override", type="number", format="float", nullable=true, example=null),
     *                     @OA\Property(property="effective_price", type="string", example="150.00")
     *                 ))
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
     * Get availability calendar for a BNB.
     */
    public function index(Request $request, $bnbId): JsonResponse
    {
        $bnb = BNB::findOrFail($bnbId);
        
        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        $availability = Availability::where('bnb_id', $bnbId)
            ->whereBetween('date', [$validated['start_date'], $validated['end_date']])
            ->orderBy('date')
            ->get();

        // Generate full calendar with default availability
        $calendar = [];
        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        
        while ($start <= $end) {
            $dateStr = $start->format('Y-m-d');
            $existingAvailability = $availability->firstWhere('date', $dateStr);
            
            $calendar[] = [
                'date' => $dateStr,
                'is_available' => $existingAvailability ? $existingAvailability->is_available : $bnb->availability,
                'price_override' => $existingAvailability ? $existingAvailability->price_override : null,
                'effective_price' => $existingAvailability && $existingAvailability->price_override 
                    ? $existingAvailability->price_override 
                    : $bnb->price_per_night,
            ];
            
            $start->addDay();
        }

        return response()->json([
            'success' => true,
            'message' => 'Availability calendar retrieved successfully',
            'data' => [
                'bnb' => [
                    'id' => $bnb->id,
                    'name' => $bnb->name,
                    'default_price' => $bnb->price_per_night,
                    'default_availability' => $bnb->availability,
                ],
                'calendar' => $calendar,
            ],
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/bnbs/{bnb}/availability/update",
     *     operationId="updateBNBAvailability",
     *     tags={"Availability"},
     *     summary="Update availability for dates",
     *     description="Update availability and pricing for specific dates (property owners only).",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="bnb",
     *         in="path",
     *         description="BNB property ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"dates"},
     *             @OA\Property(property="dates", type="array", @OA\Items(
     *                 required={"date", "is_available"},
     *                 @OA\Property(property="date", type="string", format="date", example="2025-12-25"),
     *                 @OA\Property(property="is_available", type="boolean", example=true),
     *                 @OA\Property(property="price_override", type="number", format="float", nullable=true, example=200.00)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Availability updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Availability updated successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="date", type="string", format="date", example="2025-12-25"),
     *                 @OA\Property(property="is_available", type="boolean", example=true),
     *                 @OA\Property(property="price_override", type="number", format="float", example=200.00),
     *                 @OA\Property(property="effective_price", type="string", example="200.00")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     *
     * Update availability for specific dates.
     */
    public function update(Request $request, $bnbId): JsonResponse
    {
        $bnb = BNB::findOrFail($bnbId);
        
        // Check if user owns this BNB (this would need proper owner relationship)
        // For now, just check if authenticated
        if (!Auth::user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'dates' => 'required|array|min:1',
            'dates.*.date' => 'required|date|after_or_equal:today',
            'dates.*.is_available' => 'required|boolean',
            'dates.*.price_override' => 'nullable|numeric|min:0',
        ]);

        $updated = [];
        foreach ($validated['dates'] as $dateData) {
            $availability = Availability::updateOrCreate(
                [
                    'bnb_id' => $bnbId,
                    'date' => $dateData['date'],
                ],
                [
                    'is_available' => $dateData['is_available'],
                    'price_override' => $dateData['price_override'] ?? null,
                ]
            );
            
            $updated[] = [
                'date' => $availability->date,
                'is_available' => $availability->is_available,
                'price_override' => $availability->price_override,
                'effective_price' => $availability->price_override ?? $bnb->price_per_night,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Availability updated successfully',
            'data' => $updated,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/bnbs/{bnb}/availability/check",
     *     operationId="checkBNBAvailability",
     *     tags={"Availability"},
     *     summary="Check availability for date range",
     *     description="Check if a BNB is available for specific dates and get pricing breakdown.",
     *     @OA\Parameter(
     *         name="bnb",
     *         in="path",
     *         description="BNB property ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"check_in", "check_out"},
     *             @OA\Property(property="check_in", type="string", format="date", example="2025-12-01", description="Check-in date"),
     *             @OA\Property(property="check_out", type="string", format="date", example="2025-12-07", description="Check-out date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Availability checked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Availability checked successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="is_available", type="boolean", example=true),
     *                 @OA\Property(property="check_in", type="string", format="date", example="2025-12-01"),
     *                 @OA\Property(property="check_out", type="string", format="date", example="2025-12-07"),
     *                 @OA\Property(property="nights", type="integer", example=6),
     *                 @OA\Property(property="total_price", type="number", format="float", example=900.00),
     *                 @OA\Property(property="average_price_per_night", type="number", format="float", example=150.00),
     *                 @OA\Property(property="price_breakdown", type="array", @OA\Items(
     *                     @OA\Property(property="date", type="string", format="date", example="2025-12-01"),
     *                     @OA\Property(property="price", type="number", format="float", example=150.00)
     *                 ))
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
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     )
     * )
     *
     * Check availability for a date range (for booking validation).
     */
    public function checkAvailability(Request $request, $bnbId): JsonResponse
    {
        $bnb = BNB::findOrFail($bnbId);
        
        $validated = $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
        ]);

        $isAvailable = $bnb->isAvailableOnDates($validated['check_in'], $validated['check_out']);
        
        // Get pricing for the range
        $checkIn = Carbon::parse($validated['check_in']);
        $checkOut = Carbon::parse($validated['check_out']);
        $totalPrice = 0;
        $nights = 0;
        $priceBreakdown = [];

        $current = $checkIn->copy();
        while ($current < $checkOut) {
            $availability = Availability::where('bnb_id', $bnbId)
                ->where('date', $current->format('Y-m-d'))
                ->first();
            
            $nightPrice = $availability && $availability->price_override 
                ? $availability->price_override 
                : $bnb->price_per_night;
            
            $totalPrice += $nightPrice;
            $nights++;
            
            $priceBreakdown[] = [
                'date' => $current->format('Y-m-d'),
                'price' => $nightPrice,
            ];
            
            $current->addDay();
        }

        return response()->json([
            'success' => true,
            'message' => 'Availability checked successfully',
            'data' => [
                'is_available' => $isAvailable,
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
                'nights' => $nights,
                'total_price' => $totalPrice,
                'average_price_per_night' => $nights > 0 ? $totalPrice / $nights : 0,
                'price_breakdown' => $priceBreakdown,
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/bnbs/{bnb}/availability/block",
     *     operationId="blockBNBDates",
     *     tags={"Availability"},
     *     summary="Block dates",
     *     description="Block multiple dates at once making them unavailable for booking.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="bnb",
     *         in="path",
     *         description="BNB property ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"start_date", "end_date"},
     *             @OA\Property(property="start_date", type="string", format="date", example="2025-12-24"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2025-12-26"),
     *             @OA\Property(property="reason", type="string", nullable=true, example="Holiday period - not available")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dates blocked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Dates blocked successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="blocked_dates", type="array", @OA\Items(type="string", format="date", example="2025-12-24")),
     *                 @OA\Property(property="reason", type="string", example="Holiday period - not available")
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
     * Block dates (make unavailable).
     */
    public function blockDates(Request $request, $bnbId): JsonResponse
    {
        $bnb = BNB::findOrFail($bnbId);
        
        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255',
        ]);

        $blocked = [];
        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        
        while ($start <= $end) {
            $availability = Availability::updateOrCreate(
                [
                    'bnb_id' => $bnbId,
                    'date' => $start->format('Y-m-d'),
                ],
                [
                    'is_available' => false,
                    'price_override' => null,
                ]
            );
            
            $blocked[] = $start->format('Y-m-d');
            $start->addDay();
        }

        return response()->json([
            'success' => true,
            'message' => 'Dates blocked successfully',
            'data' => [
                'blocked_dates' => $blocked,
                'reason' => $validated['reason'] ?? 'No reason provided',
            ],
        ]);
    }
}