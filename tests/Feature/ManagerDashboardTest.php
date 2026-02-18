<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;

class ManagerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_dashboard_shows_totals()
    {
    /** @var \App\Models\User $manager */
    $manager = User::factory()->create(['role' => 'manager']);

        Product::factory()->count(3)->create();

        $this->actingAs($manager)
            ->get(route('manager.dashboard'))
            ->assertStatus(200)
            ->assertSeeText('Manager Dashboard')
            ->assertSeeText('Total Products');
    }
}
