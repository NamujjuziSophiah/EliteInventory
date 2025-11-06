<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminDashboardViewTest extends TestCase
{
    use RefreshDatabase;

    protected $policies = [
        \App\Models\Sale::class => \App\Policies\SalePolicy::class,
    ];

    public function test_admin_can_view_dashboard()
    {
        // create an admin user
    /** @var \App\Models\User $admin */
    $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard');
    }
}
