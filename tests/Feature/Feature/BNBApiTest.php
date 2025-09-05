<?php

namespace Tests\Feature\Feature;

use App\Models\BNB;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BNBApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_bnbs(): void
    {
        BNB::factory()->count(3)->create();
        
        $response = $this->getJson('/api/v1/bnbs');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'location', 'price_per_night', 'availability']
                ],
                'meta' => ['pagination']
            ]);
    }

    public function test_can_show_bnb(): void
    {
        $bnb = BNB::factory()->create();
        
        $response = $this->getJson("/api/v1/bnbs/{$bnb->id}");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'location', 'price_per_night', 'availability']
            ]);
    }

    public function test_can_create_bnb_when_authenticated(): void
    {
        $user = User::factory()->create();
        
        $data = [
            'name' => 'New BNB',
            'description' => 'Beautiful place',
            'location' => 'Paris',
            'price_per_night' => 150.00,
            'availability' => true
        ];
        
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/bnbs', $data);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'location', 'price_per_night']
            ]);
    }

    public function test_cannot_create_bnb_when_unauthenticated(): void
    {
        $data = [
            'name' => 'New BNB',
            'location' => 'Paris',
            'price_per_night' => 150.00
        ];
        
        $response = $this->postJson('/api/v1/bnbs', $data);
        
        $response->assertStatus(401);
    }

    public function test_validation_errors_on_create(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/bnbs', []);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'location', 'price_per_night']);
    }

    public function test_can_update_bnb_when_authenticated(): void
    {
        $user = User::factory()->create();
        $bnb = BNB::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/bnbs/{$bnb->id}", [
                'name' => 'Updated Name'
            ]);
        
        $response->assertStatus(200);
    }

    public function test_can_delete_bnb_when_authenticated(): void
    {
        $user = User::factory()->create();
        $bnb = BNB::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/bnbs/{$bnb->id}");
        
        $response->assertStatus(200);
    }

    public function test_returns_404_for_nonexistent_bnb(): void
    {
        $response = $this->getJson('/api/v1/bnbs/999');
        
        $response->assertStatus(404);
    }
}
