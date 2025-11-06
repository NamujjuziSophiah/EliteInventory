<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;

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

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
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
