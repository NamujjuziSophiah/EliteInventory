@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6">
            <div class="card p-4">
                <h4 class="mb-3">Set New Password</h4>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <input name="email" type="email" class="form-control" required value="{{ old('email') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New password</label>
                        <input name="password" type="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm password</label>
                        <input name="password_confirmation" type="password" class="form-control" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-primary">Reset password</button>
                        <a href="{{ route('login') }}" class="btn btn-link">Back to login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
