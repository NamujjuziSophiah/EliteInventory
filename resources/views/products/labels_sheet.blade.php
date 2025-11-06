@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Print Labels</h3>
        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">Print</button>
    </div>

    <div class="labels-grid" style="display:flex;flex-wrap:wrap;gap:12px;">
        @foreach($products as $product)
            @php $pid = optional($product)->id ?? data_get($product, 'id'); @endphp
            <div class="label-item" style="width:180px;padding:8px;border:1px solid #eee;text-align:center;background:#fff">
                <div style="font-weight:600;margin-bottom:6px">{{ data_get($product,'name','Unnamed product') }}</div>
                <div style="margin-bottom:6px">SKU: {{ data_get($product,'sku','') }}</div>
                <div style="margin-bottom:6px">
                    @if($pid)
                        <img src="{{ route('admin.products.barcode', $pid) }}" alt="barcode" style="max-width:100%" />
                    @else
                        <div style="color:#999;font-size:12px">No barcode</div>
                    @endif
                </div>
                <div style="font-size:12px;color:#444">{{ data_get($product,'selling_price') ? format_currency(data_get($product,'selling_price')) : '' }}</div>
            </div>
        @endforeach
    </div>
</div>
@endsection
