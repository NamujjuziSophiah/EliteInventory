<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Elite Retail Management') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/css/manager-ui.css" rel="stylesheet">
    <!-- Tailwind Play CDN for utility classes (complimentary to Bootstrap) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="/css/print.css" rel="stylesheet" media="print">
    <style>
        /* small color accents for dashboard cards */
        .card-accent-primary { border-left: 6px solid #0d6efd; }
        .card-accent-success { border-left: 6px solid #198754; }
        .card-accent-info { border-left: 6px solid #0dcaf0; }
        .card-accent-warning { border-left: 6px solid #ffc107; }
        .small-muted { font-size: 0.85rem; color: #6c757d; }
        /* Dashboard layout helpers */
        .dashboard-app .border-end { min-height: 100%; }
        .dashboard-app .sidebar-collapsed aside { width: 64px !important; }
        .dashboard-app .sidebar-collapsed aside nav .nav-link { display: none; }
        .dashboard-toggle-btn { cursor: pointer; }
    </style>
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center text-white" href="{{ url('/') }}">
                {{-- small inline SVG logo to avoid external image issues --}}
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                    <rect width="24" height="24" rx="4" fill="#fff" opacity="0.06"></rect>
                    <path d="M4 12h16M12 4v16" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="fw-bold">Elite Retail Management</span>
            </a>
            <button class="btn btn-sm btn-outline-light ms-2 d-md-none dashboard-toggle-btn" id="sidebarToggle" title="Toggle sidebar"><i class="fa fa-bars"></i></button>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsMain" aria-controls="navbarsMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarsMain">
                <ul class="navbar-nav ms-auto align-items-center">
                    @guest
                        <li class="nav-item"><a class="nav-link text-white" href="{{ route('login') }}">Login</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="{{ route('register') }}">Register</a></li>
                    @else
                        @if(Auth::check() && (Auth::user()->role ?? null) === 'admin')
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-white" href="#" id="adminMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">Admin</a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminMenu">
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.products.index') }}">Manage Products</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.categories.index') }}">Manage Categories</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.customers.index') }}">Customers</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.suppliers.index') }}">Suppliers</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">Users</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.settings.edit') }}">Settings</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.reports.index') }}">Reports</a></li>
                                </ul>
                            </li>
                        @endif

                        <li class="nav-item me-2 d-none d-md-block">
                            <form id="logout-form" action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-outline-light" type="submit" title="Logout"><i class="fa fa-sign-out-alt"></i> Logout</button>
                            </form>
                        </li>

                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ Auth::user()->name }}</a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4 flex-fill">
        <div class="container-fluid">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="bg-light py-2 border-top">
        <div class="container text-center small text-muted">&copy; ElitesDevelopersGroup {{ date('Y') }} — Elite Retail Management</div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/formatters.js"></script>
    {{-- Chart.js (used by reports and dashboards) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
    <script>
        (function(){
            const btn = document.getElementById('sidebarToggle');
            if (!btn) return;
            btn.addEventListener('click', function(){
                document.querySelector('.dashboard-app')?.classList.toggle('sidebar-collapsed');
                try { localStorage.setItem('dashboardSidebarCollapsed', document.querySelector('.dashboard-app').classList.contains('sidebar-collapsed') ? '1' : '0'); } catch(e){}
            });
            // restore state
            try{
                const v = localStorage.getItem('dashboardSidebarCollapsed');
                if (v === '1') document.querySelector('.dashboard-app')?.classList.add('sidebar-collapsed');
            }catch(e){}
        })();
    </script>
    @stack('scripts')
</body>
</html>
