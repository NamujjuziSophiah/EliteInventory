<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$db = $app->make('db');

$backupTable = 'sale_items_backup_for_cost_fix_20251109_070039';
try {
    // Verify backup table exists and get count
    $exists = $db->select("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?", [$backupTable]);
    if (!($exists[0]->cnt ?? 0)) {
        echo json_encode(['error' => "Backup table $backupTable not found"]);
        exit(1);
    }

    $count = $db->select("SELECT COUNT(*) as cnt FROM `$backupTable`")[0]->cnt ?? 0;

    echo json_encode(['backup_table' => $backupTable, 'backed_up_count' => (int)$count], JSON_PRETTY_PRINT);

    // Perform restore: update sale_items.cost_per_unit to original values from backup
    $updated = $db->statement("UPDATE sale_items s JOIN `$backupTable` b ON s.id = b.id SET s.cost_per_unit = b.cost_per_unit");

    // Diagnostics after restore
    $gp = $db->select('SELECT COALESCE(SUM(qty * (price - COALESCE(cost_per_unit,0))),0) as grossProfit FROM sale_items');
    $rev = $db->select('SELECT COALESCE(SUM(qty * price),0) as revenue FROM sale_items');
    $salesTotal = $db->select('SELECT COALESCE(SUM(total),0) as salesTotal FROM sales');

    $out = [
        'rows_restored' => $updated,
        'after' => [
            'grossProfit' => (float)($gp[0]->grossProfit ?? 0),
            'revenue' => (float)($rev[0]->revenue ?? 0),
            'salesTotal' => (float)($salesTotal[0]->salesTotal ?? 0),
        ]
    ];

    echo PHP_EOL . json_encode($out, JSON_PRETTY_PRINT) . PHP_EOL;
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
