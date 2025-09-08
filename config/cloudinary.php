<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the configuration settings for Cloudinary
    | image and video management service.
    |
    */

    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    'api_key' => env('CLOUDINARY_API_KEY'),
    'api_secret' => env('CLOUDINARY_API_SECRET'),
    'cloudinary_url' => env('CLOUDINARY_URL'),

    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for image uploads
    |
    */
    'upload' => [
        'folder' => 'bnb-images',
        'resource_type' => 'image',
        'allowed_formats' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        'max_file_size' => 10485760, // 10MB in bytes
        'transformation' => [
            'quality' => 'auto',
            'fetch_format' => 'auto',
        ],
        'secure' => true, // Always use HTTPS URLs
        'sign_url' => true, // Generate signed URLs for security
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for secure image access
    |
    */
    'security' => [
        'auth_token' => [
            'key' => env('CLOUDINARY_API_SECRET'),
            'duration' => 3600, // Token validity in seconds (1 hour)
            'start_time' => null, // null means current time
        ],
        'delivery_type' => 'authenticated', // Use authenticated delivery
    ],
];