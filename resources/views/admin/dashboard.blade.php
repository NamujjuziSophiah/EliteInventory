@extends('layouts.app')

@section('content')
<div class="dashboard-app d-flex vh-100">
    <!-- Offcanvas sidebar for smaller screens / drawer -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="adminDrawer" aria-labelledby="adminDrawerLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="adminDrawerLabel">Admin</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <div class="p-3 w-100">
                <div class="d-flex align-items-center mb-3">
                    <div>
                        <h5 class="mb-0">Admin Dashboard</h5>
                        <div class="small text-muted">Overview</div>
                    </div>
                </div>

                <nav class="nav flex-column mb-3">
                    <a class="nav-link" href="{{ route('admin.dashboard') }}">Overview</a>
                    <a class="nav-link" href="{{ route('admin.products.index') }}">Products</a>
                    <a class="nav-link" href="{{ route('admin.categories.index') }}">Categories</a>
                    <a class="nav-link" href="{{ route('admin.customers.index') }}">Customers</a>
                    <a class="nav-link" href="{{ route('admin.reports.index') }}">Reports</a>
                    <a class="nav-link" href="{{ route('admin.audit.index') }}">Audit Logs</a>
                    <a class="nav-link" href="{{ route('admin.settings.edit') }}">Settings</a>
                    <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); window.history.back();">Back</a>
                </nav>

                <!-- Logout (visible inside admin sidebar for convenience) -->
                <form id="admin-logout-form" action="{{ route('logout') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">Logout</button>
                </form>

                <div class="mt-4 small text-muted">Quick actions</div>
                <div class="d-flex flex-column gap-2 mt-2">
                    <a href="{{ route('admin.reports.export', ['date_from' => now()->subDays(30)->toDateString(), 'date_to' => now()->toDateString()]) }}" class="btn btn-sm btn-outline-success">Export P&L (30d)</a>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-primary">Manage Products</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Static sidebar for wide screens -->
    <aside class="d-none d-md-block dashboard-sidebar">
        <div class="p-3">
            <div class="d-flex align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Admin Dashboard</h5>
                    <div class="small text-muted">Overview</div>
                </div>
            </div>

            <nav class="nav flex-column mb-3">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">Overview</a>
                <a class="nav-link" href="{{ route('admin.products.index') }}">Products</a>
                <a class="nav-link" href="{{ route('admin.categories.index') }}">Categories</a>
                <a class="nav-link" href="{{ route('admin.customers.index') }}">Customers</a>
                <a class="nav-link" href="{{ route('admin.reports.index') }}">Reports</a>
                <a class="nav-link" href="{{ route('admin.audit.index') }}">Audit Logs</a>
                <a class="nav-link" href="{{ route('admin.settings.edit') }}">Settings</a>
                <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); window.history.back();">Back</a>
            </nav>

            <!-- Logout for desktop sidebar -->
            <form id="admin-logout-form-desktop" action="{{ route('logout') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger w-100">Logout</button>
            </form>

            <div class="mt-4 small text-muted">Quick actions</div>
            <div class="d-flex flex-column gap-2 mt-2">
                <a href="{{ route('admin.reports.export', ['date_from' => now()->subDays(30)->toDateString(), 'date_to' => now()->toDateString()]) }}" class="btn btn-sm btn-outline-success">Export P&L (30d)</a>
                <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-primary">Manage Products</a>
            </div>
        </div>
    </aside>

    <!-- Main content -->
    <main class="flex-fill p-3 overflow-auto">
        <!-- Topbar for toggling drawer on small screens -->
        <div class="d-flex align-items-center mb-3 d-md-none">
            <button class="btn btn-outline-secondary me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminDrawer" aria-controls="adminDrawer">
                <span class="navbar-toggler-icon"></span>
            </button>
            <h5 class="mb-0">Admin Dashboard</h5>
        </div>
        <div class="container-fluid">
            <div class="row g-3">
                <!-- Stats Cards (styled with gradients) -->
                <div class="col-12 col-md-3">
                    <div class="card text-white p-3 h-100 bg-gradient-blue">
                        <h6 class="mb-1">Sales</h6>
                        <div class="display-6">{{ $totalSales ?? 0 }}</div>
                        <div class="small">Transactions</div>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="card text-white p-3 h-100 bg-gradient-orange">
                        <h6 class="mb-1">Revenue</h6>
                        <div class="display-6">{{ format_currency($totalSalesValue ?? 0) }}</div>
                        <div class="small">Total sales value</div>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="card text-white p-3 h-100 bg-gradient-green">
                        <h6 class="mb-1">Today's Sales</h6>
                        <div class="display-6">{{ format_currency($todaysSalesValue ?? 0) }}</div>
                        <div class="small">({{ $todaysSalesCount ?? 0 }} tx)</div>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="card text-white p-3 h-100 bg-gradient-sky">
                        <h6 class="mb-1">Products</h6>
                        <div class="display-6">{{ $totalProducts ?? 0 }}</div>
                        <div class="small">Total products</div>
                    </div>
                </div>

                <!-- Charts Section (pie) -->
                <div class="col-12 col-lg-6">
                    <div class="card p-3 h-100">
                        <h5>Sales (last 7 days)</h5>
                        <div style="height:200px"><canvas id="salesPie" data-labels='@json($salesTrendLabels ?? [])' data-values='@json($salesTrendData ?? [])'></canvas></div>
                    </div>
                </div>

                <!-- Low stock card (doughnut) -->
                <div class="col-12 col-lg-6">
                    <div class="card p-3 h-100">
                        <h5>Low stock</h5>
                        @php
                            $lsLabels = [];
                            $lsValues = [];
                            foreach($lowStock ?? [] as $p) {
                                $lsLabels[] = data_get($p, 'name', 'Unnamed');
                                $lsValues[] = $productQuantityColumn ? data_get($p, $productQuantityColumn, 0) : data_get($p, 'stock', 0);
                            }
                            // produce a top-8 array that works whether $lowStock is array or Collection
                            if (is_array($lowStock ?? null)) {
                                $topLowStock = array_slice($lowStock, 0, 8);
                            } elseif (($lowStock ?? null) instanceof \Illuminate\Support\Collection) {
                                $topLowStock = $lowStock->slice(0,8)->all();
                            } else {
                                $topLowStock = [];
                            }
                        @endphp
                        <div class="row g-3 align-items-center">
                            <div class="col-6" style="min-height:180px">
                                <canvas id="lowStockDoughnut" data-labels='@json($lsLabels)' data-values='@json($lsValues)'></canvas>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Top low-stock items</div>
                                <ul class="list-unstyled mt-2 mb-0">
                                    @forelse($topLowStock as $p)
                                        <li class="py-1">{{ data_get($p, 'name', 'Unnamed') }} — {{ $productQuantityColumn ? data_get($p, $productQuantityColumn, '0') : data_get($p, 'stock', '0') }}</li>
                                    @empty
                                        <li class="text-muted">No low-stock products</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clickable quick-cards linking to full feature pages -->
                <div class="col-12">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <a href="{{ Route::has('admin.sales.index') ? route('admin.sales.index') : '#' }}" class="text-decoration-none">
                                <div class="card bg-primary text-white p-3 h-100">
                                    <h6 class="mb-1">Sales</h6>
                                    <div class="display-6">{{ $totalSales ?? 0 }}</div>
                                    <div class="small">View & export sales</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ Route::has('admin.purchases.index') ? route('admin.purchases.index') : '#' }}" class="text-decoration-none">
                                <div class="card bg-success text-white p-3 h-100">
                                    <h6 class="mb-1">Purchases</h6>
                                    <div class="display-6">{{ $totalPurchases ?? 0 }}</div>
                                    <div class="small">Manage purchases</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ Route::has('admin.products.index') ? route('admin.products.index') : '#' }}" class="text-decoration-none">
                                <div class="card bg-info text-white p-3 h-100">
                                    <h6 class="mb-1">Products</h6>
                                    <div class="display-6">{{ $totalProducts ?? 0 }}</div>
                                    <div class="small">Manage products</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ Route::has('admin.customers.index') ? route('admin.customers.index') : '#' }}" class="text-decoration-none">
                                <div class="card bg-warning text-dark p-3 h-100">
                                    <h6 class="mb-1">Customers</h6>
                                    <div class="display-6">{{ $totalUsers ?? 0 }}</div>
                                    <div class="small">Manage customers</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ Route::has('admin.reports.index') ? route('admin.reports.index') : '#' }}" class="text-decoration-none">
                                <div class="card p-3 h-100 bg-gradient-reports text-white">
                                    <h6 class="mb-1">Reports</h6>
                                    <div class="display-6">Reports</div>
                                    <div class="small">View analytics & exports</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ Route::has('admin.user_logs.index') ? route('admin.user_logs.index') : '#' }}" class="text-decoration-none">
                                <div class="card p-3 h-100 bg-gradient-logs text-dark">
                                    <h6 class="mb-1">Logs</h6>
                                    <div class="display-6">Upload</div>
                                    <div class="small">Upload or view custom logs</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ Route::has('admin.manager.dashboard') ? route('admin.manager.dashboard') : (Route::has('manager.dashboard') ? route('manager.dashboard') : '#') }}" class="text-decoration-none">
                                <div class="card p-3 h-100 bg-secondary text-white">
                                    <h6 class="mb-1">Manager</h6>
                                    <div class="display-6">Go</div>
                                    <div class="small">Open Manager Dashboard</div>
                                </div>
                            </a>
                        </div>

                        <div class="col-6 col-md-3">
                            <a href="{{ Route::has('cashier.dashboard') ? route('cashier.dashboard') : '#' }}" class="text-decoration-none">
                                <div class="card p-3 h-100 bg-dark text-white">
                                    <h6 class="mb-1">Cashier</h6>
                                    <div class="display-6">Go</div>
                                    <div class="small">Open Cashier Dashboard</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Extra graphs area below quick-cards -->
                <div class="col-12 mt-3">
                    <div class="card p-3">
                        <h6 class="mb-3">Additional Charts</h6>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div style="height:220px"><canvas id="salesByCategory" data-labels='@json($salesByCategoryLabels ?? [])' data-values='@json($salesByCategoryData ?? [])'></canvas></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div style="height:220px"><canvas id="revenueByPayment" data-labels='@json($revenuePaymentLabels ?? [])' data-values='@json($revenuePaymentData ?? [])'></canvas></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Stats -->
                <div class="col-12 col-md-4">
                    <div class="card card-modern p-3 h-100">
                        <h6>Gross Profit</h6>
                        <div class="display-6">{{ format_currency($grossProfit ?? 0) }}</div>
                        <div class="small text-muted">Gross margin: {{ number_format($grossMarginPercent ?? 0, 2) }}%</div>
                    </div>
                </div>

                        </div>
            </div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    function coloursFor(n) {
        const base = [
            '#4dc9f6','#f67019','#f53794','#537bc4','#acc236','#166a8f','#00a950','#58595b','#8549ba'
        ];
        const out = [];
        for (let i=0;i<n;i++) out.push(base[i % base.length]);
        return out;
    }

    const initPie = () => {
        const canvas = document.getElementById('salesPie');
        if (!canvas) return;
        const labels = JSON.parse(canvas.dataset.labels || '[]');
        const values = JSON.parse(canvas.dataset.values || '[]').map(v => Number(v) || 0);
        const ctx = canvas.getContext('2d');
        if (!ctx) return;
        if (canvas._chartInstance && typeof canvas._chartInstance.destroy === 'function') canvas._chartInstance.destroy();
        canvas._chartInstance = new Chart(ctx, {
            type: 'pie',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: coloursFor(values.length),
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, animation: { duration: 400, loop: false } }
        });
    };

    const initExtraCharts = () => {
        // salesByCategory (bar)
        const byCat = document.getElementById('salesByCategory');
        if (byCat) {
            const labels = JSON.parse(byCat.dataset.labels || '[]');
            const values = JSON.parse(byCat.dataset.values || '[]').map(v => Number(v) || 0);
            const ctx = byCat.getContext('2d');
            if (byCat._chartInstance && typeof byCat._chartInstance.destroy === 'function') byCat._chartInstance.destroy();
            byCat._chartInstance = new Chart(ctx, {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Sales', data: values, backgroundColor: coloursFor(values.length) }] },
                options: { responsive: true, maintainAspectRatio: false, animation: { duration: 400, loop: false } }
            });
        }

        // revenueByPayment (doughnut)
        const byPay = document.getElementById('revenueByPayment');
        if (byPay) {
            const labels = JSON.parse(byPay.dataset.labels || '[]');
            const values = JSON.parse(byPay.dataset.values || '[]').map(v => Number(v) || 0);
            const ctx = byPay.getContext('2d');
            if (byPay._chartInstance && typeof byPay._chartInstance.destroy === 'function') byPay._chartInstance.destroy();
            byPay._chartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: { labels, datasets: [{ data: values, backgroundColor: coloursFor(values.length) }] },
                options: { responsive: true, maintainAspectRatio: false, animation: { duration: 400, loop: false } }
            });
        }
    };

    const initLowStock = () => {
        const canvas = document.getElementById('lowStockDoughnut');
        if (!canvas) return;
        const labels = JSON.parse(canvas.dataset.labels || '[]');
        const values = JSON.parse(canvas.dataset.values || '[]').map(v => Number(v) || 0);
        const ctx = canvas.getContext('2d');
        if (!ctx) return;
        if (canvas._chartInstance && typeof canvas._chartInstance.destroy === 'function') canvas._chartInstance.destroy();
        canvas._chartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{ data: values, backgroundColor: coloursFor(values.length) }]
            },
            options: { responsive: true, maintainAspectRatio: false, animation: { duration: 400, loop: false } }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => { initPie(); initExtraCharts(); initLowStock(); }, { once: true });
    } else {
        initPie(); initExtraCharts(); initLowStock();
    }
})();
</script>
@endpush