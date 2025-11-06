<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckMiddlewareAliases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'middleware:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check that required route middleware aliases are registered in Kernel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $required = [
            'ensure.role',
            'single.role.enforcer',
            'audit.log',
        ];

        // Read Kernel class to inspect $routeMiddleware entries without booting app state
        $kernelPath = app_path('Http/Kernel.php');
        if (! file_exists($kernelPath)) {
            $this->error("Kernel file not found at {$kernelPath}");
            return 1;
        }

        $contents = file_get_contents($kernelPath);

        $missing = [];

        foreach ($required as $alias) {
            // simple check for the alias key in the routeMiddleware array
            if (strpos($contents, "'{$alias}'") === false && strpos($contents, '"'.$alias.'"') === false) {
                $missing[] = $alias;
            }
        }

        if (empty($missing)) {
            $this->info('OK: All required middleware aliases are present.');
            return 0;
        }

        $this->error('Missing middleware aliases: '.implode(', ', $missing));
        $this->line('Please add them to $routeMiddleware in app/Http/Kernel.php');

        return 2;
    }
}
