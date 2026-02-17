<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$db = $app->make('db');

try {
    // rows where cost_per_unit is greater or equal to line revenue (qty*price)
    $rows = $db->select(
        'SELECT id, sale_id, product_id, qty, price, cost_per_unit, (qty * price) as line_revenue, (cost_per_unit - (qty * price)) as diff FROM sale_items WHERE qty > 0 AND cost_per_unit >= (qty * price) ORDER BY diff DESC LIMIT 100'
    );

    $count = $db->select('SELECT COUNT(*) as cnt FROM sale_items WHERE qty > 0 AND cost_per_unit >= (qty * price)')[0]->cnt ?? 0;
    $sumDiff = $db->select('SELECT COALESCE(SUM(cost_per_unit - (qty * price)),0) as sumdiff FROM sale_items WHERE qty > 0 AND cost_per_unit >= (qty * price)')[0]->sumdiff ?? 0;

    $out = [
        'count' => (int)$count,
        'sum_diff' => (float)$sumDiff,
        'sample' => []
    ];

    foreach ($rows as $r) {
        $out['sample'][] = [
            'id' => $r->id,
            'sale_id' => $r->sale_id,
            'product_id' => $r->product_id,
            'qty' => (float)$r->qty,
            'price' => (float)$r->price,
            'cost_per_unit' => (float)$r->cost_per_unit,
            'line_revenue' => (float)$r->line_revenue,
            'diff' => (float)$r->diff,
            'suggested_new_cost_per_unit' => $r->qty ? round($r->cost_per_unit / $r->qty, 6) : null,
        ];
    }

    echo json_encode($out, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
