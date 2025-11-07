<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class POSController extends Controller
{
    // Show POS interface
    public function index()
    {
        $userId = auth()->id();

        $todayStart = now()->startOfDay()->toDateTimeString();
        $todayEnd = now()->endOfDay()->toDateTimeString();

        $salesCount = 0;
        $salesTotal = 0.0;
        $lowStock = [];

        if (Schema::hasTable('sales')) {
            $query = DB::table('sales')->whereBetween('created_at', [$todayStart, $todayEnd]);
            if (Schema::hasColumn('sales', 'user_id')) {
                $query->where('user_id', $userId);
            }
            $salesCount = $query->count();
            $salesTotal = (float) DB::table('sales')
                ->whereBetween('created_at', [$todayStart, $todayEnd])
                ->when(Schema::hasColumn('sales', 'user_id'), function ($q) use ($userId) { return $q->where('user_id', $userId); })
                ->sum('total');
        }

        if (Schema::hasTable('products')) {
            $stockCol = null;
            if (Schema::hasColumn('products', 'stock')) $stockCol = 'stock';
            elseif (Schema::hasColumn('products', 'quantity')) $stockCol = 'quantity';
            elseif (Schema::hasColumn('products', 'qty')) $stockCol = 'qty';

            if ($stockCol) {
                $lowStock = DB::table('products')->where($stockCol, '<=', 5)->limit(10)->get();
            }
        }

        $customers = [];
        if (Schema::hasTable('customers')) {
            // include credit_limit and balance so POS can display customer credit info without extra requests
            $customers = DB::table('customers')->select('id','name','phone','credit_limit','balance')->limit(200)->get();
        }

        return view('cashier.pos', compact('salesCount','salesTotal','lowStock','customers'));
    }

    /**
     * Search products by name, sku or barcode for POS UI autocomplete
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        if ($q === '') return response()->json(['results' => []]);

        if (! Schema::hasTable('products')) {
            return response()->json(['results' => []]);
        }

        $query = DB::table('products');
        // if numeric or short, search sku/barcode too
        $query->where(function ($qr) use ($q) {
            $qr->where('name', 'like', '%'.$q.'%')
               ->orWhere('sku', 'like', '%'.$q.'%')
               ->orWhere('barcode', 'like', '%'.$q.'%');
        });

        $rows = $query->limit(12)->get();

        $results = $rows->map(function ($p) {
            $available = 0;
            if (isset($p->stock)) $available = (int)$p->stock;
            elseif (isset($p->quantity)) $available = (int)$p->quantity;
            elseif (isset($p->qty)) $available = (int)$p->qty;

            return [
                'id' => $p->id,
                'name' => $p->name ?? ($p->title ?? null),
                'sku' => $p->sku ?? null,
                'barcode' => $p->barcode ?? null,
                'selling_price' => $p->selling_price ?? ($p->price ?? 0),
                'available' => $available,
            ];
        })->toArray();

        return response()->json(['results' => $results]);
    }

    /**
     * Create a customer inline from the POS UI (AJAX)
     */
    public function createCustomer(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:2000',
            'credit_limit' => 'nullable|numeric',
        ]);

        if (! Schema::hasTable('customers')) {
            return response()->json(['error' => 'Customers table not available'], 500);
        }

        // duplicate checks: prefer to fail-fast and surface to cashier
        if (! empty($data['email'])) {
            $exists = DB::table('customers')->where('email', $data['email'])->exists();
            if ($exists) {
                return response()->json(['errors' => ['email' => ['Email is already in use by another customer']]], 422);
            }
        }
        if (! empty($data['phone'])) {
            $exists = DB::table('customers')->where('phone', $data['phone'])->exists();
            if ($exists) {
                return response()->json(['errors' => ['phone' => ['Phone is already in use by another customer']]], 422);
            }
        }

        $id = DB::table('customers')->insertGetId([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'notes' => $data['notes'] ?? null,
            'address' => $data['address'] ?? null,
            'credit_limit' => isset($data['credit_limit']) ? $data['credit_limit'] : 0,
            // balance should start at 0 and be managed by credit/payment flows
            'balance' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $customer = DB::table('customers')->where('id', $id)->first();

        return response()->json(['customer' => $customer]);
    }
    // Simple scan endpoint - expects 'barcode' or 'sku' in request
    public function scan(Request $request)
    {
        $barcode = $request->input('barcode') ?? $request->input('sku');
        if (! $barcode) {
            return response()->json(['error' => 'No barcode provided'], 422);
        }

        // Attempt to find product in products table; this is a safe lookup if table exists
        try {
            $product = DB::table('products')->where('barcode', $barcode)->orWhere('sku', $barcode)->first();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Products table not found or query failed', 'message' => $e->getMessage()], 500);
        }

        if (! $product) {
            return response()->json(['found' => false]);
        }

        // normalize available stock column (support stock, quantity, qty)
        $available = 0;
        $stockColumn = null;
        if (isset($product->stock)) { $available = (int)$product->stock; $stockColumn = 'stock'; }
        elseif (isset($product->quantity)) { $available = (int)$product->quantity; $stockColumn = 'quantity'; }
        elseif (isset($product->qty)) { $available = (int)$product->qty; $stockColumn = 'qty'; }

        return response()->json(['found' => true, 'product' => $product, 'available' => $available, 'stock_column' => $stockColumn]);
    }

    // Simplified checkout: accepts cart payload, payment type, and handles stock decrement
    public function checkout(Request $request)
    {
        // enforce that only authorized users (cashier/admin) can create sales
        $this->authorize('create', Sale::class);

        // basic validation for expected payload
        // Note: we do not trust client-provided prices. Server will compute prices from product records.
        $data = $request->validate([
            'cart' => 'required|array|min:1',
            'cart.*.product_id' => 'required|integer',
            'cart.*.price' => 'nullable|numeric|min:0',
            'cart.*.qty' => 'required|integer|min:1',
            'cart.*.discount' => 'nullable|numeric|min:0',
            'payment' => 'nullable|string',
            'payment_parts' => 'nullable|array',
            'payment_parts.*.method' => 'nullable|string',
            'payment_parts.*.amount' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|integer',
            'discount' => 'nullable|numeric|min:0',
        ]);

        $cart = $data['cart'];
        $payment = $data['payment'] ?? 'cash';

        // Allowed payment methods: cash, mobile_money, credit (credit allowed only with checks below)
        $allowedPayments = ['cash', 'mobile_money', 'credit'];

        // support mixed payments via payment_parts
        $isMixed = ! empty($data['payment_parts']);
        if (! $isMixed) {
            if (! in_array($payment, $allowedPayments, true)) {
                return response()->json(['error' => 'Unsupported payment method'], 422);
            }
        }

    // Use Eloquent models for cleaner operations
    $paymentType = $isMixed ? 'mixed' : $payment;
    DB::beginTransaction();
        try {
            // compute raw total using authoritative product prices from DB and apply per-item discounts and optional overall discount
            $rawTotal = 0.0;
            $perItemDiscountTotal = 0.0;
            $productPrices = [];
            foreach ($cart as $i) {
                $prod = Product::find($i['product_id']);
                $unitPrice = ($prod && isset($prod->selling_price)) ? (float)$prod->selling_price : (float)($i['price'] ?? 0);
                $qty = (int)($i['qty'] ?? 0);
                $rawTotal += $unitPrice * $qty;
                $perItemDiscountTotal += ((float)($i['discount'] ?? 0)) * max(1, $qty);
                $productPrices[$i['product_id']] = $unitPrice;
            }
            $overallDiscount = (float) ($data['discount'] ?? 0);
            $total = max(0, $rawTotal - $perItemDiscountTotal - $overallDiscount);
         

            
            // Determine sale status and handle credit checks. Support mixed payments via payment_parts.
            $saleStatus = 'completed';
            $creditPortion = 0.0;
            $customerId = $data['customer_id'] ?? null;

            if ($isMixed) {
                $sumParts = 0.0;
                foreach ($data['payment_parts'] as $p) {
                    $method = $p['method'] ?? null;
                    $amt = (float) ($p['amount'] ?? 0);
                    if (! in_array($method, $allowedPayments, true)) {
                        return response()->json(['error' => 'Unsupported payment method in parts: ' . $method], 422);
                    }
                    $sumParts += $amt;
                    if ($method === 'credit') $creditPortion += $amt;
                }
                // allow minimal rounding tolerance
                if (abs($sumParts - $total) > 0.01) {
                    return response()->json(['error' => 'Sum of payment parts does not equal total'], 422);
                }
            } else {
                if ($payment === 'credit') {
                    $creditPortion = $total;
                }
            }

            if ($creditPortion > 0) {
                if (! $customerId) {
                    return response()->json(['error' => 'customer_id is required for credit sales'], 422);
                }

                // determine customer's outstanding and credit limit
                $outstanding = 0;
                if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'balance')) {
                    $outstanding = (float) DB::table('customers')->where('id', $customerId)->value('balance') ?: 0;
                } elseif (Schema::hasTable('customer_credits')) {
                    $outstanding = (float) DB::table('customer_credits')->where('customer_id', $customerId)->where('paid', false)->sum('amount');
                }

                $creditLimit = null;
                if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'credit_limit')) {
                    $creditLimit = (float) DB::table('customers')->where('id', $customerId)->value('credit_limit') ?: null;
                }
                if (is_null($creditLimit)) {
                    $creditLimit = (float) env('DEFAULT_CREDIT_LIMIT', 500.0);
                }

                if (($outstanding + $creditPortion) > $creditLimit) {
                    return response()->json(['error' => 'Customer credit limit exceeded'], 422);
                }

                $saleStatus = $creditPortion < $total ? 'partially_paid' : 'credit';
            }

            $paymentType = $isMixed ? 'mixed' : $payment;
            $sale = Sale::create([
                'user_id' => auth()->id() ?? null,
                'customer_id' => $customerId ?? null,
                'total' => $total,
                'payment_type' => $paymentType,
                'status' => $saleStatus,
                'discount' => $overallDiscount,
            ]);

            // Persist payment parts (for auditability). Store single-part as well.
            if (Schema::hasTable('sale_payments')) {
                if ($isMixed) {
                    foreach ($data['payment_parts'] as $p) {
                        DB::table('sale_payments')->insert([
                            'sale_id' => $sale->id,
                            'method' => $p['method'] ?? null,
                            'amount' => (float)($p['amount'] ?? 0),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                } else {
                    // single payment row
                    DB::table('sale_payments')->insert([
                        'sale_id' => $sale->id,
                        'method' => $payment,
                        'amount' => $total,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            foreach ($cart as $item) {
                if (! isset($item['product_id'])) continue;

                $product = Product::find($item['product_id']);
                if (! $product) continue;

                // Use authoritative unit price from DB (or fallback if missing)
                $unitPrice = $productPrices[$product->id] ?? (float)($item['price'] ?? 0);

                $sale->items()->create([
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'price' => $unitPrice,
                    'cost_per_unit' => $product->cost_price ?? null,
                    'discount' => isset($item['discount']) ? (float)$item['discount'] : 0,
                ]);

                // decide which column stores stock: prefer 'stock', then 'quantity', then 'qty'
                $stockCol = null;
                if (Schema::hasColumn('products', 'stock')) $stockCol = 'stock';
                elseif (Schema::hasColumn('products', 'quantity')) $stockCol = 'quantity';
                elseif (Schema::hasColumn('products', 'qty')) $stockCol = 'qty';

                $requested = (int)$item['qty'];
                $currentQty = 0;
                if ($stockCol) {
                    $currentQty = (int) DB::table('products')->where('id', $product->id)->value($stockCol);
                    if ($requested > $currentQty) {
                        // rollback and return insufficient stock error
                        DB::rollBack();
                        return response()->json(['error' => 'Insufficient stock for ' . ($product->name ?? $product->title ?? $product->id)], 422);
                    }

                    DB::table('products')->where('id', $product->id)->decrement($stockCol, $requested);
                }
            }

            // NOTE: payment rows are persisted earlier (right after sale creation) to avoid duplicates.
            // The earlier block already inserts either the single payment (non-mixed) or each part (mixed).
            // Keeping this empty to avoid double-inserting payment rows which caused inconsistent amounts for credit sales.

            // If there was a credit portion (either full credit or mixed), record only that portion
            if ($creditPortion > 0) {
                $customerId = $data['customer_id'] ?? null;
                if ($customerId && Schema::hasTable('customer_credits')) {
                    $creditId = DB::table('customer_credits')->insertGetId([
                        'customer_id' => $customerId,
                        'sale_id' => $sale->id,
                        'amount' => $creditPortion,
                        'due_date' => now()->addDays(30)->toDateString(),
                        'paid' => false,
                        'created_by' => auth()->id() ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'balance')) {
                        DB::table('customers')->where('id', $customerId)->increment('balance', $creditPortion);
                    }
                }
            }

            DB::commit();

            $receiptUrl = route('cashier.sales.show', ['id' => $sale->id]);
            return response()->json(['success' => true, 'sale_id' => $sale->id, 'receipt_url' => $receiptUrl]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Log full exception for debugging in test runs
            try {
                Log::error('Checkout failed exception', ['message' => $e->getMessage(), 'exception' => $e]);
            } catch (\Exception $lex) {
                // swallow logging errors to avoid masking original exception
            }

            return response()->json(['error' => 'Checkout failed', 'message' => $e->getMessage()], 500);
        }
    }
}
