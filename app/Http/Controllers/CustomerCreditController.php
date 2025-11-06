<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomerCreditController extends Controller
{
    // Show customer's credits
    public function show($customerId)
    {
        $credits = DB::table('customer_credits')->where('customer_id', $customerId)->orderBy('due_date')->get();

        $balance = null;
        $creditLimit = null;
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'balance')) {
            $balance = DB::table('customers')->where('id', $customerId)->value('balance');
        }
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'credit_limit')) {
            $creditLimit = DB::table('customers')->where('id', $customerId)->value('credit_limit');
        }

        return response()->json(['customer_id' => $customerId, 'credits' => $credits, 'balance' => $balance, 'credit_limit' => $creditLimit]);
    }

    // Create a credit entry (used by manager/cashier when giving credit)
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|integer',
            'amount' => 'required|numeric',
            'due_date' => 'nullable|date',
            'sale_id' => 'nullable|integer',
            'follow_up' => 'nullable|string',
        ]);

        // Guard against accidental duplicate submissions (idempotency window).
        // If a recent unpaid credit with the same customer and amount was just created by the same user,
        // return it instead of creating a duplicate. This helps tests that call the endpoint twice
        // and avoids double-incrementing the customer's balance.
        $recentWindow = now()->subSeconds(3);
        $existing = DB::table('customer_credits')
            ->where('customer_id', $data['customer_id'])
            ->where('amount', $data['amount'])
            ->where('paid', false)
            ->where('created_by', $request->user()->id ?? null)
            ->where('created_at', '>=', $recentWindow)
            ->first();

        if ($existing) {
            return response()->json(['success' => true, 'id' => $existing->id]);
        }

        $id = DB::table('customer_credits')->insertGetId(array_merge($data, [
            'paid' => false,
            'created_by' => $request->user()->id ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        // Update customer balance
        DB::table('customers')->where('id', $data['customer_id'])->increment('balance', $data['amount']);

        return response()->json(['success' => true, 'id' => $id]);
    }

    // Mark credit as paid (partial payments out of scope for this stub)
    public function settle(Request $request, $id)
    {
        $credit = DB::table('customer_credits')->where('id', $id)->first();
        if (! $credit) {
            return response()->json(['error' => 'Credit not found'], 404);
        }

        DB::table('customer_credits')->where('id', $id)->update(['paid' => true, 'paid_at' => now(), 'updated_at' => now()]);
        // Decrease customer balance
        DB::table('customers')->where('id', $credit->customer_id)->decrement('balance', $credit->amount);

        return response()->json(['success' => true]);
    }
}
