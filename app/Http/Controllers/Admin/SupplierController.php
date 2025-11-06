<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('name')->paginate(20);
        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        Supplier::create($data);
        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier created');
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $supplier->update($data);
        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier updated');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier deleted');
    }

    public function trashed()
    {
        $suppliers = Supplier::onlyTrashed()->orderBy('name')->paginate(20);
        return view('admin.suppliers.trashed', compact('suppliers'));
    }

    public function restore($id)
    {
        $supplier = Supplier::onlyTrashed()->findOrFail($id);
        $supplier->restore();
        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier restored');
    }

    public function restoreBulk(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return redirect()->route('admin.suppliers.trashed')->with('status', 'No suppliers selected');
        }

        $restored = 0;
        foreach ($ids as $id) {
            $s = Supplier::onlyTrashed()->find($id);
            if ($s) { $s->restore(); $restored++; }
        }

        return redirect()->route('admin.suppliers.trashed')->with('success', "$restored suppliers restored");
    }
}
