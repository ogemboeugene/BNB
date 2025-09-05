<?php

namespace Tests\Unit\Unit;

use App\Models\BNB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BNBModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_bnb_can_be_created(): void
    {
        $bnb = BNB::factory()->create([
            'name' => 'Test BNB',
            'location' => 'Test Location',
            'price_per_night' => 100.00
        ]);

        $this->assertDatabaseHas('bnbs', [
            'name' => 'Test BNB',
            'location' => 'Test Location',
            'price_per_night' => 100.00
        ]);
    }

    public function test_bnb_has_fillable_attributes(): void
    {
        $bnb = new BNB();
        $expected = ['name', 'location', 'price_per_night', 'availability'];
        
        $this->assertEquals($expected, $bnb->getFillable());
    }

    public function test_bnb_uses_soft_deletes(): void
    {
        $bnb = BNB::factory()->create();
        $bnb->delete();
        
        $this->assertSoftDeleted($bnb);
    }

    public function test_bnb_price_is_cast_to_decimal(): void
    {
        $bnb = BNB::factory()->create(['price_per_night' => '99.99']);
        
        $this->assertEquals('99.99', $bnb->price_per_night);
        $this->assertIsString($bnb->price_per_night); // decimal cast returns string
    }

    public function test_bnb_availability_is_cast_to_boolean(): void
    {
        $bnb = BNB::factory()->create(['availability' => 1]);
        
        $this->assertIsBool($bnb->availability);
        $this->assertTrue($bnb->availability);
    }
}
