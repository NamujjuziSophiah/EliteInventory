<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Purchase;

class DashboardService
{
    public function getTotals(): array
    {
        return [
            'totalSales' => Schema::hasTable('sales') ? DB::table('sales')->count() : 0,
            'totalSalesValue' => Schema::hasTable('sales') ? DB::table('sales')->sum('total') : 0,
            'totalPurchases' => Schema::hasTable('purchases') ? DB::table('purchases')->count() : 0,
            'totalProducts' => Schema::hasTable('products') ? DB::table('products')->count() : 0,
            'totalUsers' => Schema::hasTable('users') ? DB::table('users')->count() : 0,
        ];
    }

    public function getTodaysSales(?int $userId = null): array
    {
        $count = 0;
        $total = 0.0;
        if (Schema::hasTable('sales')) {
            $today = Carbon::today();
            $query = DB::table('sales')->whereDate('created_at', $today);
            if ($userId && Schema::hasColumn('sales', 'user_id')) {
                $query->where('user_id', $userId);
            }
            $count = $query->count();
            $sumQ = DB::table('sales')->whereDate('created_at', $today);
            if ($userId && Schema::hasColumn('sales', 'user_id')) {
                $sumQ->where('user_id', $userId);
            }
            $total = (float) $sumQ->sum('total');
        }

        return ['count' => $count, 'total' => $total];
    }

    public function getSalesTrend(int $days = 7): array
    {
        $labels = [];
        $data = [];
        if (Schema::hasTable('sales')) {
            for ($i = $days - 1; $i >= 0; $i--) {
                $d = Carbon::today()->subDays($i);
                $labels[] = $d->format('D');
                $data[] = (float) DB::table('sales')->whereDate('created_at', $d)->sum('total');
            }
        }
        return ['labels' => $labels, 'data' => $data];
    }

    public function detectStockColumn(): ?string
    {
        if (! Schema::hasTable('products')) return null;
        if (Schema::hasColumn('products', 'stock')) return 'stock';
        if (Schema::hasColumn('products', 'quantity')) return 'quantity';
        if (Schema::hasColumn('products', 'qty')) return 'qty';
        return null;
    }

    public function getLowStock(int $limit = 10)
    {
        $col = $this->detectStockColumn();
        if (! $col) return collect([]);
        return DB::table('products')->where($col, '<=', 5)->limit($limit)->get();
    }

    public function getRecentRestocks(int $limit = 5)
    {
        if (! Schema::hasTable('purchases')) return collect([]);
        return DB::table('purchases')
            ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->select('purchases.*', 'suppliers.name as supplier_name')
            ->orderBy('purchases.created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getRecentPurchasesTable(int $limit = 200)
    {
        if (! Schema::hasTable('purchases')) return collect([]);
        return Purchase::with('items.product','supplier')->orderByDesc('created_at')->limit($limit)->get();
    }

    public function getRecentSales(int $limit = 50, ?int $userId = null)
    {
        if (! Schema::hasTable('sales')) return collect([]);
        $query = Sale::with('items.product','payments','customer','user')->orderByDesc('created_at')->limit($limit);
        if ($userId && Schema::hasColumn('sales', 'user_id')) {
            $query->where('user_id', $userId);
        }
        return $query->get();
    }

    public function getSupplierSpend(int $limit = 5)
    {
        if (! Schema::hasTable('purchases') || ! Schema::hasTable('suppliers')) return collect([]);
        return DB::table('purchases')
            ->select('supplier_id', DB::raw('SUM(total) as total_spend'))
            ->groupBy('supplier_id')
            ->orderByDesc('total_spend')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $row->supplier_name = DB::table('suppliers')->where('id', $row->supplier_id)->value('name');
                return $row;
            });
    }
}
