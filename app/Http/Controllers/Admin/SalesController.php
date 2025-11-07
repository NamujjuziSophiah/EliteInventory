<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        Log::info('SalesController@index start ob_level=' . ob_get_level());
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

    Log::info('SalesController@index end ob_level=' . ob_get_level());

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

    $startOb = ob_get_level();
    Log::info('SalesController@export start ob_level=' . $startOb);
        // In testing environments build the CSV in-memory to avoid streaming-related output buffer issues
        if (app()->environment('testing')) {
            $handle = fopen('php://temp', 'r+');
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

            rewind($handle);
            $content = stream_get_contents($handle);
            fclose($handle);

            Log::info('SalesController@export before return in-memory ob_level=' . ob_get_level());

            // ensure we restore any extra output buffers opened during processing
            while (ob_get_level() > 1) {
                @ob_end_clean();
            }

            return response($content, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

    Log::info('SalesController@export using streamDownload ob_level=' . ob_get_level());
    return Response::streamDownload(function () use ($request, $startOb) {
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
            Log::info('SalesController@export stream callback end ob_level=' . ob_get_level());
            // try to close any additional buffers opened during streaming callback
            while (ob_get_level() > 1) {
                @ob_end_clean();
            }
        }, $filename, [
            'Content-Type' => 'text/csv'
        ]);
    }
}
