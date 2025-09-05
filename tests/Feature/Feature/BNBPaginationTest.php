<?php

namespace Tests\Feature\Feature;

use App\Models\BNB;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Class BNBPaginationTest
 * 
 * Feature tests for BNB API pagination functionality.
 * Tests pagination parameters, response structure, and edge cases.
 * 
 * @package Tests\Feature\Feature
 */
class BNBPaginationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test basic pagination with default parameters.
     */
    public function test_basic_pagination_with_defaults(): void
    {
        // Create test BNBs
        BNB::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/bnbs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'location',
                        'price_per_night',
                        'availability',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ]
            ]);

        // Should return 15 items (default per_page)
        $this->assertCount(15, $response->json('data'));
        $this->assertEquals(25, $response->json('meta.total'));
        $this->assertEquals(15, $response->json('meta.per_page'));
        $this->assertEquals(1, $response->json('meta.current_page'));
        $this->assertEquals(2, $response->json('meta.last_page'));
    }

    /**
     * Test custom per_page parameter.
     */
    public function test_custom_per_page_parameter(): void
    {
        // Create test BNBs
        BNB::factory()->count(30)->create();

        $response = $this->getJson('/api/v1/bnbs?per_page=10');

        $response->assertStatus(200);
        
        // Should return 10 items
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(30, $response->json('meta.total'));
        $this->assertEquals(10, $response->json('meta.per_page'));
        $this->assertEquals(3, $response->json('meta.last_page'));
    }

    /**
     * Test maximum per_page limit.
     */
    public function test_maximum_per_page_limit(): void
    {
        $response = $this->getJson('/api/v1/bnbs?per_page=150');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    /**
     * Test pagination with filtering.
     */
    public function test_pagination_with_filtering(): void
    {
                // Create test data with different availability
        BNB::factory()->count(10)->create(['availability' => true]);
        BNB::factory()->count(5)->create(['availability' => false]);

        $response = $this->getJson('/api/v1/bnbs?availability=true');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(10, $data);

        // All should be available
        foreach ($data as $bnb) {
            $this->assertTrue($bnb['availability']);
        }
    }

    /**
     * Test pagination with sorting.
     */
    public function test_pagination_with_sorting(): void
    {
        // Create BNBs with different prices
        BNB::factory()->create(['price_per_night' => 100]);
        BNB::factory()->create(['price_per_night' => 200]);
        BNB::factory()->create(['price_per_night' => 150]);

        $response = $this->getJson('/api/v1/bnbs?sort_by=price_per_night&sort_direction=asc');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $prices = array_column($data, 'price_per_night');
        
        // Prices should be in ascending order
        $sortedPrices = $prices;
        sort($sortedPrices);
        $this->assertEquals($sortedPrices, $prices);
    }

    /**
     * Test pagination navigation links.
     */
    public function test_pagination_navigation_links(): void
    {
        BNB::factory()->count(25)->create();

        // Test first page
        $response = $this->getJson('/api/v1/bnbs?per_page=10&page=1');
        $response->assertStatus(200);
        
        $meta = $response->json('meta');
        $this->assertEquals(1, $meta['current_page']);
        $this->assertEquals(3, $meta['last_page']);

        // Test middle page
        $response = $this->getJson('/api/v1/bnbs?per_page=10&page=2');
        $response->assertStatus(200);
        
        $meta = $response->json('meta');
        $this->assertEquals(2, $meta['current_page']);

        // Test last page
        $response = $this->getJson('/api/v1/bnbs?per_page=10&page=3');
        $response->assertStatus(200);
        
        $meta = $response->json('meta');
        $this->assertEquals(3, $meta['current_page']);
    }

    /**
     * Test empty result pagination.
     */
    public function test_empty_result_pagination(): void
    {
        $response = $this->getJson('/api/v1/bnbs');

        $response->assertStatus(200);
        
        $this->assertCount(0, $response->json('data'));
        $this->assertEquals(0, $response->json('meta.total'));
        $this->assertEquals(1, $response->json('meta.current_page'));
        $this->assertEquals(1, $response->json('meta.last_page'));
    }

    /**
     * Test invalid page number.
     */
    public function test_invalid_page_number(): void
    {
        BNB::factory()->count(10)->create();

        // Request page beyond available pages
        $response = $this->getJson('/api/v1/bnbs?page=999');

        $response->assertStatus(200);
        
        // Should return empty data for non-existent page
        $this->assertCount(0, $response->json('data'));
    }

    /**
     * Test pagination response structure consistency.
     */
    public function test_pagination_response_structure_consistency(): void
    {
        BNB::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/bnbs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ]
            ]);

        // Verify data structure consistency
        $data = $response->json('data');
        $this->assertIsArray($data);
        
        if (count($data) > 0) {
            $this->assertArrayHasKey('id', $data[0]);
            $this->assertArrayHasKey('name', $data[0]);
            $this->assertArrayHasKey('location', $data[0]);
            $this->assertArrayHasKey('price_per_night', $data[0]);
            $this->assertArrayHasKey('availability', $data[0]);
        }
    }
}
