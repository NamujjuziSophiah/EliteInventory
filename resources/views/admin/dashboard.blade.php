@extends('layouts.app')

@section('content')

<style>
.dashboard-app{background:#f6f8fb}

/* SIDEBAR / DRAWER */
.dashboard-sidebar{
    width:260px;
    background:#111827;
    color:#fff;
    min-height:100vh;
}
.dashboard-sidebar .nav-link{
    color:#cbd5e1;
    border-radius:8px;
    padding:.55rem .75rem;
}
.dashboard-sidebar .nav-link:hover{background:#1f2937;color:#fff}
.dashboard-sidebar .nav-link.active{background:#2563eb;color:#fff}

/* OFFCANVAS SAME STYLE */
.offcanvas-admin{background:#111827;color:#fff}
.offcanvas-admin .nav-link{color:#cbd5e1;border-radius:8px}
.offcanvas-admin .nav-link:hover{background:#1f2937;color:white}

/* CARDS */
.card{border-radius:16px}
.card-modern{border-radius:16px}

/* GRADIENTS */
.bg-gradient-blue{background:linear-gradient(135deg,#2563eb,#1d4ed8)}
.bg-gradient-orange{background:linear-gradient(135deg,#f97316,#ea580c)}
.bg-gradient-green{background:linear-gradient(135deg,#16a34a,#15803d)}
.bg-gradient-sky{background:linear-gradient(135deg,#06b6d4,#0284c7)}
.bg-gradient-reports{background:linear-gradient(135deg,#6366f1,#4338ca)}
.bg-gradient-logs{background:linear-gradient(135deg,#e5e7eb,#9ca3af)}
</style>


<div class="dashboard-app d-flex">

<!-- ===== MOBILE DRAWER ===== -->
<div class="offcanvas offcanvas-start offcanvas-admin" tabindex="-1" id="adminDrawer">

    <div class="offcanvas-header border-bottom border-secondary">
        <h5 class="mb-0">Admin Panel</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body">

        @includeWhen(false,'') {{-- prevents blade empty warning --}}

        <nav class="nav flex-column gap-1">

            <a class="nav-link {{ request()->routeIs('admin.dashboard')?'active':'' }}"
               href="{{ route('admin.dashboard') }}">Overview</a>

            <a class="nav-link {{ request()->routeIs('admin.products.*')?'active':'' }}"
               href="{{ route('admin.products.index') }}">Products</a>

            <a class="nav-link {{ request()->routeIs('admin.categories.*')?'active':'' }}"
               href="{{ route('admin.categories.index') }}">Categories</a>

            <a class="nav-link {{ request()->routeIs('admin.customers.*')?'active':'' }}"
               href="{{ route('admin.customers.index') }}">Customers</a>

            <a class="nav-link {{ request()->routeIs('admin.reports.*')?'active':'' }}"
               href="{{ route('admin.reports.index') }}">Reports</a>

            <a class="nav-link {{ request()->routeIs('admin.audit.*')?'active':'' }}"
               href="{{ route('admin.audit.index') }}">Audit Logs</a>

            <a class="nav-link {{ request()->routeIs('admin.settings.*')?'active':'' }}"
               href="{{ route('admin.settings.edit') }}">Settings</a>

        </nav>

        <form action="{{ route('logout') }}" method="POST" class="mt-4">
            @csrf
            <button class="btn btn-outline-danger w-100 btn-sm">Logout</button>
        </form>

    </div>
</div>


<!-- ===== DESKTOP SIDEBAR ===== -->
<aside class="dashboard-sidebar d-none d-md-block">
    <div class="p-3">

        <h5 class="fw-bold mb-3">Admin Panel</h5>

        <nav class="nav flex-column gap-1">

            <a class="nav-link {{ request()->routeIs('admin.dashboard')?'active':'' }}"
               href="{{ route('admin.dashboard') }}">Overview</a>

            <a class="nav-link {{ request()->routeIs('admin.products.*')?'active':'' }}"
               href="{{ route('admin.products.index') }}">Products</a>

            <a class="nav-link {{ request()->routeIs('admin.categories.*')?'active':'' }}"
               href="{{ route('admin.categories.index') }}">Categories</a>

            <a class="nav-link {{ request()->routeIs('admin.customers.*')?'active':'' }}"
               href="{{ route('admin.customers.index') }}">Customers</a>

            <a class="nav-link {{ request()->routeIs('admin.reports.*')?'active':'' }}"
               href="{{ route('admin.reports.index') }}">Reports</a>

            <a class="nav-link {{ request()->routeIs('admin.audit.*')?'active':'' }}"
               href="{{ route('admin.audit.index') }}">Audit Logs</a>

            <a class="nav-link {{ request()->routeIs('admin.settings.*')?'active':'' }}"
               href="{{ route('admin.settings.edit') }}">Settings</a>

        </nav>

        <form action="{{ route('logout') }}" method="POST" class="mt-4">
            @csrf
            <button class="btn btn-outline-danger w-100 btn-sm">Logout</button>
        </form>

    </div>
</aside>


<!-- ===== MAIN ===== -->
<main class="flex-fill p-3">

    <!-- MOBILE TOPBAR -->
    <div class="d-md-none mb-3">
        <button class="btn btn-outline-secondary" data-bs-toggle="offcanvas" data-bs-target="#adminDrawer">
            ☰ Menu
        </button>
    </div>

    <div class="container-fluid">

        <!-- ===== STATS ===== -->
        <div class="row g-3">

            <div class="col-md-3">
                <div class="card text-white bg-gradient-blue p-3">
                    <h6>Sales</h6>
                    <div class="display-6">{{ $totalSales ?? 0 }}</div>
                    <small>Transactions</small>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-gradient-orange p-3">
                    <h6>Revenue</h6>
                    <div class="display-6">{{ format_currency($totalSalesValue ?? 0) }}</div>
                    <small>Total value</small>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-gradient-green p-3">
                    <h6>Today's Sales</h6>
                    <div class="display-6">{{ format_currency($todaysSalesValue ?? 0) }}</div>
                    <small>{{ $todaysSalesCount ?? 0 }} tx</small>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-gradient-sky p-3">
                    <h6>Products</h6>
                    <div class="display-6">{{ $totalProducts ?? 0 }}</div>
                </div>
            </div>

        </div>


        <!-- ===== CHARTS ===== -->
        <div class="row g-3 mt-2">

            <div class="col-lg-4">
                <div class="card p-3">
                    <h5>Sales last 7 days</h5>
                    <div style="height:220px">
                        <canvas id="salesPie"
                            data-labels='@json($salesTrendLabels ?? [])'
                            data-values='@json($salesTrendData ?? [])'></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card p-3">
                    <h5>Low stock (below 5)</h5>
                    <div style="height:220px">
                        <canvas id="lowStockDoughnut"
                            data-labels='@json(collect($lowStock ?? [])->pluck("name"))'
                            data-values='@json(collect($lowStock ?? [])->pluck($productQuantityColumn ?? "stock"))'>
                        </canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card p-3">
                    <h5>Overstock (100 and above)</h5>
                    <div style="height:220px">
                        <canvas id="overStockDoughnut"
                            data-labels='@json(collect($overStock ?? [])->pluck("name"))'
                            data-values='@json(collect($overStock ?? [])->pluck($productQuantityColumn ?? "stock"))'>
                        </canvas>
                    </div>
                </div>
            </div>

        </div>

        <!-- ===== STOCK LISTS ===== -->
        <div class="row g-3 mt-2">

            <div class="col-lg-6">
                <div class="card p-3">
                    <h5>Products to restock (below 5)</h5>
                    @php
                        if (is_array($lowStock ?? null)) {
                            $topLowStock = array_slice($lowStock, 0, 12);
                        } elseif (($lowStock ?? null) instanceof \Illuminate\Support\Collection) {
                            $topLowStock = $lowStock->slice(0,12)->all();
                        } else {
                            $topLowStock = [];
                        }
                    @endphp
                    <ul class="list-unstyled mt-2 mb-0">
                        @forelse($topLowStock as $p)
                            <li class="py-1">{{ data_get($p, 'name', 'Unnamed') }} — {{ $productQuantityColumn ? data_get($p, $productQuantityColumn, '0') : data_get($p, 'stock', '0') }}</li>
                        @empty
                            <li class="text-muted">No low-stock products</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card p-3">
                    <h5>Overstocked products (100 and above)</h5>
                    @php
                        if (is_array($overStock ?? null)) {
                            $topOverStock = array_slice($overStock, 0, 12);
                        } elseif (($overStock ?? null) instanceof \Illuminate\Support\Collection) {
                            $topOverStock = $overStock->slice(0,12)->all();
                        } else {
                            $topOverStock = [];
                        }
                    @endphp
                    <ul class="list-unstyled mt-2 mb-0">
                        @forelse($topOverStock as $p)
                            <li class="py-1">{{ data_get($p, 'name', 'Unnamed') }} — {{ $productQuantityColumn ? data_get($p, $productQuantityColumn, '0') : data_get($p, 'stock', '0') }}</li>
                        @empty
                            <li class="text-muted">No overstock products</li>
                        @endforelse
                    </ul>
                </div>
            </div>

        </div>


        <!-- ===== QUICK LINKS ===== -->
        <div class="row g-3 mt-2">

            <div class="col-md-3">
                <a href="{{ route('admin.products.index') }}" class="text-decoration-none">
                    <div class="card bg-info text-white p-3">
                        <h6>Products</h6>
                        <div class="display-6">{{ $totalProducts ?? 0 }}</div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ route('admin.reports.index') }}" class="text-decoration-none">
                    <div class="card bg-gradient-reports text-white p-3">
                        <h6>Reports</h6>
                        <div class="display-6">Open</div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ Route::has('manager.dashboard')?route('manager.dashboard'):'#' }}" class="text-decoration-none">
                    <div class="card bg-secondary text-white p-3">
                        <h6>Manager</h6>
                        <div class="display-6">Go</div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ Route::has('cashier.dashboard')?route('cashier.dashboard'):'#' }}" class="text-decoration-none">
                    <div class="card bg-dark text-white p-3">
                        <h6>Cashier</h6>
                        <div class="display-6">Go</div>
                    </div>
                </a>
            </div>

        </div>


        <!-- ===== PROFIT ===== -->
        <div class="card p-3 mt-3">
            <h6>Gross Profit</h6>
            <div class="display-6">{{ format_currency($grossProfit ?? 0) }}</div>
            <small class="text-muted">Margin {{ number_format($grossMarginPercent ?? 0,2) }}%</small>
        </div>

    </div>
</main>
</div>
@endsection



@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function colours(n){
    const c=['#4dc9f6','#f67019','#f53794','#537bc4','#acc236','#00a950','#58595b','#8549ba'];
    return Array.from({length:n},(_,i)=>c[i%c.length]);
}

function makeChart(id,type){
    const el=document.getElementById(id);
    if(!el)return;
    const labels=JSON.parse(el.dataset.labels||'[]');
    const values=JSON.parse(el.dataset.values||'[]').map(v=>Number(v)||0);

    new Chart(el.getContext('2d'),{
        type:type,
        data:{labels:labels,datasets:[{data:values,backgroundColor:colours(values.length)}]},
        options:{responsive:true,maintainAspectRatio:false}
    });
}

document.addEventListener('DOMContentLoaded',()=>{
    makeChart('salesPie','pie');
    makeChart('lowStockDoughnut','doughnut');
    makeChart('overStockDoughnut','doughnut');
});
</script>
@endpush
