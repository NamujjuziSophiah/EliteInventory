@extends('layouts.app')

@section('content')
<div class="container py-4">
    <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary mb-3">Back to Customers</a>
    <h3>{{ $customer->name }}</h3>
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card p-3">
                <div><strong>Email:</strong> {{ $customer->email ?? 'n/a' }}</div>
                <div><strong>Phone:</strong> {{ $customer->phone ?? 'n/a' }}</div>
                <div><strong>Credit limit:</strong> {{ format_currency($customer->credit_limit ?? 0) }}</div>
                <div><strong>Balance:</strong> {{ isset($customerBalance) ? format_currency($customerBalance) : 'n/a' }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3">
                <h5>Today's Sales</h5>
                <div class="display-6">{{ format_currency($today['total'] ?? 0) }}</div>
                <div class="small text-muted">({{ $today['count'] ?? 0 }} transactions)</div>
            </div>
        </div>
    </div>

    <div class="card p-3">
        <h5>Notes</h5>
        <div class="small text-muted">{{ $customer->notes ?? '-' }}</div>
    </div>
</div>
@endsection
