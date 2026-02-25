<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Gate;
use App\Models\Sale;
use App\Models\Purchase;

class ReportsController extends Controller
{
    public function index()
    {
        // prepare a simple profit/loss time series for the reports page
        // allow explicit date_from/date_to filters (ISO date strings); if not provided, fall back to last N days
        $request = request();
        $labels = [];
        $profitSeries = [];

        if ($request->filled('date_from') || $request->filled('date_to')) {
            // parse provided dates safely
            try {
                $from = $request->filled('date_from') ? 
                    \Carbon\Carbon::parse($request->query('date_from'))->startOfDay() : now()->subDays(29)->startOfDay();
                $to = $request->filled('date_to') ? 
                    \Carbon\Carbon::parse($request->query('date_to'))->endOfDay() : now()->endOfDay();
            } catch (\Exception $e) {
                // fallback to 30 days on parse error
                $from = now()->subDays(29)->startOfDay();
                $to = now()->endOfDay();
            }
        } else {
            $days = (int) $request->query('days', 30);
            $days = $days > 0 && $days <= 365 ? $days : 30;
            $to = now()->endOfDay();
            $from = now()->subDays($days - 1)->startOfDay();
        }

        // limit range length to 365 days to prevent expensive queries
        $maxRangeDays = 365;
        $rangeDays = (int) $from->diffInDays($to) + 1;
        if ($rangeDays > $maxRangeDays) {
            $from = $to->copy()->subDays($maxRangeDays - 1)->startOfDay();
            $rangeDays = $maxRangeDays;
        }

        for ($i = 0; $i < $rangeDays; $i++) {
            $dayStart = $from->copy()->addDays($i)->startOfDay();
            $dayEnd = $dayStart->copy()->endOfDay();

            $labels[] = $dayStart->toDateString();

            $totalSales = (float) DB::table('sales')
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->sum('total');

            $totalCost = (float) DB::table('sale_items as si')
                ->leftJoin('products as p', 'p.id', 'si.product_id')
                ->leftJoin('sales as s', 's.id', 'si.sale_id')
                ->whereBetween('s.created_at', [$dayStart, $dayEnd])
                ->selectRaw('COALESCE(SUM(si.qty * COALESCE(si.cost_per_unit, p.cost_price, 0)),0) as total_cost')
                ->value('total_cost');

            $profit = $totalSales - $totalCost;
            $profitSeries[] = round($profit, 2);
        }

        // Provide both `profitLabels` (used by the blade) and `labels`
        // for any code that may reference the shorter name.
        return view('admin.reports.index', ['profitLabels' => $labels, 'labels' => $labels, 'profitSeries' => $profitSeries]);
    }

    /**
     * Return a grouped time-series (labels + values) for profit per day as JSON.
     * This uses a single grouped query for performance and fills missing days with zero.
     */
    public function series(Request $request)
    {
        // parse date range with same defaults/guards as index()
        if ($request->filled('date_from') || $request->filled('date_to')) {
            try {
                $from = $request->filled('date_from') ? \Carbon\Carbon::parse($request->query('date_from'))->startOfDay() : now()->subDays(29)->startOfDay();
                $to = $request->filled('date_to') ? \Carbon\Carbon::parse($request->query('date_to'))->endOfDay() : now()->endOfDay();
            } catch (\Exception $e) {
                $from = now()->subDays(29)->startOfDay();
                $to = now()->endOfDay();
            }
        } else {
            $days = (int) $request->query('days', 30);
            $days = $days > 0 && $days <= 365 ? $days : 30;
            $to = now()->endOfDay();
            $from = now()->subDays($days - 1)->startOfDay();
        }

        // enforce max range
        $maxRangeDays = 365;
        $rangeDays = (int) $from->diffInDays($to) + 1;
        if ($rangeDays > $maxRangeDays) {
            $from = $to->copy()->subDays($maxRangeDays - 1)->startOfDay();
            $rangeDays = $maxRangeDays;
        }

        // Guard when tables missing
        if (! Schema::hasTable('sales')) {
            return response()->json(['labels' => [], 'values' => []]);
        }

        // One grouped query: group by date(s.created_at)
        $rows = DB::table('sales as s')
            ->leftJoin('sale_items as si', 'si.sale_id', 's.id')
            ->leftJoin('products as p', 'p.id', 'si.product_id')
            ->whereBetween('s.created_at', [$from->toDateTimeString(), $to->toDateTimeString()])
            ->selectRaw("DATE(s.created_at) as day, COALESCE(SUM(s.total),0) as total_sales, COALESCE(SUM(si.qty * COALESCE(si.cost_per_unit, p.cost_price, 0)),0) as total_cost")
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // build associative map day => profit
        $map = [];
        foreach ($rows as $r) {
            $map[$r->day] = round(((float) $r->total_sales - (float) $r->total_cost), 2);
        }

        // produce full labels and values arrays filling missing days with 0
        $labels = [];
        $values = [];
        for ($i = 0; $i < $rangeDays; $i++) {
            $day = $from->copy()->addDays($i)->toDateString();
            $labels[] = $day;
            $values[] = $map[$day] ?? 0.0;
        }

        return response()->json(['labels' => $labels, 'values' => $values]);
    }

    // Export profit & loss CSV for a date range (inclusive)
    public function export(Request $request)
    {
        if (! Gate::allows('export-reports')) {
            abort(403, 'Only admins may export reports.');
        }

        $from = $request->query('date_from') ?: now()->subDays(30)->startOfDay()->toDateTimeString();
        $to = $request->query('date_to') ?: now()->endOfDay()->toDateTimeString();

        // Guard against missing tables
        if (! Schema::hasTable('sales')) {
            return response()->json(['error' => 'No sales table'], 400);
        }

        $row = DB::table('sales as s')
            ->leftJoin('sale_items as si', 'si.sale_id', 's.id')
            ->leftJoin('products as p', 'p.id', 'si.product_id')
            ->whereBetween('s.created_at', [$from, $to])
            ->selectRaw(
                'COALESCE(SUM(s.total),0) as total_sales, '
                . 'COALESCE(SUM(si.qty * COALESCE(si.cost_per_unit, p.cost_price, 0)),0) as total_cost'
            )
            ->first();

        $totalSales = (float) ($row->total_sales ?? 0);
        $totalCost = (float) ($row->total_cost ?? 0);
        $grossProfit = $totalSales - $totalCost;
        $grossMargin = $totalSales > 0 ? ($grossProfit / $totalSales) * 100 : 0;

        $filename = 'profit_report_' . date('Ymd_His') . '.csv';

        $response = new StreamedResponse(function () use ($totalSales, $totalCost, $grossProfit, $grossMargin) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['metric', 'value']);
            // Use dot as decimal separator and no thousands separator for CSV numeric fields
            fputcsv($out, ['total_sales', number_format($totalSales, 2, '.', '')]);
            fputcsv($out, ['total_cost', number_format($totalCost, 2, '.', '')]);
            fputcsv($out, ['gross_profit', number_format($grossProfit, 2, '.', '')]);
            fputcsv($out, ['gross_margin_percent', number_format($grossMargin, 2, '.', '')]);
            fclose($out);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

        return $response;
    }

    // Sales listing view (for admin & cashier)
    public function sales(Request $request)
    {
        // provide a simple listing view; the view can decide how to render
        $rows = [];
        if (Schema::hasTable('sales')) {
            $rows = DB::table('sales')->latest()->limit(200)->get();
        }

        return view('admin.reports.index', ['type' => 'sales', 'rows' => $rows]);
    }

    // Export sales CSV (admin & cashier)
    public function exportSales(Request $request)
    {
        if (! Gate::allows('export-reports')) {
            abort(403, 'Only admins may export sales exports.');
        }

        if (! Schema::hasTable('sales')) {
            return response()->json(['error' => 'No sales table'], 400);
        }

        $filename = 'sales_report_' . date('Ymd_His') . '.csv';

        $response = new StreamedResponse(function () use ($request) {
            $out = fopen('php://output', 'w');
            // header
            fputcsv($out, ['sale_id', 'created_at', 'customer_id', 'total']);

            $query = Sale::query()->orderByDesc('created_at');
            foreach ($query->cursor() as $row) {
                fputcsv($out, [(int) $row->id, $row->created_at, $row->customer_id ?? '', $row->total ?? 0]);
            }

            fclose($out);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

        return $response;
    }

    // Purchases listing view (for admin & cashier)
    public function purchases(Request $request)
    {
        $rows = [];
        if (Schema::hasTable('purchases')) {
            $user = auth()->user();
            // Admins can see full listing; managers get a limited recent window for privacy/volume
            if ($user && ($user->role ?? null) === 'manager') {
                $rows = DB::table('purchases')
                    ->where('created_at', '>=', now()->subDays(90))
                    ->orderBy('created_at', 'desc')
                    ->limit(200)
                    ->get();
            } else {
                $rows = DB::table('purchases')->latest()->limit(200)->get();
            }
        }

        // Only admins may export full reports; controllers/views can use this flag to show/hide export UI
        $canExport = auth()->check() && (auth()->user()->role ?? null) === 'admin';

        return view('admin.reports.index', ['type' => 'purchases', 'rows' => $rows, 'canExport' => $canExport]);
    }

    // Export purchases CSV (admin & cashier)
    public function exportPurchases(Request $request)
    {
        if (! Gate::allows('export-reports')) {
            abort(403, 'Only admins may export purchase exports.');
        }

        if (! Schema::hasTable('purchases')) {
            return response()->json(['error' => 'No purchases table'], 400);
        }

        $filename = 'purchases_report_' . date('Ymd_His') . '.csv';

        $response = new StreamedResponse(function () use ($request) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['purchase_id', 'created_at', 'supplier_id', 'total']);

            $query = Purchase::query()->orderByDesc('created_at');
            foreach ($query->cursor() as $row) {
                fputcsv($out, [(int) $row->id, $row->created_at, $row->supplier_id ?? '', $row->total ?? 0]);
            }

            fclose($out);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

        return $response;
    }
}
