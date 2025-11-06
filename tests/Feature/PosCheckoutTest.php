<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\User;

class PosCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_sale_and_decrements_stock()
    {
    /** @var \App\Models\User $user */
    $user = User::factory()->create(['role' => 'cashier']);

        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TP1',
            'stock' => 10,
            'selling_price' => 100,
        ]);

        $cart = [
            ['product_id' => $product->id, 'qty' => 2, 'price' => 100],
        ];

        $response = $this->actingAs($user)->postJson('/cashier/checkout', ['cart' => $cart, 'payment' => 'cash']);
        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('sales', ['total' => 200]);
        $this->assertDatabaseHas('sale_items', ['product_id' => $product->id, 'qty' => 2]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 8]);
    }
}
