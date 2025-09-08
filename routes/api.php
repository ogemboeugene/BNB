<?php

use App\Http\Controllers\Api\V1\BNBController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AvailabilityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
| API Versioning Strategy:
| - v1: Current stable API version
| - All routes are prefixed with /api/v1/
| - Authentication required for write operations
| - Rate limiting applied to all routes
|
*/

/*
|--------------------------------------------------------------------------
| API Version 1 Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Health Check Routes
    |--------------------------------------------------------------------------
    | 
    | These routes are used for monitoring the API health and status.
    | No authentication required for monitoring purposes.
    */
    Route::get('/health', [HealthController::class, 'check'])->name('health.check');
    Route::get('/health/detailed', [HealthController::class, 'detailed'])->name('health.detailed');
    
    /*
    |--------------------------------------------------------------------------
    | Test Routes (Development Only)
    |--------------------------------------------------------------------------
    */
    Route::get('/test-cloudinary', function () {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Cloudinary configuration test',
                'config' => [
                    'cloud_name' => config('cloudinary.cloud_name'),
                    'api_key' => config('cloudinary.api_key'),
                    'api_secret_set' => !empty(config('cloudinary.api_secret')),
                    'url_set' => !empty(config('cloudinary.cloudinary_url')),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'config' => [
                    'cloud_name' => config('cloudinary.cloud_name'),
                    'api_key' => config('cloudinary.api_key'),
                    'api_secret_set' => !empty(config('cloudinary.api_secret')),
                ],
            ], 500);
        }
    })->name('test.cloudinary');
    
    /*
    |--------------------------------------------------------------------------
    | Public BNB Routes
    |--------------------------------------------------------------------------
    | 
    | These routes are publicly accessible and don't require authentication.
    | They are used for browsing and viewing BNB listings.
    */
    Route::get('/bnbs', [BNBController::class, 'index'])->name('bnbs.index');
    Route::get('/bnbs/search/nearby', [BNBController::class, 'searchNearby'])->name('bnbs.search.nearby');
    Route::get('/bnbs/search/map', [BNBController::class, 'getForMap'])->name('bnbs.search.map');
    Route::get('/bnbs/{id}', [BNBController::class, 'show'])->name('bnbs.show');
    Route::get('/bnbs/{id}/reviews', [\App\Http\Controllers\ReviewController::class, 'index'])->name('bnbs.reviews.index');
    
    // Public availability endpoints
    Route::get('/bnbs/{bnb}/availability', [AvailabilityController::class, 'index'])->name('bnbs.availability.public');
    Route::post('/bnbs/{bnb}/availability/check', [AvailabilityController::class, 'checkAvailability'])->name('bnbs.availability.check.public');
    
    /*
    |--------------------------------------------------------------------------
    | Protected BNB Routes
    |--------------------------------------------------------------------------
    | 
    | These routes require authentication using Laravel Sanctum.
    | They are used for creating, updating, and deleting BNB listings.
    */
    Route::middleware(['auth:sanctum'])->group(function () {
        
        // Standard CRUD operations (authenticated users)
        Route::post('/bnbs', [BNBController::class, 'store'])->name('bnbs.store');
        Route::put('/bnbs/{id}', [BNBController::class, 'update'])->name('bnbs.update');
        Route::patch('/bnbs/{id}', [BNBController::class, 'update'])->name('bnbs.patch');
        
        // Additional BNB operations (authenticated users)
        Route::patch('/bnbs/{id}/availability', [BNBController::class, 'updateAvailability'])->name('bnbs.availability');
        
        // Review endpoints (authenticated)
        Route::post('/bnbs/{id}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('bnbs.reviews.store');
        Route::put('/reviews/{review}', [\App\Http\Controllers\ReviewController::class, 'update'])->name('reviews.update');
        Route::delete('/reviews/{review}', [\App\Http\Controllers\ReviewController::class, 'destroy'])->name('reviews.destroy');
        
        // Notification endpoints
        Route::get('/notifications', function(Request $request) {
            return response()->json([
                'success' => true,
                'data' => auth()->user()->notifications()->latest()->paginate(15),
            ]);
        })->name('notifications.index');
        
        Route::patch('/notifications/{id}/mark-read', function(Request $request, $id) {
            $notification = auth()->user()->notifications()->findOrFail($id);
            $notification->update(['is_read' => true, 'read_at' => now()]);
            return response()->json(['success' => true, 'message' => 'Notification marked as read']);
        })->name('notifications.mark-read');
        
        Route::delete('/notifications/{id}', function(Request $request, $id) {
            auth()->user()->notifications()->findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Notification deleted']);
        })->name('notifications.delete');
        
        // Support ticket endpoints
        Route::get('/support/tickets', [\App\Http\Controllers\SupportTicketController::class, 'index'])->name('support.tickets.index');
        Route::post('/support/tickets', [\App\Http\Controllers\SupportTicketController::class, 'store'])->name('support.tickets.store');
        Route::get('/support/tickets/{ticket}', [\App\Http\Controllers\SupportTicketController::class, 'show'])->name('support.tickets.show');
        Route::patch('/support/tickets/{ticket}', [\App\Http\Controllers\SupportTicketController::class, 'update'])->name('support.tickets.update');
        
        // Availability management endpoints (for property owners)
        Route::patch('/bnbs/{bnb}/availability/update', [AvailabilityController::class, 'update'])->name('bnbs.availability.update');
        Route::post('/bnbs/{bnb}/availability/block', [AvailabilityController::class, 'blockDates'])->name('bnbs.availability.block');
        
        // Analytics and dashboard endpoints
        Route::get('/dashboard/analytics', [BNBController::class, 'getAnalytics'])->name('dashboard.analytics');
        Route::get('/bnbs/{id}/analytics', [BNBController::class, 'getBnbAnalytics'])->name('bnbs.analytics');
        
        // User information route
        Route::get('/user', function (Request $request) {
            return response()->json([
                'success' => true,
                'message' => 'User information retrieved successfully',
                'data' => new \App\Http\Resources\UserResource($request->user())
            ]);
        })->name('user.profile');
        
    });
    
    /*
    |--------------------------------------------------------------------------
    | Admin Only Routes
    |--------------------------------------------------------------------------
    | 
    | These routes require admin role access for sensitive operations.
    */
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        
        // Admin-only BNB operations
        Route::delete('/bnbs/{id}', [BNBController::class, 'destroy'])->name('bnbs.destroy');
        
        // Admin user management
        Route::get('/admin/users', function (Request $request) {
            $users = \App\Models\User::with(['tokens'])->paginate(15);
            return \App\Http\Resources\UserResource::collection($users);
        })->name('admin.users.index');
        
        Route::patch('/admin/users/{user}/role', function (Request $request, \App\Models\User $user) {
            $validated = $request->validate([
                'role' => 'required|string|in:admin,user'
            ]);
            
            $user->assignRole($validated['role']);
            
            return response()->json([
                'success' => true,
                'message' => 'User role updated successfully',
                'data' => new \App\Http\Resources\UserResource($user->fresh())
            ]);
        })->name('admin.users.role');
        
        // Admin statistics
        Route::get('/admin/stats', function () {
            $stats = [
                'total_users' => \App\Models\User::count(),
                'admin_users' => \App\Models\User::admins()->count(),
                'regular_users' => \App\Models\User::regularUsers()->count(),
                'total_bnbs' => \App\Models\BNB::count(),
                'active_bnbs' => \App\Models\BNB::available()->count(),
                'inactive_bnbs' => \App\Models\BNB::unavailable()->count(),
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'Admin statistics retrieved successfully',
                'data' => $stats
            ]);
        })->name('admin.stats');
        
    });
    
    /*
    |--------------------------------------------------------------------------
    | Authentication Routes
    |--------------------------------------------------------------------------
    | 
    | These routes handle user authentication and token management.
    */
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
            Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
        });
    });
    
});

/*
|--------------------------------------------------------------------------
| Rate Limiting
|--------------------------------------------------------------------------
| 
| API routes are automatically rate limited by the 'api' middleware group.
| You can customize rate limiting in app/Http/Kernel.php or create
| custom rate limiters in app/Providers/RouteServiceProvider.php
*/

/*
|--------------------------------------------------------------------------
| API Response Middleware
|--------------------------------------------------------------------------
| 
| All API routes automatically have the following middleware applied:
| - throttle:api (rate limiting)
| - json.response (ensure JSON responses)
| - cors (Cross-Origin Resource Sharing)
*/