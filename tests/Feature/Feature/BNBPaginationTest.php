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
                        'description',
                        'location',
                        'pricing',
                        'capacity',
                        'features',
                        'availability',
                        'media',
                        'metadata',
                        'links'
                    ]
                ],
                'summary' => [
                    'total_items',
                    'current_page_items',
                    'available_count',
                    'unavailable_count',
                    'average_price',
                    'price_range'
                ],
                'filters',
                'meta',
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next'
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
        // Create available and unavailable BNBs
        BNB::factory()->count(10)->create(['is_available' => true]);
        BNB::factory()->count(5)->create(['is_available' => false]);

        $response = $this->getJson('/api/v1/bnbs?availability=true&per_page=5');

        $response->assertStatus(200);
        
        // Should return only available BNBs
        $data = $response->json('data');
        $this->assertCount(5, $data);
        
        foreach ($data as $bnb) {
            $this->assertTrue($bnb['availability']['is_available']);
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
        $prices = array_column(array_column($data, 'pricing'), 'price_per_night');
        
        // Prices should be in ascending order
        $this->assertEquals($prices, array_values(array_sort($prices)));
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
        $this->assertNull($meta['prev_page_url']);
        $this->assertNotNull($meta['next_page_url']);

        // Test middle page
        $response = $this->getJson('/api/v1/bnbs?per_page=10&page=2');
        $response->assertStatus(200);
        
        $meta = $response->json('meta');
        $this->assertEquals(2, $meta['current_page']);
        $this->assertNotNull($meta['prev_page_url']);
        $this->assertNotNull($meta['next_page_url']);

        // Test last page
        $response = $this->getJson('/api/v1/bnbs?per_page=10&page=3');
        $response->assertStatus(200);
        
        $meta = $response->json('meta');
        $this->assertEquals(3, $meta['current_page']);
        $this->assertNotNull($meta['prev_page_url']);
        $this->assertNull($meta['next_page_url']);
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
                'summary' => [
                    'total_items',
                    'current_page_items',
                    'available_count',
                    'unavailable_count',
                    'average_price',
                    'price_range' => ['min', 'max']
                ],
                'filters' => [
                    'applied',
                    'suggestions'
                ],
                'meta' => [
                    'version',
                    'timestamp',
                    'resource_type'
                ],
                'links' => [
                    'self',
                    'create'
                ]
            ]);
    }
}
