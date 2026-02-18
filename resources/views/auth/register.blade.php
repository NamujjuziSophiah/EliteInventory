@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6">
            <div class="card p-4">
                <h4 class="mb-3">Create an account</h4>
                @if(session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input id="name" name="name" class="form-control" required aria-label="Full name">
                    </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" name="email" type="email" class="form-control" required aria-label="Email address">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" name="password" type="password" class="form-control" required aria-label="Password">
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required aria-label="Confirm password">
                        </div>

                    <div class="mb-3">
                        <label for="roleSelect" class="form-label">Role</label>
                        <select id="roleSelect" name="role" class="form-select" required aria-label="User role">
                            <option value="admin">Admin</option>
                            <option value="manager">Manager</option>
                            <option value="cashier">Cashier</option>
                        </select>
                        <div id="roleHelp" class="form-text">Roles are single-user; unavailable roles will be disabled.</div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <button type="submit" class="btn btn-primary">Register</button>
                        <a href="{{ route('login') }}" class="btn btn-link">Already have an account? Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Disable roles that are already taken
    (async function(){
        try{
            const res = await fetch('/roles/taken');
            const data = await res.json();
            const taken = data.taken || [];
            const sel = document.getElementById('roleSelect');
            for(const opt of sel.options){
                if (taken.includes(opt.value)) {
                    opt.disabled = true;
                    opt.text += ' (taken)';
                }
            }
        }catch(e){/* ignore */}
    })();
</script>
@endpush
@endsection
