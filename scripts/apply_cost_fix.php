<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$db = $app->make('db');

try {
    $condition = 'qty > 0 AND cost_per_unit >= (qty * price)';
    $backupTable = 'sale_items_backup_for_cost_fix_' . date('Ymd_His');

    // Create backup table
    $db->statement("CREATE TABLE `$backupTable` LIKE sale_items");
    // Insert affected rows into backup
    $inserted = $db->insert("INSERT INTO `$backupTable` SELECT * FROM sale_items WHERE $condition");

    // Show preview: number of rows backed up
    $count = $db->select("SELECT COUNT(*) as cnt FROM `$backupTable`")[0]->cnt ?? 0;

    // Run diagnostics before change
    $before = [];
    $gp = $db->select('SELECT COALESCE(SUM(qty * (price - COALESCE(cost_per_unit,0))),0) as grossProfit FROM sale_items');
    $rev = $db->select('SELECT COALESCE(SUM(qty * price),0) as revenue FROM sale_items');
    $salesTotal = $db->select('SELECT COALESCE(SUM(total),0) as salesTotal FROM sales');
    $before['grossProfit'] = (float)($gp[0]->grossProfit ?? 0);
    $before['revenue'] = (float)($rev[0]->revenue ?? 0);
    $before['salesTotal'] = (float)($salesTotal[0]->salesTotal ?? 0);

    // Apply update - convert stored total-cost entries into per-unit cost
    // Use NULLIF(qty,0) to avoid division by zero; only affects rows matching condition
    $updated = $db->statement("UPDATE sale_items SET cost_per_unit = ROUND(cost_per_unit / NULLIF(qty,0), 6) WHERE $condition");

    // Run diagnostics after change
    $after = [];
    $gp2 = $db->select('SELECT COALESCE(SUM(qty * (price - COALESCE(cost_per_unit,0))),0) as grossProfit FROM sale_items');
    $rev2 = $db->select('SELECT COALESCE(SUM(qty * price),0) as revenue FROM sale_items');
    $salesTotal2 = $db->select('SELECT COALESCE(SUM(total),0) as salesTotal FROM sales');
    $after['grossProfit'] = (float)($gp2[0]->grossProfit ?? 0);
    $after['revenue'] = (float)($rev2[0]->revenue ?? 0);
    $after['salesTotal'] = (float)($salesTotal2[0]->salesTotal ?? 0);

    // Prepare response with sample rows before/after (select a few rows from backup and current)
    $sampleBefore = $db->select("SELECT id, sale_id, product_id, qty, price, cost_per_unit, (qty*price) as line_revenue FROM `$backupTable` LIMIT 20");
    $sampleAfter = $db->select("SELECT id, sale_id, product_id, qty, price, cost_per_unit, (qty*price) as line_revenue FROM sale_items WHERE $condition LIMIT 20");

    $out = [
        'backup_table' => $backupTable,
        'backed_up_count' => (int)$count,
        'rows_updated' => $updated,
        'before' => $before,
        'after' => $after,
        'sample_before' => $sampleBefore,
        'sample_after_remaining_matches' => $sampleAfter,
    ];

    echo json_encode($out, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
