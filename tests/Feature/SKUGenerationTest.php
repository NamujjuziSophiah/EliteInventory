<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Setting;
use App\Models\Product;

class SKUGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_created_uses_settings_sequence_for_sku_and_advances_sequence()
    {
    /** @var \App\Models\User $admin */
    $admin = User::factory()->create(['role' => 'admin']);

        // set settings with a known sku sequence
        Setting::create([
            'name' => 'Test',
            'currency' => 'USD',
            'default_markup_percent' => 10,
            'sku_prefix' => 'XX',
            'sku_padding' => 4,
            'sku_next' => 10,
        ]);

        $post = [
            'name' => 'Test Product',
            'cost_price' => 50,
            // no sku provided
        ];

        $response = $this->actingAs($admin)->post(route('admin.products.store'), $post);

        $response->assertRedirect();

        $product = Product::first();
        $this->assertNotNull($product);
        $this->assertEquals('XX' . str_pad(10, 4, '0', STR_PAD_LEFT), $product->sku);

        $settings = Setting::first();
        $this->assertEquals(11, $settings->sku_next);
    }
}
