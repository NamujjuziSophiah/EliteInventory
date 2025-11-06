<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Sale;

class CheckoutPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_persists_sale_and_decrements_stock()
    {
    /** @var \App\Models\User $cashier */
    $cashier = User::factory()->create(['role' => 'cashier']);

        // create product
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TP123',
            'selling_price' => 10.00,
            'cost_price' => 5.00,
            'stock' => 10,
        ]);

        $cart = [[
            'product_id' => $product->id,
            'qty' => 2,
            'price' => 10.00,
        ]];

        $this->actingAs($cashier)
            ->post('/cashier/checkout', ['cart' => $cart, 'payment' => 'cash'])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('sales', ['total' => 20.00]);
        $this->assertDatabaseHas('sale_items', ['product_id' => $product->id, 'qty' => 2]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 8]);
    }
}
