<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiException
 * 
 * Custom exception class for API-specific errors.
 * Provides structured error responses with proper HTTP status codes.
 * 
 * @package App\Exceptions
 */
class ApiException extends Exception
{
    /**
     * The HTTP status code.
     */
    protected int $statusCode;

    /**
     * The error code identifier.
     */
    protected string $errorCode;

    /**
     * Additional error data.
     */
    protected array $errorData;

    /**
     * ApiException constructor.
     * 
     * @param string $message The error message
     * @param int $statusCode The HTTP status code
     * @param string|null $errorCode The error code identifier
     * @param array $errorData Additional error data
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message = 'An API error occurred',
        int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR,
        ?string $errorCode = null,
        array $errorData = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode ?? class_basename($this);
        $this->errorData = $errorData;
    }

    /**
     * Get the HTTP status code.
     * 
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the error code.
     * 
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Get additional error data.
     * 
     * @return array
     */
    public function getErrorData(): array
    {
        return $this->errorData;
    }

    /**
     * Convert the exception to a JSON response.
     * 
     * @return JsonResponse
     */
    public function toResponse(): JsonResponse
    {
        $response = [
            'success' => false,
            'error' => $this->getErrorCode(),
            'message' => $this->getMessage(),
            'timestamp' => now()->toISOString(),
        ];

        // Add additional error data if present
        if (!empty($this->errorData)) {
            $response['data'] = $this->errorData;
        }

        // Add debug information in non-production environments
        if (app()->environment(['local', 'development', 'testing'])) {
            $response['debug'] = [
                'file' => $this->getFile(),
                'line' => $this->getLine(),
                'trace' => collect($this->getTrace())
                    ->take(5)
                    ->map(function ($trace) {
                        return [
                            'file' => $trace['file'] ?? 'unknown',
                            'line' => $trace['line'] ?? 'unknown',
                            'function' => $trace['function'] ?? 'unknown',
                            'class' => $trace['class'] ?? null,
                        ];
                    }),
            ];
        }

        return response()->json($response, $this->statusCode);
    }

    /**
     * Create a validation error exception.
     * 
     * @param array $errors The validation errors
     * @param string $message The error message
     * @return static
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): static
    {
        return new static(
            $message,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'VALIDATION_ERROR',
            ['errors' => $errors]
        );
    }

    /**
     * Create an authentication error exception.
     * 
     * @param string $message The error message
     * @return static
     */
    public static function authenticationError(string $message = 'Authentication required'): static
    {
        return new static(
            $message,
            Response::HTTP_UNAUTHORIZED,
            'AUTHENTICATION_ERROR'
        );
    }

    /**
     * Create an authorization error exception.
     * 
     * @param string $message The error message
     * @return static
     */
    public static function authorizationError(string $message = 'Access denied'): static
    {
        return new static(
            $message,
            Response::HTTP_FORBIDDEN,
            'AUTHORIZATION_ERROR'
        );
    }

    /**
     * Create a not found error exception.
     * 
     * @param string $resource The resource that was not found
     * @param string|null $message Custom error message
     * @return static
     */
    public static function notFound(string $resource = 'Resource', ?string $message = null): static
    {
        $message = $message ?? "{$resource} not found";
        
        return new static(
            $message,
            Response::HTTP_NOT_FOUND,
            'NOT_FOUND_ERROR',
            ['resource' => $resource]
        );
    }

    /**
     * Create a bad request error exception.
     * 
     * @param string $message The error message
     * @param array $data Additional error data
     * @return static
     */
    public static function badRequest(string $message = 'Bad request', array $data = []): static
    {
        return new static(
            $message,
            Response::HTTP_BAD_REQUEST,
            'BAD_REQUEST_ERROR',
            $data
        );
    }

    /**
     * Create a server error exception.
     * 
     * @param string $message The error message
     * @param array $data Additional error data
     * @return static
     */
    public static function serverError(string $message = 'Internal server error', array $data = []): static
    {
        return new static(
            $message,
            Response::HTTP_INTERNAL_SERVER_ERROR,
            'SERVER_ERROR',
            $data
        );
    }

    /**
     * Create a rate limit error exception.
     * 
     * @param string $message The error message
     * @param int $retryAfter Seconds to wait before retrying
     * @return static
     */
    public static function rateLimitError(string $message = 'Too many requests', int $retryAfter = 60): static
    {
        return new static(
            $message,
            Response::HTTP_TOO_MANY_REQUESTS,
            'RATE_LIMIT_ERROR',
            ['retry_after' => $retryAfter]
        );
    }
}