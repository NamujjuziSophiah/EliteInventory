<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Sale;
use App\Models\Product;

class SalesController extends Controller
{
    // List sales for the logged-in cashier (or admin if they visit)
    public function index(Request $request)
    {
        $user = auth()->user();
    $query = Sale::query()->with('customer')->orderBy('created_at', 'desc');

        if ($user && $user->role === 'cashier' && Schema::hasColumn('sales', 'user_id')) {
            $query->where('user_id', $user->id);
        }

        $sales = $query->paginate(25);
        return view('cashier.sales.index', compact('sales'));
    }

    // Show a single sale (receipt view) — reprintable
    public function show(Request $request, $id)
    {
    $sale = Sale::with('items.product','customer','payments')->findOrFail($id);

        // authorization: cashiers can only view their own sales unless admin
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }
        if (($user->role ?? null) === 'cashier' && $sale->user_id !== $user->id) {
            abort(403);
        }

        // If there's a recorded credit line for this sale, load it for the receipt
        $credit = null;
        $customerBalance = null;
        if (Schema::hasTable('customer_credits')) {
            $credit = DB::table('customer_credits')->where('sale_id', $sale->id)->first();
        }

        if ($sale->customer_id && Schema::hasTable('customers') && Schema::hasColumn('customers', 'balance')) {
            $customerBalance = DB::table('customers')->where('id', $sale->customer_id)->value('balance');
        }

        // If this is an AJAX request (receipt modal), return a partial without layout
        if ($request->ajax()) {
            return view('cashier.sales._receipt', compact('sale','credit','customerBalance'));
        }

        return view('cashier.sales.show', compact('sale','credit','customerBalance'));
    }

    // Cancel (void) a sale — sets status to 'void' if allowed
    public function destroy($id)
    {
        $sale = Sale::findOrFail($id);
        $user = auth()->user();
        if (! $user) abort(403);

        // Only admin or the owning cashier can void
        $role = $user->role ?? null;
        if (! ($role === 'admin' || ($role === 'cashier' && $sale->user_id === $user->id))) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            // restore stock for each sale item if possible
            foreach ($sale->items as $it) {
                $product = Product::find($it->product_id);
                if (! $product) continue;

                // determine stock column
                $stockCol = null;
                if (Schema::hasColumn('products', 'stock')) $stockCol = 'stock';
                elseif (Schema::hasColumn('products', 'quantity')) $stockCol = 'quantity';
                elseif (Schema::hasColumn('products', 'qty')) $stockCol = 'qty';

                if ($stockCol) {
                    DB::table('products')->where('id', $product->id)->increment($stockCol, (int)$it->qty);
                }
            }

            $sale->status = 'void';
            $sale->save();

            DB::commit();
            return redirect()->route('cashier.sales.index')->with('success', 'Sale voided and stock restored');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('cashier.sales.index')->with('error', 'Failed to void sale: ' . $e->getMessage());
        }
    }
}
