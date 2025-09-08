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
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="User model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="role", type="string", enum={"admin", "user"}, example="user"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2025-09-08T10:00:00.000000Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-08T10:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-08T10:00:00.000000Z")
 * )
 */

/**
 * Class AuthController
 * 
 * Handles user authentication operations including registration, login,
 * logout, and token management for the BNB Management System.
 * 
 * @package App\Http\Controllers\Api\V1
 */
#[OA\Tag(
    name: 'Authentication',
    description: 'User authentication and authorization endpoints'
)]
class AuthController extends Controller
{
    use ApiResponseTrait;
    /**
     * Register a new user.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    #[OA\Post(
        path: '/auth/register',
        summary: 'Register a new user',
        description: 'Create a new user account and return authentication token',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 255, example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', minLength: 8, example: 'password123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', minLength: 8, example: 'password123'),
                    new OA\Property(property: 'role', type: 'string', enum: ['admin', 'user'], default: 'user', example: 'user')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'User registered successfully'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                                new OA\Property(property: 'access_token', type: 'string', example: '1|eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'),
                                new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                                new OA\Property(property: 'expires_in', type: 'integer', example: 2592000)
                            ],
                            type: 'object'
                        )
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
            new OA\Response(response: 500, description: 'Registration failed', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
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
    #[OA\Post(
        path: '/auth/login',
        summary: 'User login',
        description: 'Authenticate user and return access token',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                    new OA\Property(property: 'remember', type: 'boolean', default: false, example: false)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Login successful'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                                new OA\Property(property: 'access_token', type: 'string', example: '2|eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'),
                                new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                                new OA\Property(property: 'expires_in', type: 'integer', example: 86400)
                            ],
                            type: 'object'
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Invalid credentials', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))
        ]
    )]
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
    #[OA\Post(
        path: '/auth/logout',
        summary: 'User logout',
        description: 'Logout user and revoke access token',
        security: [['sanctum' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logged out successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Logged out successfully')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 500, description: 'Logout failed', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
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
     * @OA\Post(
     *     path="/auth/refresh",
     *     operationId="refreshToken",
     *     tags={"Authentication"},
     *     summary="Refresh authentication token",
     *     description="Refresh the current authentication token. The old token will be invalidated.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token refreshed successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="access_token", type="string", example="2|abcdef123456..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="user", ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or expired token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     *
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
    #[OA\Get(
        path: '/auth/profile',
        summary: 'Get user profile',
        description: 'Retrieve authenticated user profile information',
        security: [['sanctum' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User profile retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'User profile retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'user', ref: '#/components/schemas/User')
                            ],
                            type: 'object'
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 500, description: 'Profile retrieval failed', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
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
