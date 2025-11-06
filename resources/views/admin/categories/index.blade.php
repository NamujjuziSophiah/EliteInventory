@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0">Categories</h3>
            <div class="small-muted">Manage product categories</div>
        </div>
        <div>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-sm btn-primary">Add Category</a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary ms-2">Back to Products</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr><th>Name</th><th>Description</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($categories as $c)
                        <tr>
                            <td>{{ $c->name }}</td>
                            <td>{{ Str::limit($c->description, 80) }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.categories.edit', $c->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('admin.categories.destroy', $c->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Delete category?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger ms-1">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $categories->links() }}
        </div>
    </div>
</div>
@endsection
