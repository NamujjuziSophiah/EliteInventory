<?php
// ...existing code...

// FIX 1: Gross profit must be Sales - COGS (not COGS - Sales)
$grossProfit = (float) $salesTotal - (float) $cogsTotal;

// ...existing code...

// FIX 2: Daily series must also be Sales - COGS
$values = [];
foreach ($labels as $day) {
    $sales = (float) ($salesByDate[$day] ?? 0);
    $cogs  = (float) ($cogsByDate[$day] ?? 0);
    $values[] = round($sales - $cogs, 2);
}

// ...existing code...

// FIX 3: If quantities can be negative in DB, normalize before COGS math
$qty = abs((float) $item->quantity);
$cogs += $qty * (float) $item->unit_cost;

// ...existing code...