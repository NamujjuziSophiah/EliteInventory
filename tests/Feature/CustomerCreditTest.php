<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CustomerCreditTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_and_settle_customer_credit_updates_balance()
    {
    /** @var \App\Models\User $user */
    $user = User::factory()->create(['role' => 'manager']);

    $customer = DB::table('customers')->insertGetId([
            'name' => 'ACME Corp',
            'phone' => '0777000000',
            'credit_limit' => 1000,
            'balance' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'customer_id' => $customer,
            'amount' => 250,
            'due_date' => now()->addDays(7)->toDateString(),
        ];

        $this->actingAs($user)->postJson('/cashier/credits/'.$customer, $payload);

        // Use controller store directly via endpoint we created
        $response = $this->actingAs($user)->postJson('/cashier/credits/'.$customer, $payload);

        // Our controller inserts into customer_credits; check DB
        $this->assertDatabaseHas('customer_credits', ['customer_id' => $customer, 'amount' => 250]);
        $this->assertDatabaseHas('customers', ['id' => $customer, 'balance' => 250]);

        // Settle the credit
    $credit = DB::table('customer_credits')->where('customer_id', $customer)->first();
        $this->actingAs($user)->postJson('/cashier/credits/'.$credit->id.'/settle');

        $this->assertDatabaseHas('customer_credits', ['id' => $credit->id, 'paid' => 1]);
        $this->assertDatabaseHas('customers', ['id' => $customer, 'balance' => 0]);
    }
}
