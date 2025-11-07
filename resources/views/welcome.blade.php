<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Elites Inventory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .hero { padding: 6rem 0; }
        .hero-title { font-weight:700; letter-spacing: -0.02em; }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">Elites Inventory</a>
        <div class="d-flex ms-auto">
            <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">Login</a>
            <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
        </div>
    </div>
</nav>

<main class="container hero">
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
                <img src="/images/hero-inventory.svg" alt="Inventory" style="max-width:100%;height:320px;object-fit:contain;">
            </div>
        </div>
    </div>
</main>

<footer class="py-4 text-center text-muted">
    <div class="container">© {{ date('Y') }} Elites Inventory Management</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
