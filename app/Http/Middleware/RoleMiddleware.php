<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RoleMiddleware
 * 
 * Middleware for role-based access control in the BNB Management System.
 * Ensures users have the required role to access protected endpoints.
 * 
 * @package App\Http\Middleware
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles
     * @return Response|JsonResponse
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in to access this resource.',
                'error_code' => 'UNAUTHENTICATED'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        // Check if user has any of the required roles
        if (!empty($roles) && !in_array($user->role, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You do not have the required permissions to access this resource.',
                'error_code' => 'FORBIDDEN',
                'required_roles' => $roles,
                'user_role' => $user->role
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}