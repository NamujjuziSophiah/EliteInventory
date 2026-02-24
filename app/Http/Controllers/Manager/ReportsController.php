<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\DashboardService;

class ReportsController extends Controller
{
    public function sales(Request $request)
    {
        $from = $request->query('from', now()->startOfWeek()->toDateString());
        $to = $request->query('to', now()->toDateString());

        $rows = DB::table('sales')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderBy('created_at')
            ->get();

        // Provide a lightweight sales trend (last 7 days) for chart rendering
        $svc = new DashboardService();
        $trend = $svc->getSalesTrend(7);

        return view('manager.reports.sales', compact('rows','from','to'))
            ->with('salesTrendLabels', $trend['labels'])
            ->with('salesTrendData', $trend['data']);
    }

    public function exportSales(Request $request)
    {
        $from = $request->query('from', now()->startOfWeek()->toDateString());
        $to = $request->query('to', now()->toDateString());

        $rows = DB::table('sales')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderBy('created_at')
            ->get();

        $csv = "id,created_at,total,payment_type\n";
        foreach ($rows as $r) {
            $csv .= "{$r->id},{$r->created_at},{$r->total},{$r->payment_type}\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=manager_sales_{$from}_to_{$to}.csv",
        ]);
    }
}
