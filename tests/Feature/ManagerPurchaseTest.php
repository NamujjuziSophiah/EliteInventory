<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class ManagerPurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_purchase_with_existing_supplier()
    {
        /** @var \App\Models\User $manager */
        $manager = User::factory()->create(['role' => 'manager']);

        $supplier = Supplier::create(['name' => 'Acme Supplies']);
        $product = Product::factory()->create(['cost_price' => 5.00, 'supplier_id' => $supplier->id]);

        $response = $this->actingAs($manager)->post(route('manager.purchases.store'), [
            'supplier_id' => $supplier->id,
            'items' => [
                ['product_id' => $product->id, 'qty' => 2, 'cost_price' => 5.00]
            ]
        ]);

    $response->assertStatus(302);

        $this->assertDatabaseHas('purchases', [
            'supplier_id' => $supplier->id,
            'total' => 10.00,
        ]);
    }
    public function test_manager_can_create_purchase_with_new_supplier()
    {
        /** @var \App\Models\User $manager */
        $manager = User::factory()->create(['role' => 'manager']);

        $product = Product::factory()->create(['cost_price' => 3.50]);

        $response = $this->actingAs($manager)->post(route('manager.purchases.store'), [
            'supplier_id' => null,
            'supplier_new_name' => 'Fresh Supplier Ltd',
            'items' => [
                ['product_id' => $product->id, 'qty' => 4, 'cost_price' => 3.50]
            ]
        ]);

    $response->assertStatus(302);

        $supplier = DB::table('suppliers')->where('name', 'Fresh Supplier Ltd')->first();
        $this->assertNotNull($supplier);

        $this->assertDatabaseHas('purchases', [
            'supplier_id' => $supplier->id,
            'total' => 14.00,
        ]);
    }
}
