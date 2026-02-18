<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;

class ManagerDashboardController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 5;
    private const OVERSTOCK_THRESHOLD = 100;

    public function index(Request $request)
    {
        // total products
        $totalProducts = Schema::hasTable('products') 
            ? DB::table('products')->count() 
            : 0;

        // detect stock column
        $stockColumn = null;
        if (Schema::hasTable('products')) {
            if (Schema::hasColumn('products', 'stock')) $stockColumn = 'stock';
            elseif (Schema::hasColumn('products', 'quantity')) $stockColumn = 'quantity';
            elseif (Schema::hasColumn('products', 'qty')) $stockColumn = 'qty';
        }

        // low stock products
        $lowStock = collect();
        $overStock = collect();
        if ($stockColumn) {
            $lowStock = DB::table('products')
                ->where($stockColumn, '<', self::LOW_STOCK_THRESHOLD)
                ->limit(10)
                ->get();

            $overStock = DB::table('products')
                ->where($stockColumn, '>=', self::OVERSTOCK_THRESHOLD)
                ->limit(10)
                ->get();
        }

        // chart: last 7 days sales
        $chart = collect();
        if (Schema::hasTable('sales')) {
            $chart = Sale::selectRaw("DATE(created_at) as day, COALESCE(SUM(total),0) as amount")
                ->where('created_at', '>=', now()->subDays(6))
                ->groupBy('day')
                ->orderBy('day')
                ->get()
                ->pluck('amount', 'day');
        }

        // recent restocks
        $recentRestocks = collect();
        if (Schema::hasTable('purchases')) {
            $recentRestocks = DB::table('purchases')
                ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
                ->select('purchases.*', 'suppliers.name as supplier_name')
                ->orderBy('purchases.created_at', 'desc')
                ->limit(5)
                ->get();
        }

        // supplier spend (top 5)
        $supplierSpend = collect();
        if (Schema::hasTable('purchases') && Schema::hasTable('suppliers')) {
            $supplierSpend = DB::table('purchases')
                ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
                ->select('supplier_id', 'suppliers.name as supplier_name', DB::raw('SUM(total) as total_spend'))
                ->groupBy('supplier_id', 'suppliers.name')
                ->orderByDesc('total_spend')
                ->limit(5)
                ->get();
        }

        return view('manager.dashboard', compact(
            'totalProducts',
            'lowStock',
            'overStock',
            'recentRestocks',
            'supplierSpend',
            'stockColumn',
            'chart'
        ));
    }

    public function data()
    {
        $stockColumn = null;
        if (Schema::hasTable('products')) {
            if (Schema::hasColumn('products', 'stock')) {
                $stockColumn = 'stock';
            } elseif (Schema::hasColumn('products', 'quantity')) {
                $stockColumn = 'quantity';
            } elseif (Schema::hasColumn('products', 'qty')) {
                $stockColumn = 'qty';
            }
        }

        $lowStockCount = 0;
        $overStockCount = 0;
        if ($stockColumn) {
            $lowStockCount = DB::table('products')
                ->where($stockColumn, '<', self::LOW_STOCK_THRESHOLD)
                ->count();

            $overStockCount = DB::table('products')
                ->where($stockColumn, '>=', self::OVERSTOCK_THRESHOLD)
                ->count();
        }

        return response()->json([
            'totalProducts' => Schema::hasTable('products') ? Product::count() : 0,
            'lowStock' => $lowStockCount,
            'overStock' => $overStockCount,
            'recentRestocks' => Schema::hasTable('purchases') ? Purchase::whereDate('created_at', '>=', now()->subDays(7))->count() : 0,
            'topSuppliers' => Schema::hasTable('suppliers') ? Supplier::count() : 0,
            'salesTrendLabels' => $this->getSalesTrendLabels(),
            'salesTrendData' => $this->getSalesTrendData(),
        ]);
    }

    private function getSalesTrendLabels()
    {
        if (!Schema::hasTable('sales')) return [];

        return Sale::selectRaw("DATE(created_at) as day")
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('day');
    }

    private function getSalesTrendData()
    {
        if (!Schema::hasTable('sales')) return [];

        return Sale::selectRaw("COALESCE(SUM(total),0) as amount, DATE(created_at) as day")
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('amount');
    }
}
