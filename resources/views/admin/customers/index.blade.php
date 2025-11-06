@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Customers</h3>
        <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">Add Customer</a>
    </div>

    <div class="list-group">
        @foreach($customers as $c)
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ $c->name }}</strong>
                    <div class="small text-muted">{{ $c->email }} {{ $c->phone ? '• '.$c->phone : '' }}</div>
                </div>
                <div>
                    <a href="{{ route('admin.customers.edit', $c->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form action="{{ route('admin.customers.destroy', $c->id) }}" method="POST" style="display:inline-block">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-3">{{ $customers->links() }}</div>
</div>
@endsection
