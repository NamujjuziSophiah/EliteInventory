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
                <!-- Stats Cards -->
                <div class="col-12 col-md-3">
                    <div class="card card-modern p-3 h-100">
                        <h6 class="mb-1">Sales</h6>
                        <div class="display-6">{{ $totalSales ?? 0 }}</div>
                        <div class="small text-muted">Transactions</div>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="card card-modern p-3 h-100">
                        <h6 class="mb-1">Revenue</h6>
                        <div class="display-6">{{ format_currency($totalSalesValue ?? 0) }}</div>
                        <div class="small text-muted">Total sales value</div>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="card card-modern p-3 h-100">
                        <h6 class="mb-1">Today's Sales</h6>
                        <div class="display-6">{{ format_currency($todaysSalesValue ?? 0) }}</div>
                        <div class="small text-muted">({{ $todaysSalesCount ?? 0 }} tx)</div>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="card card-modern p-3 h-100">
                        <h6 class="mb-1">Products</h6>
                        <div class="display-6">{{ $totalProducts ?? 0 }}</div>
                        <div class="small text-muted">Total products</div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="col-12 col-lg-8">
                    <div class="card card-modern p-3 h-100">
                        <h5>Sales (last 7 days)</h5>
                        <canvas id="salesChart" height="150" data-labels='@json($salesTrendLabels ?? [])' data-values='@json($salesTrendData ?? [])'></canvas>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card card-modern p-3 h-100">
                        <h5>Low stock</h5>
                        <ul class="list-unstyled mb-0">
                            @forelse($lowStock ?? [] as $p)
                                <li class="py-1">{{ data_get($p, 'name', 'Unnamed') }} — {{ $productQuantityColumn ? data_get($p, $productQuantityColumn, '0') : '0' }}</li>
                            @empty
                                <li class="text-muted">No low-stock products</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="card card-modern p-3 h-100">
                        <h5 class="mb-3">Recent Purchases</h5>
                        @include('partials.transactions-table', ['rows' => $recentPurchases])
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="card card-modern p-3 h-100">
                        <h5 class="mb-3">Recent Sales</h5>
                        @include('partials.transactions-table', ['rows' => $recentSales])
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
    const initialiseChart = () => {
        const canvas = document.getElementById('salesChart');
        if (!canvas) {
            return;
        }

        // If the exact payload hasn't changed since last init, skip re-creating the chart.
        const currentPayload = JSON.stringify({ labels: labelPayload, values: dataPayload });
        if (canvas.dataset.lastPayload && canvas.dataset.lastPayload === currentPayload) {
            console.debug('[admin salesChart] payload unchanged, skipping re-init');
            return;
        }

        const labelPayload = canvas.dataset.labels || '[]';
        const dataPayload = canvas.dataset.values || '[]';
        let labels = [];
        let values = [];

        try {
            labels = JSON.parse(labelPayload);
        } catch (_) {
            labels = [];
        }

        try {
            const parsed = JSON.parse(dataPayload);
            values = Array.isArray(parsed)
                ? parsed.map((value) => {
                    const numeric = Number(value);
                    return Number.isFinite(numeric) ? numeric : 0;
                })
                : [];
        } catch (_) {
            values = [];
        }

        // Dev debug: show payload sizes
        try {
            console.debug('[admin salesChart] initialise called', { labelsCount: labels.length, valuesCount: values.length, payloadPreview: { labels: labels.slice(0,5), values: values.slice(0,5) } });
        } catch (e) { /* ignore */ }

        const ctx = canvas.getContext('2d');
        if (!ctx) {
            return;
        }

        if (canvas._chartInstance && typeof canvas._chartInstance.destroy === 'function') {
            try { console.debug('[admin salesChart] destroying previous chart'); } catch (e) {}
            canvas._chartInstance.destroy();
        }

        canvas._chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Revenue',
                    data: values,
                    borderColor: 'rgba(54,162,235,1)',
                    backgroundColor: 'rgba(54,162,235,0.1)',
                    fill: true,
                    tension: 0.2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                    },
                },
            },
        });

        try {
            canvas.dataset.lastPayload = currentPayload;
        } catch (e) {}
        canvas.dataset.chartInitialised = '1';
        try { console.debug('[admin salesChart] chart created'); } catch (e) {}
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialiseChart, { once: true });
    } else {
        initialiseChart();
    }

    // Listen for Turbo visits but only once to avoid accumulating listeners
    document.addEventListener('turbo:load', function onTurbo() {
        initialiseChart();
        document.removeEventListener('turbo:load', onTurbo);
    }, { once: true });

    // Mark global initialized flag after successful init
    document.addEventListener('DOMContentLoaded', () => {
        const c = document.getElementById('salesChart');
        if (c) {
            window.__chartsInitialized = window.__chartsInitialized || {};
            if (c.dataset.chartInitialised === '1') {
                window.__chartsInitialized['salesChart'] = true;
            }
        }
    }, { once: true });
})();
</script>
@endpush