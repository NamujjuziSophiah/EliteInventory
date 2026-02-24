<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
try {
    $count = DB::table('sales')->count();
    echo "OK: sales_count={$count}\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
