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
        return view('admin.reports.index');
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
