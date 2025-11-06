@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Sale #{{ $sale->id }}</h1>

    <p><strong>Date:</strong> {{ $sale->created_at->toDateTimeString() }}</p>
    <p><strong>Cashier:</strong> {{ optional($sale->user)->name }}</p>
    <p><strong>Total:</strong> {{ format_currency($sale->total) }}</p>

    <h3>Items</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td>{{ optional($item->product)->name ?? 'N/A' }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ format_currency($item->price) }}</td>
                <td>{{ format_currency($item->price * $item->qty) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
