@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Suppliers</h3>
        <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary">Add Supplier</a>
    </div>

    <div class="list-group">
        @foreach($suppliers as $s)
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ $s->name }}</strong>
                    <div class="small text-muted">{{ $s->email }} {{ $s->phone ? '• '.$s->phone : '' }}</div>
                </div>
                <div>
                    <a href="{{ route('admin.suppliers.edit', $s->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form action="{{ route('admin.suppliers.destroy', $s->id) }}" method="POST" style="display:inline-block">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        @endforeach
+    </div>
+
+    <div class="mt-3">{{ $suppliers->links() }}</div>
+</div>
@endsection
