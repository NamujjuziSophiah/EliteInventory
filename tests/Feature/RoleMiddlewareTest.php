<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_route_forbidden_for_non_admin()
    {
    /** @var \App\Models\User $user */
    $user = User::factory()->create(['role' => 'cashier']);
        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_admin_route_allowed_for_admin()
    {
    /** @var \App\Models\User $user */
    $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(200);
    }
}
