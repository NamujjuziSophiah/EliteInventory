<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\UserLog;

class UserLogAclTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_download_all_logs()
    {
        Storage::fake('public');

        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $u1 = User::factory()->create(['role' => 'manager']);
        $u2 = User::factory()->create(['role' => 'cashier']);

        // create two files and logs
        Storage::disk('public')->put('user_logs/log1.txt', 'first');
        Storage::disk('public')->put('user_logs/log2.txt', 'second');

        $log1 = UserLog::create([ 'user_id' => $u1->id, 'title' => 'L1', 'filename' => 'user_logs/log1.txt', 'mime_type' => 'text/plain', 'size' => 5 ]);
        $log2 = UserLog::create([ 'user_id' => $u2->id, 'title' => 'L2', 'filename' => 'user_logs/log2.txt', 'mime_type' => 'text/plain', 'size' => 6 ]);

        // admin sees both
        $resp = $this->actingAs($admin)->get(route('admin.user_logs.index'));
        $resp->assertStatus(200);
        $resp->assertSee('L1')->assertSee('L2');

        // admin can download each
        $d1 = $this->actingAs($admin)->get(route('admin.user_logs.download', $log1->id));
        $d1->assertStatus(200);
        $this->assertStringContainsString('first', $d1->getContent());

        $d2 = $this->actingAs($admin)->get(route('admin.user_logs.download', $log2->id));
        $d2->assertStatus(200);
        $this->assertStringContainsString('second', $d2->getContent());
    }

    public function test_non_admin_cannot_download_others_logs_but_can_download_own()
    {
        Storage::fake('public');

        /** @var \App\Models\User $u1 */
        $u1 = User::factory()->create(['role' => 'manager']);
        $u2 = User::factory()->create(['role' => 'cashier']);

        Storage::disk('public')->put('user_logs/log1.txt', 'first');
        Storage::disk('public')->put('user_logs/log2.txt', 'second');

        $log1 = UserLog::create([ 'user_id' => $u1->id, 'title' => 'L1', 'filename' => 'user_logs/log1.txt', 'mime_type' => 'text/plain', 'size' => 5 ]);
        $log2 = UserLog::create([ 'user_id' => $u2->id, 'title' => 'L2', 'filename' => 'user_logs/log2.txt', 'mime_type' => 'text/plain', 'size' => 6 ]);

        // u1 cannot download u2's log
        $resp = $this->actingAs($u1)->get(route('admin.user_logs.download', $log2->id));
        $resp->assertStatus(403);

        // u1 can download own
        $ok = $this->actingAs($u1)->get(route('admin.user_logs.download', $log1->id));
        $ok->assertStatus(200);
        $this->assertStringContainsString('first', $ok->getContent());
    }
}
