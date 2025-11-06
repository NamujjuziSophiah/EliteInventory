@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>Users</h1>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="mb-3">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Create User</a>
    </div>

    <form method="POST" action="{{ route('admin.users.restore_bulk') }}">
        @csrf
    <table class="table table-striped">
        <thead>
            <tr>
                <th></th>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Deleted</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
            <tr>
                <td>
                    @if($u->deleted_at)
                        <input type="checkbox" name="restore_ids[]" value="{{ $u->id }}">
                    @endif
                </td>
                <td>{{ $u->id }}</td>
                <td>{{ $u->name }}</td>
                <td>{{ $u->email }}</td>
                <td>{{ $u->role }}</td>
                <td>{{ $u->deleted_at ? 'Yes' : 'No' }}</td>
                <td>
                    <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-sm btn-info">Edit</a>
                    @if(!$u->deleted_at)
                        <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Delete user?')">Delete</button>
                        </form>
                    @else
                        <span class="text-muted">Deleted</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mb-3">
        <button class="btn btn-success" onclick="return confirm('Restore selected users?')">Restore Selected</button>
    </div>

    </form>

    {{ $users->links() }}
    
</div>
@endsection
