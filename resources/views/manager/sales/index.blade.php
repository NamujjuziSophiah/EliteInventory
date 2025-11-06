@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Sales (Recent)</h3>
        <div>
            <a href="{{ route('manager.dashboard') }}" class="btn btn-outline-secondary">Back to dashboard</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by sale id, customer, or product">
                </div>
                <div class="col-md-3">
                    <input type="date" name="date" value="{{ request('date') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($sales->isEmpty())
                <p class="text-muted">No sales found.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Cashier</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Payments</th>
                                <th class="text-end">Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sales as $sale)
                            <tr>
                                <td>{{ $sale->id }}</td>
                                <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $sale->user->name ?? 'n/a' }}</td>
                                <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                                <td style="min-width:220px;">
                                    @foreach($sale->items->take(5) as $it)
                                        <span class="badge bg-light text-dark me-1">{{ Str::limit($it->product->name ?? ('Item '.$it->product_id), 24) }} x{{ $it->qty }}</span>
                                    @endforeach
                                    @if($sale->items->count() > 5)
                                        <span class="badge bg-secondary">+{{ $sale->items->count() - 5 }} more</span>
                                    @endif
                                </td>
                                <td>
                                    @if($sale->payments && $sale->payments->count())
                                        @foreach($sale->payments as $p)
                                            <div class="small"><span class="badge bg-outline-primary text-dark">{{ ucfirst($p->method) }} {{ format_currency($p->amount) }}</span></div>
                                        @endforeach
                                    @else
                                        <div class="small text-muted">N/A</div>
                                    @endif
                                </td>
                                <td class="text-end"><strong>{{ format_currency($sale->total) }}</strong></td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-outline-secondary view-receipt-btn" data-sale-id="{{ $sale->id }}">View</a>
                                    <a href="{{ route('manager.sales.show', $sale->id) }}" class="btn btn-sm btn-link">Full</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $sales->links() }}</div>
            @endif
        </div>
    </div>
</div>

@endsection
