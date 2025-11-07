<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Services\DashboardService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('name')->paginate(20);
        return view('admin.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function show(Customer $customer)
    {
        $svc = new DashboardService();
        $today = $svc->getTodaysSales(null, $customer->id);

        $customerBalance = null;
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'balance')) {
            $customerBalance = DB::table('customers')->where('id', $customer->id)->value('balance');
        }

        return view('admin.customers.show', compact('customer', 'today', 'customerBalance'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        Customer::create($data);
        return redirect()->route('admin.customers.index')->with('success', 'Customer created');
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $customer->update($data);
        return redirect()->route('admin.customers.index')->with('success', 'Customer updated');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted');
    }

    public function trashed()
    {
        $customers = Customer::onlyTrashed()->orderBy('name')->paginate(20);
        return view('admin.customers.trashed', compact('customers'));
    }

    public function restore($id)
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $customer->restore();
        return redirect()->route('admin.customers.index')->with('success', 'Customer restored');
    }

    public function restoreBulk(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return redirect()->route('admin.customers.trashed')->with('status', 'No customers selected');
        }

        $restored = 0;
        foreach ($ids as $id) {
            $c = Customer::onlyTrashed()->find($id);
            if ($c) { $c->restore(); $restored++; }
        }

        return redirect()->route('admin.customers.trashed')->with('success', "$restored customers restored");
    }
}
