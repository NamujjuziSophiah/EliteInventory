<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Supplier;

class AdminBulkRestoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_bulk_restore_customers()
    {
    /** @var \App\Models\User $admin */
    $admin = User::factory()->create(['role' => 'admin']);

        $customers = Customer::factory()->count(3)->create();
        // soft delete two
        $customers[0]->delete();
        $customers[1]->delete();

        $this->assertSoftDeleted('customers', ['id' => $customers[0]->id]);
        $this->assertSoftDeleted('customers', ['id' => $customers[1]->id]);

        $this->actingAs($admin)
            ->post(route('admin.customers.restore_bulk'), ['ids' => [$customers[0]->id, $customers[1]->id]])
            ->assertRedirect(route('admin.customers.trashed'));

        $this->assertDatabaseHas('customers', ['id' => $customers[0]->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('customers', ['id' => $customers[1]->id, 'deleted_at' => null]);
    }

    public function test_admin_can_bulk_restore_suppliers()
    {
    /** @var \App\Models\User $admin */
    $admin = User::factory()->create(['role' => 'admin']);

        $suppliers = Supplier::factory()->count(3)->create();
        $suppliers[0]->delete();
        $suppliers[2]->delete();

        $this->assertSoftDeleted('suppliers', ['id' => $suppliers[0]->id]);
        $this->assertSoftDeleted('suppliers', ['id' => $suppliers[2]->id]);

        $this->actingAs($admin)
            ->post(route('admin.suppliers.restore_bulk'), ['ids' => [$suppliers[0]->id, $suppliers[2]->id]])
            ->assertRedirect(route('admin.suppliers.trashed'));

        $this->assertDatabaseHas('suppliers', ['id' => $suppliers[0]->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('suppliers', ['id' => $suppliers[2]->id, 'deleted_at' => null]);
    }
};