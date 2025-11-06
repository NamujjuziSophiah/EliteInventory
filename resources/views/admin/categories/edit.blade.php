@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3>Edit Category</h3>
    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input name="name" value="{{ $category->name }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control">{{ $category->description }}</textarea>
        </div>
        <div class="d-flex">
            <button class="btn btn-success">Save</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </div>
    </form>
</div>
@endsection
