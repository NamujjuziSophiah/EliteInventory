@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>Edit User</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-control" required>
                <option value="admin" {{ $user->role==='admin' ? 'selected' : '' }}>Admin</option>
                <option value="manager" {{ $user->role==='manager' ? 'selected' : '' }}>Manager</option>
                <option value="cashier" {{ $user->role==='cashier' ? 'selected' : '' }}>Cashier</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Password (leave blank to keep current)</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>

        <button class="btn btn-primary">Save</button>
    </form>

    @if($user->trashed())
        <form method="POST" action="{{ route('admin.users.restore', $user->id) }}" class="mt-3">
            @csrf
            <button class="btn btn-success" onclick="return confirm('Restore user?')">Restore User</button>
        </form>
    @endif

</div>
@endsection
