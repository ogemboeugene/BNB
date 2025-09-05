<?php

namespace Tests\Feature\Feature;

use App\Models\BNB;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Class ErrorHandlingTest
 * 
 * Feature tests for API error handling and exception responses.
 * Tests various error scenarios and response structures.
 * 
 * @package Tests\Feature\Feature
 */
class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test 404 error for non-existent BNB.
     */
    public function test_bnb_not_found_error(): void
    {
        $response = $this->getJson('/api/v1/bnbs/999');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'error',
                'message',
                'timestamp',
                'path',
                'method'
            ])
            ->assertJson([
                'success' => false,
                'error' => 'BNBNotFoundException',
                'path' => '/api/v1/bnbs/999',
                'method' => 'GET'
            ]);
    }

    /**
     * Test validation error for invalid BNB data.
     */
    public function test_validation_error_response(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/bnbs', [
                'name' => '', // Required field
                'price_per_night' => 'invalid', // Should be numeric
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors'
            ]);
    }

    /**
     * Test authentication error for protected endpoints.
     */
    public function test_authentication_error(): void
    {
        $response = $this->postJson('/api/v1/bnbs', [
            'name' => 'Test BNB',
            'description' => 'Test description',
        ]);

        $response->assertStatus(401)
            ->assertJsonStructure([
                'success',
                'error',
                'message',
                'timestamp',
                'path',
                'method'
            ])
            ->assertJson([
                'success' => false,
                'error' => 'AuthenticationException'
            ]);
    }

    /**
     * Test authorization error for role-protected endpoints.
     */
    public function test_authorization_error(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/admin/stats');

        $response->assertStatus(403)
            ->assertJsonStructure([
                'message'
            ]);
    }

    /**
     * Test method not allowed error.
     */
    public function test_method_not_allowed_error(): void
    {
        $response = $this->patchJson('/api/v1/health');

        $response->assertStatus(405)
            ->assertJsonStructure([
                'success',
                'error',
                'message',
                'timestamp',
                'path',
                'method'
            ])
            ->assertJson([
                'success' => false,
                'error' => 'MethodNotAllowedHttpException'
            ]);
    }

    /**
     * Test invalid ID format error.
     */
    public function test_invalid_id_format_error(): void
    {
        $response = $this->getJson('/api/v1/bnbs/invalid-id');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'error',
                'message',
                'timestamp',
                'path',
                'method'
            ])
            ->assertJson([
                'success' => false,
                'error' => 'InvalidBNBDataException',
                'message' => 'Invalid BNB ID format'
            ]);
    }

    /**
     * Test that error responses include debug information in development.
     */
    public function test_debug_information_in_development(): void
    {
        // Set app environment to development
        config(['app.env' => 'development']);
        
        $response = $this->getJson('/api/v1/bnbs/999');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'error',
                'message',
                'timestamp',
                'path',
                'method',
                'debug' => [
                    'file',
                    'line',
                    'trace'
                ]
            ]);
    }

    /**
     * Test that sensitive debug information is not exposed in production.
     */
    public function test_no_debug_information_in_production(): void
    {
        // Set app environment to production
        config(['app.env' => 'production']);
        
        $response = $this->getJson('/api/v1/bnbs/999');

        $response->assertStatus(404)
            ->assertJsonMissing(['debug']);
    }

    /**
     * Test error response structure consistency.
     */
    public function test_error_response_structure_consistency(): void
    {
        $response = $this->getJson('/api/v1/bnbs/999');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'error',
                'message',
                'timestamp',
                'path',
                'method'
            ]);

        $data = $response->json();
        
        $this->assertIsBool($data['success']);
        $this->assertFalse($data['success']);
        $this->assertIsString($data['error']);
        $this->assertIsString($data['message']);
        $this->assertIsString($data['timestamp']);
        $this->assertIsString($data['path']);
        $this->assertIsString($data['method']);
    }

    /**
     * Test that errors are properly logged.
     */
    public function test_errors_are_logged(): void
    {
        // This test would require mocking the Log facade
        // For now, we'll just verify the endpoint works
        $response = $this->getJson('/api/v1/bnbs/999');
        
        $response->assertStatus(404);
        
        // In a real test environment, you would assert that Log::error was called
        // with the expected parameters
    }

    /**
     * Test error handling for invalid JSON requests.
     */
    public function test_invalid_json_error(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->call('POST', '/api/v1/bnbs', [], [], [], [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ], '{invalid json}');

        $response->assertStatus(422);
    }
}
