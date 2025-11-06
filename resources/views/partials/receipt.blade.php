<div class="receipt p-3 bg-white border">
    <h5 class="mb-2">Receipt #{{ $sale->id ?? 'n/a' }}</h5>
    <div class="small text-muted">{{ $sale->created_at ?? '' }}</div>
    <hr>
    <div>
        @foreach($sale->items ?? [] as $item)
            <div class="d-flex justify-content-between">
                <div>{{ data_get($item, 'name', $item->product->name ?? 'Item') }} x{{ $item->qty ?? ($item->quantity ?? 1) }}</div>
                <div>{{ format_currency($item->price ?? 0) }}</div>
            </div>
        @endforeach
    </div>
    <hr>
    <div class="d-flex justify-content-between fw-bold">
        <div>Total</div>
    <div>{{ format_currency($sale->total ?? 0) }}</div>
    </div>
</div>
