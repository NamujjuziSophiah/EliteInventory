<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;

class AdminProfitExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_profit_csv()
    {
    /** @var \App\Models\User $admin */
    $admin = User::factory()->create(['role' => 'admin']);

        // create a product with cost
        $p = Product::factory()->create(['cost_price' => 10.00, 'selling_price' => 15.00]);

        // create a sale and sale_item
        $sale = Sale::factory()->create(['total' => 15.00, 'created_at' => now()]);
        SaleItem::factory()->create(['sale_id' => $sale->id, 'product_id' => $p->id, 'qty' => 1, 'price' => 15.00, 'cost_per_unit' => null]);

        $resp = $this->actingAs($admin)->get(route('admin.reports.export'));
        $resp->assertStatus(200);
        $this->assertStringContainsString('text/csv', $resp->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename=', $resp->headers->get('Content-Disposition'));
    }
}
