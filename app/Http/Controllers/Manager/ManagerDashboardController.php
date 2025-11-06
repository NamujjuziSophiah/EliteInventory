<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Sale;

class ManagerDashboardController extends Controller
{
    public function index(Request $request)
    {
        // total products
        $totalProducts = Schema::hasTable('products') ? DB::table('products')->count() : 0;

        // detect stock column
        $stockColumn = null;
        if (Schema::hasTable('products')) {
            if (Schema::hasColumn('products', 'stock')) $stockColumn = 'stock';
            elseif (Schema::hasColumn('products', 'quantity')) $stockColumn = 'quantity';
            elseif (Schema::hasColumn('products', 'qty')) $stockColumn = 'qty';
        }

        // low stock products
        $lowStock = [];
        if ($stockColumn) {
            $lowStock = DB::table('products')->where($stockColumn, '<=', 5)->limit(10)->get();
        }

        // chart: last 7 days sales
        $chart = [];
        if (Schema::hasTable('sales')) {
            $chart = Sale::selectRaw("DATE(created_at) as day, COALESCE(SUM(total),0) as amount")
                ->where('created_at', '>=', now()->subDays(6))
                ->groupBy('day')
                ->orderBy('day')
                ->get()
                ->pluck('amount', 'day');
        }

        // recent restocks from purchases table
        $recentRestocks = [];
        if (Schema::hasTable('purchases')) {
            $recentRestocks = DB::table('purchases')
                ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
                ->select('purchases.*', 'suppliers.name as supplier_name')
                ->orderBy('purchases.created_at', 'desc')
                ->limit(5)
                ->get();
        }

        // supplier spend (top 5)
        $supplierSpend = [];
        if (Schema::hasTable('purchases') && Schema::hasTable('suppliers')) {
            $supplierSpend = DB::table('purchases')
                ->select('supplier_id', DB::raw('SUM(total) as total_spend'))
                ->groupBy('supplier_id')
                ->orderByDesc('total_spend')
                ->limit(5)
                ->get()
                ->map(function($row){
                    $row->supplier_name = DB::table('suppliers')->where('id', $row->supplier_id)->value('name');
                    return $row;
                });
        }

        return view('manager.dashboard', compact(
            'totalProducts',
            'lowStock',
            'recentRestocks',
            'supplierSpend',
            'stockColumn',
            'chart'
        ));
    }
}
