<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreditSalePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_credit_sale_persists_payment_and_customer_credit()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['role' => 'cashier']);

        $product = Product::create([
            'name' => 'Credit Product',
            'sku' => 'CP1',
            'stock' => 10,
            'selling_price' => 150,
        ]);

        // create a customer with default credit limit
        $customerId = DB::table('customers')->insertGetId([
            'name' => 'Test Customer',
            'phone' => '0700000000',
            'email' => 'c@test.local',
            'credit_limit' => 1000,
            'balance' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cart = [ ['product_id' => $product->id, 'qty' => 1, 'price' => 150] ];

        $payload = ['cart' => $cart, 'payment' => 'credit', 'customer_id' => $customerId];

        $response = $this->actingAs($user)->postJson('/cashier/checkout', $payload);
        $response->assertStatus(200)->assertJson(['success' => true]);

        $saleId = $response->json('sale_id');
        $this->assertNotNull($saleId);

        $this->assertDatabaseHas('sales', ['id' => $saleId, 'total' => 150, 'payment_type' => 'credit']);
        $this->assertDatabaseHas('sale_payments', ['sale_id' => $saleId, 'method' => 'credit', 'amount' => 150]);
        $this->assertDatabaseHas('customer_credits', ['sale_id' => $saleId, 'customer_id' => $customerId, 'amount' => 150]);

        // customer balance should have increased if customers.balance column exists
        if (DB::getSchemaBuilder()->hasTable('customers') && DB::getSchemaBuilder()->hasColumn('customers', 'balance')) {
            $balance = DB::table('customers')->where('id', $customerId)->value('balance');
            $this->assertEquals(150, (float)$balance);
        }
    }
}
