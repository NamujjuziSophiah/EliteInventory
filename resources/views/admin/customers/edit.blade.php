@extends('layouts.app')

@php
/** @var \App\Models\Customer $customer */
@endphp

@section('content')
<div class="container py-4">
    <h3>Edit Customer</h3>
    <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" value="{{ $customer->name }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" class="form-control" value="{{ $customer->email }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input name="phone" class="form-control" value="{{ $customer->phone }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Credit limit</label>
            <input name="credit_limit" type="number" step="0.01" class="form-control" value="{{ old('credit_limit', $customer->credit_limit ?? 0) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control">{{ $customer->notes }}</textarea>
        </div>
        <button class="btn btn-primary">Save</button>
    </form>
</div>
@endsection
