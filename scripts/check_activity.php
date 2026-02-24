<?php
require __DIR__ . '/../vendor/autoload.php';

// bootstrap the framework
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

// now use models
use App\Models\ActivityLog;

try {
    $count = ActivityLog::count();
    echo "OK: activity_logs_count={$count}\n";
} catch (\Throwable $e) {
    echo "ERR: ".get_class($e)." - ". $e->getMessage() ."\n";
}

$kernel->terminate($request, $response);
