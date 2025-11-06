<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class PurchasesController extends Controller
{
    public function index()
    {
        // Minimal listing - in full product a purchases model may exist
        $purchases = DB::table('purchases')->latest()->limit(50)->get();
        return view('manager.purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = DB::table('suppliers')->orderBy('name')->get();
        // provide products so the restock form can show a product selector instead of free-text id
        $products = Product::orderBy('name')->get(['id','name','cost_price','supplier_id']);
        return view('manager.purchases.create', compact('suppliers','products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.cost_price' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'supplier_new_name' => 'nullable|string|max:255',
            'supplier_new_contact' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Determine supplier: existing or create new
            $supplierId = $request->input('supplier_id');
            if (empty($supplierId) && $request->filled('supplier_new_name')) {
                $supplierId = DB::table('suppliers')->insertGetId([
                    'name' => $request->input('supplier_new_name'),
                    'notes' => $request->input('supplier_new_contact'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $purchaseId = DB::table('purchases')->insertGetId([
                'supplier_id' => $supplierId,
                'total' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $total = 0;
            foreach ($data['items'] as $line) {
                $product = Product::where('id', $line['product_id'])->lockForUpdate()->first();
                if (! $product) {
                    DB::rollBack();
                    return back()->withErrors(['product' => 'Product not found: ' . ($line['product_id'] ?? 'unknown')])->withInput();
                }
                $product->stock = ($product->stock ?? 0) + $line['qty'];
                $product->cost_price = $line['cost_price'];
                $product->save();

                DB::table('purchase_items')->insert([
                    'purchase_id' => $purchaseId,
                    'product_id' => $product->id,
                    'qty' => $line['qty'],
                    'cost_price' => $line['cost_price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $total += $line['qty'] * $line['cost_price'];
            }

            DB::table('purchases')->where('id', $purchaseId)->update(['total' => $total]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('manager.purchases.index')->with('success', 'Purchase recorded');
    }
}
