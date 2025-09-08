<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register custom middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'swagger' => \App\Http\Middleware\SwaggerMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Configure API exception handling
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            // Only handle API requests
            if (!$request->is('api/*')) {
                return null;
            }

            // Import required classes
            $statusCode = $response->getStatusCode();
            $message = $exception->getMessage() ?: 'An error occurred';
            $errorCode = class_basename($exception);

            // Create structured error response
            $errorResponse = [
                'success' => false,
                'error' => $errorCode,
                'message' => $message,
                'timestamp' => now()->toISOString(),
                'path' => $request->getPathInfo(),
                'method' => $request->getMethod(),
            ];

            // Add debug information in non-production environments
            if (app()->environment(['local', 'development', 'testing'])) {
                $errorResponse['debug'] = [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => collect($exception->getTrace())
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

            // Handle specific exception types
            if ($exception instanceof \App\Exceptions\ApiException) {
                $errorResponse['message'] = $exception->getMessage();
                $errorResponse['error_code'] = $exception->getErrorCode();
                $statusCode = $exception->getStatusCode();
            } elseif ($exception instanceof \App\Exceptions\BNBNotFoundException) {
                $errorResponse['message'] = $exception->getMessage();
                $statusCode = 404;
            } elseif ($exception instanceof \App\Exceptions\InvalidBNBDataException) {
                $errorResponse['message'] = $exception->getMessage();
                $errorResponse['errors'] = $exception->getErrors();
                $statusCode = 422;
            } elseif ($exception instanceof \App\Exceptions\BNBAccessDeniedException) {
                $errorResponse['message'] = $exception->getMessage();
                $statusCode = 403;
            } elseif ($exception instanceof \Illuminate\Validation\ValidationException) {
                $errorResponse['errors'] = $exception->errors();
                $errorResponse['error'] = 'Validation failed';
                $errorResponse['message'] = 'The given data was invalid.';
                $statusCode = 422;
            } elseif ($exception instanceof \Illuminate\Auth\AuthenticationException) {
                $errorResponse['message'] = 'Authentication required';
                $statusCode = 401;
            } elseif ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
                $errorResponse['message'] = 'Access denied';
                $statusCode = 403;
            } elseif ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                $errorResponse['message'] = 'Resource not found';
                $statusCode = 404;
            } elseif ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                $errorResponse['message'] = 'Method not allowed';
                $statusCode = 405;
            } elseif ($exception instanceof \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException) {
                $errorResponse['message'] = 'Too many requests';
                $statusCode = 429;
            }

            // Log the error
            \Log::error('API Exception', [
                'exception' => $errorCode,
                'message' => $message,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'url' => $request->fullUrl(),
                'method' => $request->getMethod(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json($errorResponse, $statusCode);
        });
    })->create();
