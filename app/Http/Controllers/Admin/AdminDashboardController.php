<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DashboardService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $svc = new DashboardService();

        $totals = $svc->getTotals();
        $today = $svc->getTodaysSales();
        $trend = $svc->getSalesTrend(7);
        $lowStock = $svc->getLowStock(10);
        $overStock = $svc->getOverStock(10);
        $productQuantityColumn = $svc->detectStockColumn();
        $recentPurchases = $svc->getRecentPurchasesTable(10);
        $recentSales = $svc->getRecentSales(10);
        $salesByCategory = $svc->getSalesByCategory(6);
        $revenueByPayment = $svc->getRevenueByPayment();

        // ===============================
        // SAFE GROSS PROFIT CALCULATION
        // ===============================

        $grossProfit = 0.0;
        $lineItemRevenue = 0.0;
        $lineItemCogs = 0.0;

        if (Schema::hasTable('sale_items')) {

            $lineItemRevenue = (float) DB::table('sale_items')
                ->whereNotNull('price')
                ->whereNotNull('qty')
                ->selectRaw('COALESCE(SUM(qty * price),0) as revenue')
                ->value('revenue');

            $lineItemCogs = (float) DB::table('sale_items')
                ->whereNotNull('qty')
                ->selectRaw('COALESCE(SUM(qty * GREATEST(COALESCE(cost_per_unit,0),0)),0) as cogs')
                ->value('cogs');

            $grossProfit = $lineItemRevenue - $lineItemCogs;
        }

        // ===============================
        // OUTSTANDING CREDIT
        // ===============================

        $outstandingCredits = 0;

        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'balance')) {

            $outstandingCredits = (float) DB::table('customers')->sum('balance');

        } elseif (Schema::hasTable('customer_credits')) {

            $outstandingCredits = (float) DB::table('customer_credits')
                ->where('paid', false)
                ->sum('amount');
        }

        // ===============================
        // GROSS MARGIN %
        // ===============================

        $grossMarginPercent = 0.0;

        $denominator = $lineItemRevenue;

        if ($denominator <= 0) {
            $denominator = (float) ($totals['totalSalesValue'] ?? 0);
        }

        if ($denominator > 0) {

            $grossMarginPercent = ($grossProfit / $denominator) * 100;

            if (!is_finite($grossMarginPercent) || abs($grossMarginPercent) > 1000) {
                $grossMarginPercent = 0;
            }
        }

        // ===============================
        // RETURN VIEW
        // ===============================

        return view('admin.dashboard', array_merge($totals, [
            'todaysSalesCount' => $today['count'],
            'todaysSalesValue' => $today['total'],
            'salesTrendLabels' => $trend['labels'],
            'salesTrendData' => $trend['data'],
            'grossProfit' => $grossProfit,
            'grossMarginPercent' => $grossMarginPercent,
            'outstandingCredits' => $outstandingCredits,
            'lowStock' => $lowStock,
            'overStock' => $overStock,
            'productQuantityColumn' => $productQuantityColumn,
            'recentPurchases' => $recentPurchases,
            'recentSales' => $recentSales,
            'salesByCategoryLabels' => $salesByCategory['labels'] ?? [],
            'salesByCategoryData' => $salesByCategory['data'] ?? [],
            'revenuePaymentLabels' => $revenueByPayment['labels'] ?? [],
            'revenuePaymentData' => $revenueByPayment['data'] ?? [],
        ]));
    }
}
