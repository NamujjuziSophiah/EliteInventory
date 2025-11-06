<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class ManagerForbiddenCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_cannot_access_cashier_checkout()
    {
    /** @var \App\Models\User $manager */
    $manager = User::factory()->create(['role' => 'manager']);

        $cart = [
            ['product_id' => 1, 'qty' => 1, 'price' => 10]
        ];

        $this->actingAs($manager)
            ->postJson('/cashier/checkout', ['cart' => $cart, 'payment' => 'cash'])
            ->assertStatus(403);
    }
}
