<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagerSessionAndAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_dashboard_data_requires_login()
    {
        $response = $this->get('/manager/dashboard/data');

        $response->assertRedirect(route('login'));
    }

    public function test_manager_dashboard_data_allows_manager()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['role' => 'manager']);

        $response = $this->actingAs($user)->get('/manager/dashboard/data');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'totalProducts',
            'lowStock',
            'overStock',
            'recentRestocks',
            'topSuppliers',
            'salesTrendLabels',
            'salesTrendData',
        ]);
    }

    public function test_authenticated_request_refreshes_session_marker()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['role' => 'manager']);

        $response = $this->actingAs($user)->get('/manager');

        $response->assertStatus(200);
        $this->assertTrue(session()->has('last_refreshed_at'));
    }
}
