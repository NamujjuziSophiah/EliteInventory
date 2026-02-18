@extends('layouts.app')

@section('content')
<div class="container hero">
    <div class="row align-items-center">
        <div class="col-12 col-md-6">
            <h1 class="hero-title display-5">Manage inventory, sales and customers with confidence</h1>
            <p class="lead text-muted">Lightweight, fast and designed for small businesses. Track stock, view reports and run point-of-sale operations from a single dashboard.</p>
            <div class="mt-4">
                <a href="{{ route('register') }}" class="btn btn-lg btn-primary me-2">Get started — Register</a>
                <a href="{{ route('login') }}" class="btn btn-lg btn-outline-primary">Sign in</a>
            </div>
        </div>
        <div class="col-12 col-md-6 d-none d-md-block text-center">
            <div class="shadow rounded p-4" style="background:linear-gradient(180deg,#fff,#f7fbff);">
                <img src="https://images.unsplash.com/photo-1506619216599-9d16d0903dfd?auto=format&fit=crop&w=900&q=80" alt="Warehouse shelves" style="width:100%;height:320px;object-fit:cover;border-radius:12px;">
            </div>
        </div>
    </div>
</div>
@endsection
