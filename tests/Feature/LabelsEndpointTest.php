<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use App\Models\Setting;

class LabelsEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_labels_endpoint_returns_view_with_selected_products()
    {
    /** @var \App\Models\User $admin */
    $admin = User::factory()->create(['role' => 'admin']);

        Setting::create([
            'name' => 'Test',
            'currency' => 'USD',
            'default_markup_percent' => 10,
            'sku_prefix' => 'LB',
            'sku_padding' => 3,
            'sku_next' => 1,
        ]);

        $p1 = Product::create(['name' => 'Labelled One', 'cost_price' => 10]);
        $p2 = Product::create(['name' => 'Labelled Two', 'cost_price' => 20]);

        $response = $this->actingAs($admin)->get(route('admin.products.labels.print', ['ids' => [$p1->id, $p2->id]]));

        $response->assertStatus(200);
        $response->assertSeeText('Labelled One');
        $response->assertSeeText('Labelled Two');
        // Should contain img tags for barcodes (route will be referenced)
        $this->assertStringContainsString('<img', $response->getContent());
    }
}
