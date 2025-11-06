<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Supplier;

class AdminCustomerSupplierTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_delete_and_restore_customer()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.customers.store'), [
                'name' => 'Acme Customer',
                'email' => 'customer@example.com',
                'phone' => '12345'
            ])->assertRedirect(route('admin.customers.index'));

        $this->assertDatabaseHas('customers', ['name' => 'Acme Customer']);

        $customer = Customer::where('name', 'Acme Customer')->first();
        $this->actingAs($admin)
            ->delete(route('admin.customers.destroy', $customer->id))
            ->assertRedirect(route('admin.customers.index'));

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);

        $this->actingAs($admin)
            ->post(route('admin.customers.restore', $customer->id))
            ->assertRedirect(route('admin.customers.index'));

        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'deleted_at' => null]);
    }

    public function test_admin_can_create_delete_and_restore_supplier()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.suppliers.store'), [
                'name' => 'Acme Supplier',
                'email' => 'supplier@example.com',
                'phone' => '555'
            ])->assertRedirect(route('admin.suppliers.index'));

        $this->assertDatabaseHas('suppliers', ['name' => 'Acme Supplier']);

        $supplier = Supplier::where('name', 'Acme Supplier')->first();
        $this->actingAs($admin)
            ->delete(route('admin.suppliers.destroy', $supplier->id))
            ->assertRedirect(route('admin.suppliers.index'));

        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);

        $this->actingAs($admin)
            ->post(route('admin.suppliers.restore', $supplier->id))
            ->assertRedirect(route('admin.suppliers.index'));

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'deleted_at' => null]);
    }
}
