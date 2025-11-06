<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ReportsPurchasesAccessTest extends TestCase
{
    // Do not touch DB for this simple UI access test; use make() to avoid persisting

    public function test_admin_sees_purchases_export_button()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->make(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('reports.purchases'))
            ->assertStatus(200)
            ->assertSee('Export Purchases CSV');
    }

    public function test_manager_does_not_see_purchases_export_button()
    {
        /** @var \App\Models\User $manager */
        $manager = User::factory()->make(['role' => 'manager']);

        $this->actingAs($manager)
            ->get(route('reports.purchases'))
            ->assertStatus(200)
            ->assertSee('Export Sales CSV')
            ->assertSee('Export Purchases CSV (admins only)');
    }
}
