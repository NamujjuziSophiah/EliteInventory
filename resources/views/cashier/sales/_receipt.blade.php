<div id="receiptContent" class="p-2">

    <style>
        #receiptContent .table-receipt{table-layout:fixed;width:100%;}
        #receiptContent .table-receipt th,
        #receiptContent .table-receipt td{vertical-align:top;}
        #receiptContent .col-item{width:50%;}
        #receiptContent .col-qty{width:12%;}
        #receiptContent .col-price{width:18%;}
        #receiptContent .col-total{width:20%;}
        #receiptContent .item-name{display:block;white-space:normal;word-break:break-word;}
        #receiptContent .receipt-scroll{max-height:320px;overflow-y:auto;}
    </style>

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
    <div class="receipt-scroll">
    <table class="table table-sm mb-2 table-receipt">
        <thead class="border-top border-bottom">
            <tr class="small">
                <th class="col-item">Item</th>
                <th class="text-center col-qty">Qty</th>
                <th class="text-end col-price">Price</th>
                <th class="text-end col-total">Total</th>
            </tr>
        </thead>
        <tbody>
        @foreach($sale->items as $it)
            <tr class="small">
                <td><span class="item-name">{{ $it->product->name ?? $it->product->title ?? 'Item '.$it->product_id }}</span></td>
                <td class="text-center">{{ $it->qty }}</td>
                <td class="text-end">{{ format_currency($it->price) }}</td>
                <td class="text-end">{{ format_currency($it->price * $it->qty) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>

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
