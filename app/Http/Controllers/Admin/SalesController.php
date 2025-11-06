<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with('user');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }
        if ($request->filled('cashier_id')) {
            $query->where('user_id', $request->input('cashier_id'));
        }
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->input('payment_type'));
        }

        $sales = $query->orderBy('created_at', 'desc')->paginate(25);

        return view('admin.sales.index', compact('sales'));
    }

    public function show(Sale $sale)
    {
        $sale->load('items.product','user');
        return view('admin.sales.show', compact('sale'));
    }

    public function export(Request $request)
    {
        $filename = 'sales_export_' . now()->format('Ymd_His') . '.csv';

        $response = new StreamedResponse(function () use ($request) {
            $handle = fopen('php://output', 'w');
            // header
            fputcsv($handle, ['id','date','cashier','total','payment_type','items_count']);

            $query = Sale::with('user');
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->input('date_to'));
            }

            $query->orderBy('created_at','desc')->chunk(200, function ($sales) use ($handle) {
                foreach ($sales as $sale) {
                    fputcsv($handle, [
                        $sale->id,
                        $sale->created_at->toDateTimeString(),
                        optional($sale->user)->name,
                        number_format($sale->total, 2, '.', ''),
                        $sale->payment_type,
                        $sale->items()->count(),
                    ]);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
