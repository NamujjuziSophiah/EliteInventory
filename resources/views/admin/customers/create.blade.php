@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3>Add Customer</h3>
    <form action="{{ route('admin.customers.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input name="phone" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control"></textarea>
        </div>
        <button class="btn btn-primary">Save</button>
    </form>
</div>
@endsection
