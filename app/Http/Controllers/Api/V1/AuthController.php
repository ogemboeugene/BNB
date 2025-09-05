<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AuthController
 * 
 * Handles user authentication operations including registration, login,
 * logout, and token management for the BNB Management System.
 * 
 * @package App\Http\Controllers\Api\V1
 */
class AuthController extends Controller
{
    use ApiResponseTrait;
    /**
     * Register a new user.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'nullable|string|in:admin,user'
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'] ?? User::ROLE_USER,
            ]);

            $token = $user->createToken('auth_token', ['*'], now()->addDays(30))->plainTextToken;

            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => new UserResource($user),
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => 30 * 24 * 60 * 60 // 30 days in seconds
                ]
            ], Response::HTTP_CREATED);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'message' => 'The given data was invalid',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('User registration failed', [
                'error' => $e->getMessage(),
                'data' => $request->except('password', 'password_confirmation')
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Registration failed',
                'message' => 'An error occurred during registration'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Login user and create token.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
                'remember' => 'nullable|boolean'
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid credentials',
                    'message' => 'The provided credentials are incorrect'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $user = Auth::user();
            $tokenExpiry = $validated['remember'] ?? false ? now()->addDays(30) : now()->addDay();
            $token = $user->createToken('auth_token', ['*'], $tokenExpiry)->plainTextToken;

            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'remember' => $validated['remember'] ?? false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => new UserResource($user),
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => ($validated['remember'] ?? false) ? 30 * 24 * 60 * 60 : 24 * 60 * 60
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'message' => 'The given data was invalid',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('User login failed', [
                'error' => $e->getMessage(),
                'email' => $request->input('email')
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Login failed',
                'message' => 'An error occurred during login'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Logout user and revoke token.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->currentAccessToken()->delete();

            Log::info('User logged out successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('User logout failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Logout failed',
                'message' => 'An error occurred during logout'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Refresh user token.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $currentToken = $user->currentAccessToken();
            
            // Delete current token
            $currentToken->delete();
            
            // Create new token
            $newToken = $user->createToken('auth_token', ['*'], now()->addDay())->plainTextToken;

            Log::info('Token refreshed successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'access_token' => $newToken,
                    'token_type' => 'Bearer',
                    'expires_in' => 24 * 60 * 60 // 24 hours in seconds
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Token refresh failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Token refresh failed',
                'message' => 'An error occurred while refreshing token'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get authenticated user profile.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'message' => 'User profile retrieved successfully',
                'data' => [
                    'user' => new UserResource($user)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve user profile', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Profile retrieval failed',
                'message' => 'An error occurred while retrieving profile'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
