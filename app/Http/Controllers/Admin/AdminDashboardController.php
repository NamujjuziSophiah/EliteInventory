<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DashboardService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $svc = new DashboardService();
        $totals = $svc->getTotals();
        $today = $svc->getTodaysSales();
        $trend = $svc->getSalesTrend(7);
        $lowStock = $svc->getLowStock(10);
        $productQuantityColumn = $svc->detectStockColumn();
    $recentPurchases = $svc->getRecentPurchasesTable(10);
    $recentSales = $svc->getRecentSales(10);
        // additional chart datasets
        $salesByCategory = $svc->getSalesByCategory(6);
        $revenueByPayment = $svc->getRevenueByPayment();

        // compute gross profit using explicit formula: sales revenue - cost of goods sold
        // This computes revenue and COGS from line items to keep numerator/denominator aligned.
        $grossProfit = 0.0;
        $lineItemRevenue = 0.0;
        $lineItemCogs = 0.0;
        if (Schema::hasTable('sale_items')) {
            $lineItemRevenue = (float) DB::table('sale_items')->selectRaw('COALESCE(SUM(qty * price),0) as revenue')->value('revenue');
            $lineItemCogs = (float) DB::table('sale_items')->selectRaw('COALESCE(SUM(qty * COALESCE(cost_per_unit,0)),0) as cogs')->value('cogs');
            $grossProfit = $lineItemRevenue - $lineItemCogs;
        }

        $outstandingCredits = 0;
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'balance')) {
            $outstandingCredits = (float) DB::table('customers')->sum('balance');
        } elseif (Schema::hasTable('customer_credits')) {
            $outstandingCredits = (float) DB::table('customer_credits')->where('paid', false)->sum('amount');
        }

        // Compute gross margin percent = (sales revenue - COGS) / sales revenue * 100
        $grossMarginPercent = 0.0;
        $denominator = $lineItemRevenue;
        if ($denominator <= 0) {
            // fallback to stored sales.total aggregate when line-item revenue isn't available
            $denominator = (float) ($totals['totalSalesValue'] ?? 0);
        }
        if ($denominator > 0) {
            $grossMarginPercent = ($grossProfit / $denominator) * 100;
        }

        return view('admin.dashboard', array_merge($totals, [
            'todaysSalesCount' => $today['count'],
            'todaysSalesValue' => $today['total'],
            'salesTrendLabels' => $trend['labels'],
            'salesTrendData' => $trend['data'],
            'grossProfit' => $grossProfit,
            'grossMarginPercent' => $grossMarginPercent,
            'outstandingCredits' => $outstandingCredits,
            'lowStock' => $lowStock,
            'productQuantityColumn' => $productQuantityColumn,
            'recentPurchases' => $recentPurchases,
            'recentSales' => $recentSales
            , 'salesByCategoryLabels' => $salesByCategory['labels'] ?? [],
            'salesByCategoryData' => $salesByCategory['data'] ?? [],
            'revenuePaymentLabels' => $revenueByPayment['labels'] ?? [],
            'revenuePaymentData' => $revenueByPayment['data'] ?? [],
        ]));
    }
}
