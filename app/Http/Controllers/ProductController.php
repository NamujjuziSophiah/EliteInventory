<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Intervention\Image\ImageManagerStatic as Image;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->orderBy('name');
        $stockFilter = $request->query('stock_filter');

        if ($stockFilter === 'low' || $stockFilter === 'overstock') {
            $stockColumn = $this->detectStockColumn();
            if ($stockColumn) {
                if ($stockFilter === 'low') {
                    $query->where($stockColumn, '<', 5);
                }

                if ($stockFilter === 'overstock') {
                    $query->where($stockColumn, '>=', 100);
                }
            }
        }

        $products = $query->paginate(20)->appends($request->query());

        return view('products.index', compact('products', 'stockFilter'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'selling_price' => 'nullable|numeric',
            'unit' => 'nullable|string|in:kilograms,litres,metres,bags each,packets,sackets,box,bars',
            'cost_price' => 'nullable|numeric',
            'markup_percent' => 'nullable|numeric',
            'stock' => 'nullable|integer',
            'image' => 'nullable|image|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image_path'] = $this->processImage($request->file('image'));
        }

        // Calculate prices
        $priceData = $this->calculatePrices($data);
        $data = array_merge($data, $priceData);

        // Create product
        $product = Product::create($data);

        // Generate SKU if not provided
        if (empty($product->sku)) {
            $this->generateAndAssignSku($product);
        }

        return $this->redirectWithSuccess('Product created');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'selling_price' => 'nullable|numeric',
            'unit' => 'nullable|string|in:kilograms,litres,metres,bags each,packets,sackets,box,bars',
            'cost_price' => 'nullable|numeric',
            'markup_percent' => 'nullable|numeric',
            'stock' => 'nullable|integer',
            'image' => 'nullable|image|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $this->processImage($request->file('image'));
        }

        // Generate SKU if missing
        if (empty($data['sku']) && empty($product->sku)) {
            $data['sku'] = $this->generateSku($data['name'], $product);
        }

        // Calculate prices
        $priceData = $this->calculatePrices($data, $product);
        $data = array_merge($data, $priceData);

        // Update product
        $product->update($data);

        return $this->redirectWithSuccess('Product updated');
    }

    public function destroy(Product $product)
    {
        // Delete image
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }
        
        $product->delete();
        return $this->redirectWithSuccess('Product deleted');
    }

    /**
     * AJAX endpoint: return minimal product info by id (used by purchases UI).
     */
    public function ajaxGet($id)
    {
        $product = Product::with('supplier')->find($id);
        if (! $product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'supplier_id' => $product->supplier_id,
            'supplier_name' => optional($product->supplier)->name,
            'stock' => $product->stock,
            'cost_price' => $product->cost_price,
        ]);
    }

    /**
     * Process and store product image
     */
    private function processImage($file): string
    {
        // Prefer Intervention Image if it's available; avoid static typed references
        // so static analyzers don't fail when the package isn't installed.
        if (class_exists('\\Intervention\\Image\\ImageManagerStatic')) {
            $manager = '\\Intervention\\Image\\ImageManagerStatic';
            try {
                $img = $manager::make($file)->orientate()->resize(800, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->encode('jpg', 85);

                $path = 'products/' . uniqid() . '.jpg';
                Storage::disk('public')->put($path, (string) $img);
                return $path;
            } catch (\Throwable $e) {
                // Fall through to default storage
            }
        }

        return $file->store('products', 'public');
    }

    /**
     * Calculate selling price and markup
     */
    private function calculatePrices(array $data, ?Product $product = null): array
    {
        $settings = Setting::first();
        $defaultMarkup = $settings->default_markup_percent ?? 20;
        
        $markup = isset($data['markup_percent']) 
            ? (float)$data['markup_percent'] 
            : ($product->markup_percent ?? $defaultMarkup);

        $cost = isset($data['cost_price']) 
            ? (float)$data['cost_price'] 
            : ($product->cost_price ?? 0.0);

        $selling = isset($data['selling_price']) 
            ? (float)$data['selling_price'] 
            : null;

        if ($selling === null) {
            $selling = round($cost * (1 + ($markup / 100)), 2);
        }

        return [
            'selling_price' => $selling,
            'cost_price' => $cost,
            'markup_percent' => $data['markup_percent'] ?? ($product->markup_percent ?? null),
            'stock' => $data['stock'] ?? ($product->stock ?? 0),
        ];
    }

    /**
     * Generate and assign SKU to product
     */
    private function generateAndAssignSku(Product $product): void
    {
        $sku = $this->generateSku($product->name, $product);
        $product->sku = $sku;
        $product->save();
    }

    /**
     * Generate SKU based on settings or name
     */
    private function generateSku(string $productName, ?Product $product = null): string
    {
        $settings = Setting::first();
        
        // Use settings-based SKU generation if available
        if ($settings && $settings->sku_prefix) {
            $prefix = $settings->sku_prefix ?? 'PR';
            $padding = $settings->sku_padding ?? 6;
            $next = $settings->sku_next ?? ($product->id ?? 1);
            
            if ($next < ($product->id ?? 1)) {
                $next = $product->id ?? $next;
            }
            
            $sku = $prefix . str_pad($next, $padding, '0', STR_PAD_LEFT);
            
            // Advance sequence
            try {
                $settings->sku_next = $next + 1;
                $settings->save();
            } catch (\Exception $e) {
                // Log error if needed
            }
            
            return $sku;
        }

        // Fallback to name-based SKU generation
        $base = \Illuminate\Support\Str::slug(substr($productName, 0, 50));
        $sku = strtoupper(substr($base, 0, 10)) . rand(1000, 9999);
        
        while (Product::where('sku', $sku)->exists()) {
            $sku = strtoupper(substr($base, 0, 10)) . rand(1000, 9999);
        }
        
        return $sku;
    }

    /**
     * Redirect with success message based on route
     */
    private function redirectWithSuccess(string $message)
    {
        $route = request()->is('admin/*') ? 'admin.products.index' : 'products.index';
        return redirect()->route($route)->with('success', $message);
    }

    private function detectStockColumn(): ?string
    {
        if (!Schema::hasTable('products')) {
            return null;
        }

        if (Schema::hasColumn('products', 'stock')) {
            return 'stock';
        }

        if (Schema::hasColumn('products', 'quantity')) {
            return 'quantity';
        }

        if (Schema::hasColumn('products', 'qty')) {
            return 'qty';
        }

        return null;
    }
}