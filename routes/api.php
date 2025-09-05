<?php

use App\Http\Controllers\Api\V1\BNBController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\AuthController;
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
    | Public BNB Routes
    |--------------------------------------------------------------------------
    | 
    | These routes are publicly accessible and don't require authentication.
    | They are used for browsing and viewing BNB listings.
    */
    Route::get('/bnbs', [BNBController::class, 'index'])->name('bnbs.index');
    Route::get('/bnbs/{id}', [BNBController::class, 'show'])->name('bnbs.show');
    
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