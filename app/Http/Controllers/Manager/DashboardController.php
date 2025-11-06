<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\DashboardService;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $svc = new DashboardService();
        $totals = $svc->getTotals();
        $lowStock = $svc->getLowStock(10);
        $trend = $svc->getSalesTrend(7);
        $recentRestocks = $svc->getRecentRestocks(5);
        $supplierSpend = $svc->getSupplierSpend(5);
        $recentPurchases = $svc->getRecentPurchasesTable(10);
        $recentSales = $svc->getRecentSales(50);

        return view('manager.dashboard', array_merge($totals, [
            'lowStock' => $lowStock,
            'recentRestocks' => $recentRestocks,
            'supplierSpend' => $supplierSpend,
            'stockColumn' => $svc->detectStockColumn(),
            'salesTrendLabels' => $trend['labels'],
            'salesTrendData' => $trend['data'],
            'recentPurchases' => $recentPurchases,
            'recentSales' => $recentSales
        ]));
    }
}
