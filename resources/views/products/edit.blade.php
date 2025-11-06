@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h3 class="mb-0">Edit Product</h3>
            <div class="small-muted">Modify product details</div>
        </div>
            @php $pid = optional($product)->id ?? data_get($product,'id'); @endphp
        
        
        <div>
            <a href="{{ request()->is('admin/*') ? route('admin.products.index') : route('products.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left"></i> Back</a>
        </div>
    </div>
    <div class="card p-3 shadow-sm" id="productForm" data-default-markup="{{ $settings->default_markup_percent ?? 20 }}">
    <form action="{{ request()->is('admin/*') ? route('admin.products.update', $pid) : route('products.update', $pid) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input name="name" value="{{ $product->name }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">SKU</label>
            <input name="sku" value="{{ $product->sku }}" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select">
                <option value="">-- none --</option>
                @foreach(App\Models\Category::orderBy('name')->get() as $cat)
                    <option value="{{ $cat->id }}" @if($product->category_id == $cat->id) selected @endif>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Preferred supplier</label>
            <select name="supplier_id" class="form-select">
                <option value="">-- none --</option>
                @foreach(App\Models\Supplier::orderBy('name')->get() as $s)
                    <option value="{{ $s->id }}" @if($product->supplier_id == $s->id) selected @endif>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>

            <div class="mb-3">
                <label class="form-label">Markup percent (%) <small class="text-muted">optional</small></label>
                <input name="markup_percent" class="form-control" type="number" step="0.01" min="0" value="{{ old('markup_percent', $product->markup_percent ?? '') }}">
            </div>
        <?php $settings = \App\Models\Setting::first(); ?>
        <div class="mb-3">
            <label class="form-label">Stock</label>
            <input name="stock" value="{{ $product->stock }}" type="number" class="form-control">
        </div>

            <div class="mb-3">
                <label class="form-label">Cost price</label>
                <input name="cost_price" id="cost_price" value="{{ $product->cost_price }}" class="form-control" type="number" step="0.01" min="0">
            </div>

            <div class="mb-3">
                <label class="form-label">Selling price</label>
                <input name="selling_price" id="selling_price" value="{{ $product->selling_price }}" class="form-control" type="number" step="0.01" min="0">
                <div class="small-muted">Selling price will be auto-calculated from cost price and markup if left blank.</div>
            </div>
        @push('scripts')
        <script>
            (function(){
                const form = document.getElementById('productForm');
                const defaultMarkup = parseFloat(form?.dataset?.defaultMarkup || '20');
                const cost = document.getElementById('cost_price');
                const selling = document.getElementById('selling_price');
                const markupInput = document.querySelector('input[name="markup_percent"]');
                function compute(){
                    const c = parseFloat(cost.value || '0');
                    const m = parseFloat(markupInput.value || String(defaultMarkup));
                    const s = (c * (1 + (m/100))).toFixed(2);
                    selling.value = s;
                }
                if (cost) cost.addEventListener('input', compute);
                if (markupInput) markupInput.addEventListener('input', compute);
            })();
        </script>
        @endpush
        <div class="mb-3">
            <label class="form-label">Image</label>
            <input name="image" type="file" class="form-control">
            @if($product->image_path)
                <div class="mt-2"><img src="{{ asset('storage/'.$product->image_path) }}" width="120"></div>
            @endif
        </div>
        <div class="d-flex">
            <button class="btn btn-success">Save</button>
            <button form="deleteForm" class="btn btn-danger ms-2">Delete</button>
            <a href="{{ request()->is('admin/*') ? route('admin.products.index') : route('products.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </div>
    </form>

        <form id="deleteForm" action="{{ request()->is('admin/*') ? route('admin.products.destroy', $pid) : route('products.destroy', $pid) }}" method="POST">
        @csrf
        @method('DELETE')
    </form>
    </div>
</div>
@endsection
