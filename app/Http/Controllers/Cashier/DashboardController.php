<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $svc = new DashboardService();
            $userId = auth()->id();
            $today = $svc->getTodaysSales($userId);
            $lowStock = $svc->getLowStock(20);
            $recentSales = $svc->getRecentSales(5, $userId);

            return view('cashier.dashboard', [
                'salesCount' => $today['count'],
                'salesTotal' => $today['total'],
                'lowStock' => $lowStock,
                'recentSales' => $recentSales
            ]);
        } catch (\Exception $e) {
            // Log the issue but render a degraded dashboard so the cashier UI is still usable.
            Log::error('Cashier dashboard error: '.$e->getMessage());

            return view('cashier.dashboard', [
                'salesCount' => 0,
                'salesTotal' => 0.0,
                'lowStock' => collect([]),
                'recentSales' => collect([]),
                'error' => 'Some data is currently unavailable. The system may be offline or the database is unreachable.'
            ]);
        }
    }
}
