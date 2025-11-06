<div id="receiptContent">
    <div class="row mb-2">
        <div class="col-8">
            <h5 class="mb-0">{{ config('app.name', 'Store') }}</h5>
            <small class="text-muted">{{ config('app.address', '') }}</small>
        </div>
        <div class="col-4 text-end">
            <div><strong>Receipt #</strong> {{ $sale->id }}</div>
            <div class="small text-muted">{{ $sale->created_at }}</div>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-6"><strong>Cashier:</strong> {{ $sale->user->name ?? 'n/a' }}</div>
        <div class="col-6 text-end">@if(isset($sale->customer) && $sale->customer)<strong>Customer:</strong> {{ $sale->customer->name }} {{ $sale->customer->phone ? '• '.$sale->customer->phone : '' }}@endif</div>
    </div>

    <table class="table table-sm">
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Price per Qty</th>
                <th class="text-end">Total Amount</th>
            </tr>
        </thead>
        <tbody>
        @foreach($sale->items as $it)
            <tr>
                <td>{{ $it->product->name ?? $it->product->title ?? 'Item '.$it->product_id }}</td>
                <td class="text-center">{{ $it->qty }}</td>
                <td class="text-end">{{ format_currency($it->price) }}</td>
                <td class="text-end">{{ format_currency($it->price * $it->qty) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="row mt-2">
        <div class="col-6">
            @if($sale->payments && $sale->payments->count())
                <div><strong>Payment breakdown</strong></div>
                <ul class="list-unstyled small mb-0">
                    @foreach($sale->payments as $p)
                        <li>{{ ucfirst($p->method ?? 'unknown') }}: {{ format_currency($p->amount) }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="col-6 text-end">
            <div>Discount: {{ format_currency($sale->discount ?? 0) }}</div>
            <div class="h5">Total Amount: {{ format_currency($sale->total) }}</div>
            @if(function_exists('numberToWords'))
                <div class="small text-muted">Amount (in words): {{ ucfirst(numberToWords($sale->total)) }}</div>
            @endif
            @if(isset($customerBalance))
                <div>Customer Balance: {{ format_currency($customerBalance) }}</div>
            @endif
        </div>
    </div>
</div>
