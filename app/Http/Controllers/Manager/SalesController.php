<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;

class SalesController extends Controller
{
    public function index()
    {
        $sales = Sale::with('items.product','payments','customer','user')
            ->latest()
            ->paginate(25);

        return view('manager.sales.index', compact('sales'));
    }

    public function show(Sale $sale)
    {
        $sale->load('items.product','payments','customer','user');
        return view('manager.sales.show', compact('sale'));
    }
}
