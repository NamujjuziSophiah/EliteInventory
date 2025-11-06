<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;

class ManagerSaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_cannot_create_sale_and_stock_not_changed()
    {
    /** @var \App\Models\User $manager */
    $manager = User::factory()->create(['role' => 'manager']);

        $product = Product::factory()->create(['stock' => 10, 'cost_price' => 5]);

        $payload = [
            'items' => [
                ['product_id' => $product->id, 'qty' => 2, 'price' => 10],
            ],
            'payment_type' => 'cash'
        ];

        // Managers should be forbidden from creating sales via manager routes
        // The store route under manager namespace is removed for managers; expect not found
        $this->actingAs($manager)
            ->post('/manager/sales', $payload)
            // Manager routes for sales are read-only; POST should be method-not-allowed (405)
            ->assertStatus(405);

        $this->assertDatabaseMissing('sales', ['total' => 20]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 10]);
    }
}
