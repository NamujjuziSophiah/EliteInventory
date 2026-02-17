<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
/** @var \Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $db = $app->make('db');

    $gp = $db->select('SELECT COALESCE(SUM(qty * (price - COALESCE(cost_per_unit,0))),0) as grossProfit FROM sale_items');
    $rev = $db->select('SELECT COALESCE(SUM(qty * price),0) as revenue FROM sale_items');
    $salesTotal = $db->select('SELECT COALESCE(SUM(total),0) as salesTotal FROM sales');

    $out = [
        'grossProfit' => $gp[0]->grossProfit ?? 0,
        'revenue' => $rev[0]->revenue ?? 0,
        'salesTotal' => $salesTotal[0]->salesTotal ?? 0,
    ];

    echo json_encode($out, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
