@extends('layouts.app')

@section('content')
<div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Products</h3>
        @if(request()->is('admin/*'))
            @if(Route::has('admin.products.create'))
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Add Product</a>
            @else
                <button class="btn btn-primary" disabled>Add Product</button>
            @endif
        @else
            @if(Route::has('products.create'))
                <a href="{{ route('products.create') }}" class="btn btn-primary">Add Product</a>
            @else
                <button class="btn btn-primary" disabled>Add Product</button>
            @endif
        @endif
    </div>

    @if(($stockFilter ?? null) === 'low')
        <div class="alert alert-warning py-2">Showing Low Stock products (quantity below 5).</div>
    @endif
    @if(($stockFilter ?? null) === 'overstock')
        <div class="alert alert-danger py-2">Showing Overstock products (quantity 100 and above).</div>
    @endif

    <form method="GET" id="labels-form" target="_blank"
          @if(request()->is('admin/*'))
              action="{{ Route::has('admin.products.labels.print') ? route('admin.products.labels.print') : '#' }}"
          @else
              action="{{ Route::has('products.index') ? route('products.index') : '#' }}"
          @endif>
        <div class="mb-2 d-flex justify-content-between align-items-center">
            <div>
                <input type="checkbox" id="select-all"> <label for="select-all" class="small">Select all on page</label>
            </div>
            <div>
                @if(request()->is('admin/*'))
                    <button type="submit" formaction="{{ route('admin.products.labels.print') }}" class="btn btn-outline-primary btn-sm">Print selected labels</button>
                @endif
            </div>
        </div>

        <div class="row">
            @foreach($products as $product)
                <div class="col-12 col-md-4 mb-3">
                    <div class="card p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <input type="checkbox" name="ids[]" value="{{ optional($product)->id ?? data_get($product,'id') }}" class="me-2 product-checkbox">
                                @php
                                    $imgSrc = '/images/avatar-placeholder.png';
                                    if ($product->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image_path)) {
                                        $imgSrc = route('storage.files.show', ['path' => $product->image_path]);
                                    }
                                @endphp
                                <img src="{{ $imgSrc }}" alt="" width="64" height="64" class="me-2" onerror="this.onerror=null;this.src='/images/avatar-placeholder.png'">
                                <div>
                                    <h6 class="mb-0">{{ $product->name }}</h6>
                                    <small class="text-muted">SKU: {{ $product->sku }}</small>
                                    @php $pid = optional($product)->id ?? data_get($product,'id'); @endphp
                                    <div>
                                        @if($pid)
                                            @if(request()->is('admin/*') && Route::has('admin.products.barcode'))
                                                <a href="{{ route('admin.products.barcode', $pid) }}" target="_blank" class="small">View barcode</a> | <a href="{{ route('admin.products.barcode', $pid) }}?print=1" target="_blank" class="small">Print label</a>
                                            @elseif(Route::has('products.barcode'))
                                                <a href="{{ route('products.barcode', $pid) }}" target="_blank" class="small">View barcode</a> | <a href="{{ route('products.barcode', $pid) }}?print=1" target="_blank" class="small">Print label</a>
                                            @else
                                                <span class="small text-muted">No barcode</span>
                                            @endif
                                        @else
                                            <span class="small text-muted">No barcode</span>
                                        @endif
                                    </div>
                                    <div>Stock: {{ $product->stock }}</div>
                                </div>
                            </div>
                            <div>
                                    @php $pid = optional($product)->id ?? data_get($product,'id'); @endphp
                                    @if(request()->is('admin/*'))
                                        @if($pid && Route::has('admin.products.edit'))
                                            <a href="{{ route('admin.products.edit', $pid) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        @else
                                            <span class="small text-muted">—</span>
                                        @endif
                                    @else
                                        @if($pid && Route::has('products.edit'))
                                            <a href="{{ route('products.edit', $pid) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        @else
                                            <span class="small text-muted">—</span>
                                        @endif
                                    @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </form>

    {{ $products->links() }}
</div>
@endsection

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const selectAll = document.getElementById('select-all');
        if (!selectAll) return;
        selectAll.addEventListener('change', function(){
            document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = this.checked);
        });
    });
    </script>
    @endpush
