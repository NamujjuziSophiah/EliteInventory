<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class ReportsSeriesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_fetch_reports_series_json()
    {
        // create an admin user
        $user = User::factory()->create([ 'role' => 'admin' ]);

        $this->actingAs($user)
            ->getJson(route('admin.reports.series', [
                'date_from' => now()->subDays(7)->toDateString(),
                'date_to' => now()->toDateString(),
            ]))
            ->assertStatus(200)
            ->assertJsonStructure(['labels', 'values'])
            ->assertJsonCount(8, 'labels')
            ->assertJsonCount(8, 'values');
    }
}
