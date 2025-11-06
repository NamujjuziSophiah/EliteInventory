<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Sale;

class AdminSalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_sales_index_and_export()
    {
        // create admin user
    /** @var \App\Models\User $admin */
    $admin = User::factory()->create(['role' => 'admin']);

        // create several sales
        Sale::factory()->count(5)->create();

        $this->actingAs($admin)
            ->get(route('admin.sales.index'))
            ->assertStatus(200)
            ->assertSee('Sales');

    $resp = $this->actingAs($admin)->get(route('admin.sales.export'));
    $resp->assertStatus(200);
    // Streamed responses may not populate content in PHPUnit test harness; assert headers instead
    $this->assertStringContainsString('text/csv', $resp->headers->get('Content-Type'));
    $this->assertStringContainsString('attachment; filename=', $resp->headers->get('Content-Disposition'));
    }
}
