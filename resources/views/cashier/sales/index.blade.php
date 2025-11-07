@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary me-2">Back</a>
            <h3 class="m-0">My Sales</h3>
        </div>
        <div>
            <a href="{{ route('cashier.dashboard') }}" class="btn btn-sm btn-outline-primary">Dashboard</a>
        </div>
    </div>

    <table class="table table-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($sales as $s)
            <tr>
                <td>{{ $s->id }}</td>
                <td>{{ $s->created_at }}</td>
                <td>{{ optional($s->customer)->name ?? 'Walk-in' }}</td>
                <td style="min-width:220px">
                    @php $items = $s->items ?? collect(); $show = 3; @endphp
                    @if(is_countable($items) && count($items) === 0)
                        <span class="text-muted small">(no items)</span>
                    @else
                        @foreach(collect($items)->take($show) as $it)
                            <span class="badge bg-light text-dark me-1 mb-1" title="{{ $it->product->name ?? 'Item' }}">{{ Str::limit($it->product->name ?? ('Item '.$it->product_id), 18) }} x{{ $it->qty }}</span>
                        @endforeach
                        @if(is_countable($items) && count($items) > $show)
                            <small class="text-muted">+{{ count($items) - $show }} more</small>
                        @endif
                    @endif
                </td>
                <td>{{ format_currency($s->total) }}</td>
                <td>{{ $s->status ?? 'completed' }}</td>
                <td>
                    <a href="{{ route('cashier.sales.show', $s->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                    @if(($s->status ?? 'completed') !== 'void')
                        <form method="POST" action="{{ route('cashier.sales.destroy', $s->id) }}" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Void this sale?')">Void</button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $sales->links() }}
</div>

@endsection
