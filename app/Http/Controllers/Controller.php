<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

/**
 * @OA\Info(
 *     title="BNB Management API",
 *     version="1.0.0",
 *     description="Professional BNB Management System API with JWT Authentication"
 * )
 * @OA\Server(
 *     url="http://localhost:8000/api/v1",
 *     description="Local Development Server"
 * )
 * @OA\Server(
 *     url="https://f590492a3ec0.ngrok-free.app/api/v1",
 *     description="Public Tunnel (ngrok)"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
#[OA\Schema(
    schema: 'BNB',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Cozy Downtown Apartment'),
        new OA\Property(property: 'location', type: 'string', example: 'New York, NY'),
        new OA\Property(property: 'price_per_night', type: 'string', example: '150.00'),
        new OA\Property(property: 'availability', type: 'boolean', example: true),
        new OA\Property(property: 'image_url', type: 'string', nullable: true, example: 'https://res.cloudinary.com/demo/image/upload/sample.jpg'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-09-05T08:00:00.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2025-09-05T09:30:00.000000Z')
    ]
)]
#[OA\Schema(
    schema: 'User',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
        new OA\Property(property: 'role', type: 'string', enum: ['admin', 'user'], example: 'user'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-09-05T10:30:00.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2025-09-05T10:30:00.000000Z')
    ]
)]
#[OA\Schema(
    schema: 'Error',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'error', type: 'string', example: 'Not Found'),
        new OA\Property(property: 'message', type: 'string', example: 'The requested resource was not found'),
        new OA\Property(property: 'timestamp', type: 'string', format: 'date-time', example: '2025-09-05T12:00:00.000000Z')
    ]
)]
#[OA\Schema(
    schema: 'ValidationError',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'error', type: 'string', example: 'Validation failed'),
        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(
                type: 'array',
                items: new OA\Items(type: 'string')
            ),
            example: [
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.']
            ]
        )
    ]
)]
abstract class Controller
{
    //
}
