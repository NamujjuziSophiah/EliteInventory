@extends('layouts.app')

@section('content')
@section('content')
<div class="container">
    @include('partials.back-button')
    <h1>Sales</h1>

    <div class="mb-3">
        <a href="{{ route('admin.sales.export', request()->all()) }}" class="btn btn-sm btn-outline-primary">Export CSV</a>
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Cashier</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Items</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr>
                <td>{{ $sale->id }}</td>
                <td>{{ $sale->created_at->toDateTimeString() }}</td>
                <td>{{ optional($sale->user)->name }}</td>
                <td>{{ format_currency($sale->total) }}</td>
                <td>{{ $sale->payment_type }}</td>
                <td>{{ $sale->items()->count() }}</td>
                <td><a href="{{ route('admin.sales.show', $sale) }}">View</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $sales->withQueryString()->links() }}
</div>
@endsection
