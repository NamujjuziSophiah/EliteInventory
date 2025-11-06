@php $code = $product->barcode ?: $product->sku; @endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Label - {{ $product->name }}</title>
    <style>
        body{font-family: Arial, sans-serif;}
        .label{width:320px; padding:10px; border:1px solid #333;}
        .name{font-weight:bold; font-size:16px;}
        .price{font-size:14px; color:#333}
        img.bar{display:block; margin:8px 0;}
    </style>
    </head>
<body>
    <div class="label">
        <div class="name">{{ data_get($product, 'name', 'Unnamed product') }}</div>
    <div class="price">{{ format_currency(data_get($product, 'selling_price', 0)) }}</div>
        @php $pid = optional($product)->id ?? data_get($product, 'id'); @endphp
        @if($pid)
            <img class="bar" src="{{ route('admin.products.barcode', $pid) }}" alt="barcode">
        @else
            <div style="color:#999;font-size:12px">No barcode available</div>
        @endif
        <div class="small-muted">{{ $code }}</div>
    </div>
</body>
</html>
