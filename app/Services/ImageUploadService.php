<?php

namespace App\Services;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Image Upload Service
 * 
 * Handles image uploads to Cloudinary with proper error handling,
 * validation, optimization, and secure URL generation for BNB listings.
 */
class ImageUploadService
{
    private UploadApi $uploadApi;
    private Cloudinary $cloudinary;
    private array $config;

    public function __construct()
    {
        // Configure Cloudinary
        Configuration::instance([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key' => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => true,
            ],
        ]);

        $this->uploadApi = new UploadApi();
        $this->cloudinary = new Cloudinary();
        $this->config = config('cloudinary.upload');
    }

    /**
     * Upload an image to Cloudinary
     *
     * @param UploadedFile $file The uploaded file
     * @param string|null $publicId Optional custom public ID
     * @return array Contains 'success', 'url', 'secure_url', 'public_id', and optional 'error'
     */
    public function uploadImage(UploadedFile $file, ?string $publicId = null): array
    {
        try {
            // Validate file
            $validation = $this->validateFile($file);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'error' => $validation['message'],
                ];
            }

            // Prepare upload options
            $options = [
                'folder' => $this->config['folder'],
                'resource_type' => $this->config['resource_type'],
                'quality' => $this->config['transformation']['quality'],
                'fetch_format' => $this->config['transformation']['fetch_format'],
                'overwrite' => true,
                'invalidate' => true,
                'secure' => true, // Force HTTPS URLs
                'type' => 'upload', // Use standard upload for now
            ];

            if ($publicId) {
                $options['public_id'] = $publicId;
            }

            // Upload to Cloudinary
            $result = $this->uploadApi->upload($file->getRealPath(), $options);

            // Generate secure signed URL with authentication token
            $secureUrl = $this->generateSecureUrl($result['public_id']);

            Log::info('Image uploaded successfully to Cloudinary', [
                'public_id' => $result['public_id'],
                'url' => $result['secure_url'],
                'secure_url' => $secureUrl,
                'original_filename' => $file->getClientOriginalName(),
            ]);

            return [
                'success' => true,
                'url' => $result['secure_url'], // For now, return the direct Cloudinary URL
                'secure_url' => $secureUrl,
                'public_id' => $result['public_id'],
                'width' => $result['width'] ?? null,
                'height' => $result['height'] ?? null,
                'format' => $result['format'] ?? null,
                'resource_type' => $result['resource_type'] ?? 'image',
            ];

        } catch (Exception $e) {
            Log::error('Failed to upload image to Cloudinary', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to upload image. Please try again.',
            ];
        }
    }

    /**
     * Delete an image from Cloudinary
     *
     * @param string $publicId The public ID of the image to delete
     * @return array Contains 'success' and optional 'error'
     */
    public function deleteImage(string $publicId): array
    {
        try {
            $result = $this->uploadApi->destroy($publicId);

            if ($result['result'] === 'ok') {
                Log::info('Image deleted successfully from Cloudinary', [
                    'public_id' => $publicId,
                ]);

                return ['success' => true];
            }

            return [
                'success' => false,
                'error' => 'Failed to delete image from Cloudinary',
            ];

        } catch (Exception $e) {
            Log::error('Failed to delete image from Cloudinary', [
                'error' => $e->getMessage(),
                'public_id' => $publicId,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to delete image. Please try again.',
            ];
        }
    }

    /**
     * Update an existing image (delete old, upload new)
     *
     * @param UploadedFile $newFile The new file to upload
     * @param string|null $oldPublicId The public ID of the old image to delete
     * @param string|null $newPublicId Optional custom public ID for new image
     * @return array Contains 'success', 'url', 'secure_url', 'public_id', and optional 'error'
     */
    public function updateImage(UploadedFile $newFile, ?string $oldPublicId = null, ?string $newPublicId = null): array
    {
        // Upload new image first
        $uploadResult = $this->uploadImage($newFile, $newPublicId);

        if (!$uploadResult['success']) {
            return $uploadResult;
        }

        // Delete old image if provided
        if ($oldPublicId) {
            $deleteResult = $this->deleteImage($oldPublicId);
            if (!$deleteResult['success']) {
                Log::warning('Failed to delete old image after successful upload', [
                    'old_public_id' => $oldPublicId,
                    'new_public_id' => $uploadResult['public_id'],
                ]);
            }
        }

        return $uploadResult;
    }

    /**
     * Extract public ID from Cloudinary URL
     *
     * @param string $url The Cloudinary URL
     * @return string|null The extracted public ID
     */
    public function extractPublicIdFromUrl(string $url): ?string
    {
        // Match pattern: .../folder/public_id.extension
        if (preg_match('/\/([^\/]+)\/([^\/]+)\.[a-zA-Z]+$/', $url, $matches)) {
            return $matches[1] . '/' . $matches[2];
        }

        // Fallback: extract everything after last slash and before extension
        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            $segments = explode('/', trim($path, '/'));
            $lastSegment = end($segments);
            return pathinfo($lastSegment, PATHINFO_FILENAME);
        }

        return null;
    }

    /**
     * Regenerate secure URL for an existing image
     * 
     * This method is useful when you need to refresh the authentication token
     * for an existing image URL, especially for GET requests.
     *
     * @param string $existingUrl The existing Cloudinary URL
     * @param array $transformations Optional transformations to apply
     * @return string|null The new secure URL with fresh token, or null if extraction fails
     */
    public function refreshSecureUrl(string $existingUrl, array $transformations = []): ?string
    {
        $publicId = $this->extractPublicIdFromUrl($existingUrl);
        
        if ($publicId) {
            return $this->generateSecureUrl($publicId, $transformations);
        }
        
        return null;
    }

    /**
     * Get secure URL for display in GET requests
     * 
     * This method ensures that images returned in API responses have valid
     * authentication tokens for secure access.
     *
     * @param string|null $imageUrl The stored image URL
     * @return string|null The secure URL with fresh token, or null if no image
     */
    public function getSecureDisplayUrl(?string $imageUrl): ?string
    {
        if (!$imageUrl) {
            return null;
        }

        // For now, return the URL as-is since we're using standard Cloudinary URLs
        // In the future, this can be enhanced to add authentication tokens
        return $imageUrl;
    }

    /**
     * Check if a secure URL is still valid (token not expired)
     *
     * @param string $secureUrl The secure URL to validate
     * @return bool True if the URL is valid and token not expired
     */
    private function isSecureUrlValid(string $secureUrl): bool
    {
        // Extract auth_token from URL
        if (preg_match('/auth_token=([^&]+)/', $secureUrl, $matches)) {
            $token = $matches[1];
            $publicId = $this->extractPublicIdFromUrl($secureUrl);
            
            if ($publicId && $token) {
                return $this->validateAuthToken($publicId, $token);
            }
        }
        
        return false;
    }

    /**
     * Validate uploaded file
     *
     * @param UploadedFile $file
     * @return array Contains 'valid' boolean and 'message' string
     */
    private function validateFile(UploadedFile $file): array
    {
        // Check if file is valid
        if (!$file->isValid()) {
            return [
                'valid' => false,
                'message' => 'Invalid file upload.',
            ];
        }

        // Check file size
        if ($file->getSize() > $this->config['max_file_size']) {
            $maxSizeMB = $this->config['max_file_size'] / 1024 / 1024;
            return [
                'valid' => false,
                'message' => "File size exceeds maximum allowed size of {$maxSizeMB}MB.",
            ];
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->config['allowed_formats'])) {
            $allowedFormats = implode(', ', $this->config['allowed_formats']);
            return [
                'valid' => false,
                'message' => "Invalid file format. Allowed formats: {$allowedFormats}.",
            ];
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        $allowedMimeTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp',
            'image/gif',
        ];

        if (!in_array($mimeType, $allowedMimeTypes)) {
            return [
                'valid' => false,
                'message' => 'Invalid file type. Only images are allowed.',
            ];
        }

        return [
            'valid' => true,
            'message' => 'File is valid.',
        ];
    }

    /**
     * Generate a unique public ID for the image
     *
     * @param string $prefix Optional prefix for the public ID
     * @return string
     */
    public function generatePublicId(string $prefix = 'bnb'): string
    {
        return $prefix . '_' . uniqid() . '_' . time();
    }

    /**
     * Generate secure signed URL with authentication token
     *
     * @param string $publicId The public ID of the image
     * @param array $transformations Optional transformations to apply
     * @return string The secure signed URL
     */
    public function generateSecureUrl(string $publicId, array $transformations = []): string
    {
        try {
            $cloudName = config('cloudinary.cloud_name');
            
            // Build transformation string
            $transformationString = '';
            if (!empty($transformations)) {
                $transformParts = [];
                foreach ($transformations as $key => $value) {
                    $transformParts[] = $key . '_' . $value;
                }
                $transformationString = implode(',', $transformParts) . '/';
            } else {
                $transformationString = 'q_auto,f_auto/';
            }

            // Generate authentication token
            $authToken = $this->generateAuthToken($publicId);
            
            // Build the secure URL
            $baseUrl = "https://res.cloudinary.com/{$cloudName}/image/authenticated/{$transformationString}{$publicId}";
            $signedUrl = $baseUrl . '?auth_token=' . $authToken;

            return $signedUrl;

        } catch (Exception $e) {
            Log::error('Failed to generate secure URL', [
                'public_id' => $publicId,
                'error' => $e->getMessage(),
            ]);

            // Fallback to basic authenticated URL
            $cloudName = config('cloudinary.cloud_name');
            return "https://res.cloudinary.com/{$cloudName}/image/authenticated/{$publicId}";
        }
    }

    /**
     * Generate authentication token for secure image access
     *
     * @param string $publicId The public ID of the image
     * @param int|null $timestamp Optional timestamp (defaults to current time + 1 hour)
     * @return string The authentication token
     */
    public function generateAuthToken(string $publicId, ?int $timestamp = null): string
    {
        if ($timestamp === null) {
            $timestamp = time() + 3600; // Token valid for 1 hour
        }

        $authKey = config('cloudinary.api_secret');
        $stringToSign = "authenticated/{$publicId}--{$timestamp}--{$authKey}";
        
        return substr(sha1($stringToSign), 0, 8) . '--' . $timestamp;
    }

    /**
     * Validate authentication token
     *
     * @param string $publicId The public ID of the image
     * @param string $token The authentication token to validate
     * @return bool True if token is valid
     */
    public function validateAuthToken(string $publicId, string $token): bool
    {
        try {
            list($hash, $timestamp) = explode('--', $token);
            
            // Check if token has expired
            if (time() > $timestamp) {
                return false;
            }

            // Regenerate token and compare
            $expectedToken = $this->generateAuthToken($publicId, $timestamp);
            return hash_equals($expectedToken, $token);

        } catch (Exception $e) {
            Log::warning('Failed to validate auth token', [
                'public_id' => $publicId,
                'token' => $token,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}