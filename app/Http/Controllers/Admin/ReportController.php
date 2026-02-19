<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Purchase;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(29)->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        // Get sales by date
        $sales = Sale::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        // Get COGS by date
        $purchases = Purchase::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(created_at) as date, SUM(quantity * unit_cost) as cogs')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('cogs', 'date')
            ->toArray();

        // Build labels (dates) and profit values
        $profitLabels = [];
        $profitSeries = [];
        $period = new \DatePeriod(
            new \DateTime($dateFrom),
            new \DateInterval('P1D'),
            (new \DateTime($dateTo))->modify('+1 day')
        );

        foreach ($period as $date) {
            $day = $date->format('Y-m-d');
            $saleAmount = $sales[$day] ?? 0;
            $cogsAmount = $purchases[$day] ?? 0;
            $profitLabels[] = $day;
            $profitSeries[] = round($saleAmount - $cogsAmount, 2);
        }

        $rows = Purchase::latest()->take(10)->get();

        return view('admin.reports.index', compact('profitLabels', 'profitSeries', 'rows'));
    }

    // ...existing code for other methods (series, export, etc.)...
}