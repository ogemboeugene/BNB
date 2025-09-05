<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait ApiResponseTrait
 * 
 * Provides standardized API response methods for controllers.
 * Ensures consistent response structure across all API endpoints.
 * 
 * @package App\Traits
 */
trait ApiResponseTrait
{
    /**
     * Send a successful response.
     * 
     * @param mixed $data The response data
     * @param string $message The success message
     * @param int $statusCode The HTTP status code
     * @return JsonResponse
     */
    protected function successResponse($data = null, string $message = 'Success', int $statusCode = Response::HTTP_OK): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];

        if ($data !== null) {
            // Handle Laravel Resources
            if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
                return $data->additional($response)->response()->setStatusCode($statusCode);
            }
            
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Send a created response.
     * 
     * @param mixed $data The created resource data
     * @param string $message The success message
     * @return JsonResponse
     */
    protected function createdResponse($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->successResponse($data, $message, Response::HTTP_CREATED);
    }

    /**
     * Send an updated response.
     * 
     * @param mixed $data The updated resource data
     * @param string $message The success message
     * @return JsonResponse
     */
    protected function updatedResponse($data = null, string $message = 'Resource updated successfully'): JsonResponse
    {
        return $this->successResponse($data, $message, Response::HTTP_OK);
    }

    /**
     * Send a deleted response.
     * 
     * @param string $message The success message
     * @return JsonResponse
     */
    protected function deletedResponse(string $message = 'Resource deleted successfully'): JsonResponse
    {
        return $this->successResponse(null, $message, Response::HTTP_OK);
    }

    /**
     * Send an error response.
     * 
     * @param string $message The error message
     * @param int $statusCode The HTTP status code
     * @param string|null $errorCode The error code
     * @param array $errors Additional error data
     * @return JsonResponse
     */
    protected function errorResponse(
        string $message = 'An error occurred',
        int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR,
        ?string $errorCode = null,
        array $errors = []
    ): JsonResponse {
        $response = [
            'success' => false,
            'error' => $errorCode ?? 'ERROR',
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Send a validation error response.
     * 
     * @param array $errors The validation errors
     * @param string $message The error message
     * @return JsonResponse
     */
    protected function validationErrorResponse(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_UNPROCESSABLE_ENTITY, 'VALIDATION_ERROR', $errors);
    }

    /**
     * Send a not found error response.
     * 
     * @param string $message The error message
     * @param string $resource The resource name
     * @return JsonResponse
     */
    protected function notFoundResponse(string $message = 'Resource not found', string $resource = 'Resource'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_NOT_FOUND, 'NOT_FOUND', [
            'resource' => $resource
        ]);
    }

    /**
     * Send an unauthorized error response.
     * 
     * @param string $message The error message
     * @return JsonResponse
     */
    protected function unauthorizedResponse(string $message = 'Authentication required'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_UNAUTHORIZED, 'UNAUTHORIZED');
    }

    /**
     * Send a forbidden error response.
     * 
     * @param string $message The error message
     * @return JsonResponse
     */
    protected function forbiddenResponse(string $message = 'Access denied'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_FORBIDDEN, 'FORBIDDEN');
    }

    /**
     * Send a conflict error response.
     * 
     * @param string $message The error message
     * @param array $conflicts Conflict details
     * @return JsonResponse
     */
    protected function conflictResponse(string $message = 'Resource conflict', array $conflicts = []): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_CONFLICT, 'CONFLICT', $conflicts);
    }

    /**
     * Send a too many requests error response.
     * 
     * @param string $message The error message
     * @param int $retryAfter Seconds to wait before retrying
     * @return JsonResponse
     */
    protected function rateLimitResponse(string $message = 'Too many requests', int $retryAfter = 60): JsonResponse
    {
        $response = $this->errorResponse($message, Response::HTTP_TOO_MANY_REQUESTS, 'RATE_LIMIT_EXCEEDED', [
            'retry_after' => $retryAfter
        ]);

        return $response->header('Retry-After', $retryAfter);
    }

    /**
     * Send a paginated response.
     * 
     * @param ResourceCollection $collection The paginated resource collection
     * @param string $message The success message
     * @return JsonResponse
     */
    protected function paginatedResponse(ResourceCollection $collection, string $message = 'Data retrieved successfully'): JsonResponse
    {
        return $collection->additional([
            'success' => true,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ])->response();
    }

    /**
     * Send a no content response.
     * 
     * @return JsonResponse
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Send a method not allowed response.
     * 
     * @param array $allowedMethods The allowed HTTP methods
     * @param string $message The error message
     * @return JsonResponse
     */
    protected function methodNotAllowedResponse(array $allowedMethods = [], string $message = 'Method not allowed'): JsonResponse
    {
        $response = $this->errorResponse($message, Response::HTTP_METHOD_NOT_ALLOWED, 'METHOD_NOT_ALLOWED', [
            'allowed_methods' => $allowedMethods
        ]);

        if (!empty($allowedMethods)) {
            $response->header('Allow', implode(', ', $allowedMethods));
        }

        return $response;
    }
}