<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

use App\Models\ActivityLog;

try {
    $log = ActivityLog::with('user')->first();
    if (!$log) {
        echo "OK: no activity log rows\n";
    } else {
        $userName = optional($log->user)->name ?? '(no user)';
        echo "OK: activity_log id={$log->id} user={$userName}\n";
    }
} catch (\Throwable $e) {
    echo "ERR: ".get_class($e)." - " . $e->getMessage() ."\n";
}

$kernel->terminate($request, $response);
