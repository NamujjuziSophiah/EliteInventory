<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ForceLogoutAllUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:logout-all';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Force logout all users by marking system restart. All sessions will be invalidated.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Clear the app start marker so all existing sessions are invalidated
        Cache::forget('app_start_marker');

        // Reset by creating a new marker at current time
        Cache::rememberForever('app_start_marker', function () {
            return time();
        });

        $this->info('✓ All users have been logged out. They must log in again.');
        return 0;
    }
}
