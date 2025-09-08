<?php

use Illuminate\Support\Facades\Route;
use App\Services\ImageUploadService;

// Test route to verify Cloudinary configuration
Route::get('/test-cloudinary', function () {
    try {
        $imageUploadService = app(ImageUploadService::class);
        
        // Test generating a secure URL for a test image
        $testPublicId = 'bnb-images/test_image';
        $secureUrl = $imageUploadService->generateSecureUrl($testPublicId);
        
        return response()->json([
            'success' => true,
            'message' => 'Cloudinary configuration is working',
            'config' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key' => config('cloudinary.api_key'),
                'api_secret_set' => !empty(config('cloudinary.api_secret')),
            ],
            'test_secure_url' => $secureUrl,
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
        ]);
    }
});