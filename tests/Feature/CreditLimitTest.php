<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreditLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_credit_limit_exceeded_rejected()
    {
        /** @var \App\Models\User $user */
    /** @var \App\Models\User $user */
    $user = User::factory()->create(['role' => 'cashier']);

        $product = Product::create([
            'name' => 'CreditProd',
            'sku' => 'CP1',
            'stock' => 10,
            'selling_price' => 100,
        ]);

        // customer with small credit limit
        $customerId = DB::table('customers')->insertGetId([
            'name' => 'Low Limit', 'phone' => '0701111', 'credit_limit' => 40, 'balance' => 0, 'created_at' => now(), 'updated_at' => now()
        ]);

        $cart = [ ['product_id' => $product->id, 'qty' => 1, 'price' => 50] ]; // total 50
        $payload = ['cart' => $cart, 'payment' => 'credit', 'customer_id' => $customerId];

        $response = $this->actingAs($user)->postJson('/cashier/checkout', $payload);
        $response->assertStatus(422);

        // ensure no sale or customer credit created
        $this->assertDatabaseMissing('sales', ['total' => 50]);
        $this->assertDatabaseMissing('customer_credits', ['customer_id' => $customerId, 'amount' => 50]);
    }
}
