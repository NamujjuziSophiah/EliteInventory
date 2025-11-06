@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Sale #{{ $sale->id }}</h3>
        <div>
            <a href="{{ route('manager.sales.index') }}" class="btn btn-outline-secondary">Back to sales</a>
        </div>
    </div>

    <div class="card p-3">
        <div class="row">
            <div class="col-md-8">
                @includeWhen(View::exists('cashier.sales._receipt'), 'cashier.sales._receipt', ['sale' => $sale, 'credit' => null, 'customerBalance' => null])
            </div>
            <div class="col-md-4">
                <h5>Summary</h5>
                <div class="mb-2"><strong>Total:</strong> {{ format_currency($sale->total) }}</div>
                <div class="mb-2"><strong>Cashier:</strong> {{ $sale->user->name ?? 'n/a' }}</div>
                <div class="mb-2"><strong>Customer:</strong> {{ $sale->customer->name ?? 'Walk-in' }}</div>
                <div class="mb-2"><strong>Payments:</strong>
                    @if($sale->payments && $sale->payments->count())
                        <ul class="list-unstyled small">
                        @foreach($sale->payments as $p)
                            <li>{{ ucfirst($p->method) }} — {{ format_currency($p->amount) }}</li>
                        @endforeach
                        </ul>
                    @else
                        <div class="small text-muted">N/A</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
