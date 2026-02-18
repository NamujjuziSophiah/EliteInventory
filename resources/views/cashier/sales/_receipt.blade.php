<div id="receiptContent" class="p-2">

    <!-- COMPANY HEADER -->
    <div class="text-center mb-2">
        <h4 class="mb-0 fw-bold">ELITE RETAIL SHOP</h4>
        <small class="text-muted">{{ config('app.address','') }}</small>
        <div class="small">Tel: {{ config('app.phone','') }}</div>
        <hr class="my-2">
    </div>

    <!-- RECEIPT INFO -->
    <div class="d-flex justify-content-between small mb-2">
        <div>
            <div><strong>Receipt:</strong> #{{ $sale->id }}</div>
            <div><strong>Date:</strong> {{ $sale->created_at }}</div>
        </div>
        <div class="text-end">
            <div><strong>Cashier:</strong> {{ $sale->user->name ?? 'N/A' }}</div>
            @if(!empty($sale->customer))
                <div><strong>Customer:</strong> {{ $sale->customer->name }}</div>
            @endif
        </div>
    </div>

    <!-- ITEMS TABLE -->
    <table class="table table-sm mb-2">
        <thead class="border-top border-bottom">
            <tr class="small">
                <th>Item</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Price</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
        @foreach($sale->items as $it)
            <tr class="small">
                <td>{{ $it->product->name ?? $it->product->title ?? 'Item '.$it->product_id }}</td>
                <td class="text-center">{{ $it->qty }}</td>
                <td class="text-end">{{ format_currency($it->price) }}</td>
                <td class="text-end">{{ format_currency($it->price * $it->qty) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <hr class="my-2">

    <!-- TOTALS -->
    <div class="small">
        @if(!empty($sale->discount) && $sale->discount > 0)
        <div class="d-flex justify-content-between">
            <span>Discount</span>
            <span>{{ format_currency($sale->discount) }}</span>
        </div>
        @endif

        <div class="d-flex justify-content-between fw-bold fs-5">
            <span>TOTAL</span>
            <span>{{ format_currency($sale->total) }}</span>
        </div>
    </div>

    <!-- PAYMENT METHOD -->
    @if(!empty($sale->payments) && $sale->payments->count())
    <div class="small mt-2">
        <strong>Payment:</strong>
        @foreach($sale->payments as $p)
            {{ ucfirst($p->method ?? 'Cash') }}
            ({{ format_currency($p->amount) }})
        @endforeach
    </div>
    @endif

    <!-- FOOTER -->
    <div class="text-center mt-3 small">
        <hr class="my-2">
        <div>Thank you for shopping with us</div>
        <div>Please come again</div>
    </div>

</div>
