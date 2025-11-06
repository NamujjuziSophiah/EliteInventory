@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3>Add Category</h3>
    <form action="{{ route('admin.categories.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <div class="d-flex">
            <button class="btn btn-success">Save</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </div>
    </form>
</div>
@endsection
