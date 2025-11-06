<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MixedPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_mixed_payment_creates_sale_payments_and_records_credit_portion()
    {
        /** @var \App\Models\User $user */
    /** @var \App\Models\User $user */
    $user = User::factory()->create(['role' => 'cashier']);

        $product = Product::create([
            'name' => 'MixProd',
            'sku' => 'MP1',
            'stock' => 10,
            'selling_price' => 100,
        ]);

        // create customer
        $customerId = DB::table('customers')->insertGetId([
            'name' => 'Test Cust', 'phone' => '0700000', 'credit_limit' => 500, 'balance' => 0, 'created_at' => now(), 'updated_at' => now()
        ]);

        $cart = [ ['product_id' => $product->id, 'qty' => 2, 'price' => 100] ]; // total 200

        $parts = [ ['method' => 'cash', 'amount' => 100], ['method' => 'mobile_money', 'amount' => 50], ['method' => 'credit', 'amount' => 50] ];

        $payload = ['cart' => $cart, 'payment_parts' => $parts, 'customer_id' => $customerId];

        $response = $this->actingAs($user)->postJson('/cashier/checkout', $payload);
        $response->assertStatus(200)->assertJson(['success' => true]);

        // sale created and stock decremented
        $this->assertDatabaseHas('sales', ['total' => 200]);
        $this->assertDatabaseHas('sale_items', ['product_id' => $product->id, 'qty' => 2]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 8]);

        // payments persisted
        $this->assertDatabaseHas('sale_payments', ['method' => 'cash', 'amount' => 100]);
        $this->assertDatabaseHas('sale_payments', ['method' => 'mobile_money', 'amount' => 50]);
        $this->assertDatabaseHas('sale_payments', ['method' => 'credit', 'amount' => 50]);

        // credit recorded
        $this->assertDatabaseHas('customer_credits', ['customer_id' => $customerId, 'amount' => 50]);
        $this->assertDatabaseHas('customers', ['id' => $customerId, 'balance' => 50]);
    }
}
