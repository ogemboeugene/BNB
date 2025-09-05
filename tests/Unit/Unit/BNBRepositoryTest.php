<?php

namespace Tests\Unit\Unit;

use App\Models\BNB;
use App\Repositories\BNBRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BNBRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private BNBRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new BNBRepository(new BNB());
    }

    public function test_can_create_bnb(): void
    {
        $data = [
            'name' => 'Test BNB',
            'location' => 'Test Location',
            'price_per_night' => 100.00,
            'availability' => true
        ];

        $bnb = $this->repository->create($data);

        $this->assertInstanceOf(BNB::class, $bnb);
        $this->assertEquals('Test BNB', $bnb->name);
    }

    public function test_can_find_bnb_by_id(): void
    {
        $bnb = BNB::factory()->create();
        
        $found = $this->repository->findById($bnb->id);
        
        $this->assertInstanceOf(BNB::class, $found);
        $this->assertEquals($bnb->id, $found->id);
    }

    public function test_can_update_bnb(): void
    {
        $bnb = BNB::factory()->create(['name' => 'Original Name']);
        
        $updated = $this->repository->update($bnb->id, ['name' => 'Updated Name']);
        
        $this->assertEquals('Updated Name', $updated->name);
    }

    public function test_can_delete_bnb(): void
    {
        $bnb = BNB::factory()->create();
        
        $result = $this->repository->delete($bnb->id);
        
        $this->assertTrue($result);
        $this->assertSoftDeleted($bnb);
    }

    public function test_can_get_with_filters(): void
    {
        BNB::factory()->create(['location' => 'Paris', 'availability' => true]);
        BNB::factory()->create(['location' => 'London', 'availability' => false]);
        
        $results = $this->repository->getWithFilters(['location' => 'Paris']);
        
        $this->assertCount(1, $results);
    }
}
