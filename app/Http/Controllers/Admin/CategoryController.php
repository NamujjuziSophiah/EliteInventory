<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->paginate(20);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        $cat = Category::create($data);
        return redirect()->route('admin.categories.index')->with('success', 'Category created');
    }

    public function edit(Category $category)
    {
        if (empty(optional($category)->id)) {
            abort(404);
        }
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        if (empty(optional($category)->id)) {
            abort(404);
        }

        $catId = optional($category)->id ?? data_get($category, 'id');

        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('categories', 'name')->ignore($catId),
            ],
            'description' => 'nullable|string',
        ]);

        $category->update($data);
        return redirect()->route('admin.categories.index')->with('success', 'Category updated');
    }

    public function destroy(Category $category)
    {
        if (empty(optional($category)->id)) {
            abort(404);
        }

        // prevent deletion if has products
        if ($category->products()->exists()) {
            return redirect()->route('admin.categories.index')->with('error', 'Category has products and cannot be deleted');
        }
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted');
    }
}
