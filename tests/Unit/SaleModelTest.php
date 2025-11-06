<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Sale;

class SaleModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_factory_creates_items_and_total_is_correct()
    {
        $sale = Sale::factory()->create();

        $this->assertNotNull($sale->id);
        $items = collect($sale->items);
        $this->assertTrue($items->count() >= 1);

        $calculated = $items->reduce(function ($carry, $item) {
            return $carry + ($item->price * $item->qty);
        }, 0);

        $this->assertEqualsWithDelta($calculated, $sale->total, 0.001);
    }
}
