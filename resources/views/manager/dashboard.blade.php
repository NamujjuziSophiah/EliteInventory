@extends('layouts.app')

@section('content')

<style>

/* ===== ROOT COLORS ===== */
:root{
    --sidebar:#111827;
    --sidebar-hover:#1f2937;
    --primary:#2563eb;
    --bg:#f6f8fb;
    --card:#ffffff;
}

body.dark-mode{
    --bg:#0b1220;
    --card:#111827;
    --sidebar:#020617;
    color:#e5e7eb;
}

.dashboard-app{background:var(--bg);}

/* ===== SIDEBAR ===== */
.dashboard-sidebar{
    width:260px;
    background:var(--sidebar);
    color:white;
    transition:.25s;
}
.dashboard-sidebar .nav-link{
    color:#cbd5e1;
    border-radius:8px;
    padding:.6rem .8rem;
}
.dashboard-sidebar .nav-link:hover{
    background:var(--sidebar-hover);
    color:white;
}
.dashboard-sidebar .nav-link.active{
    background:var(--primary);
    color:white;
}

/* ===== MOBILE SIDEBAR ===== */
@media(max-width:768px){
    .dashboard-sidebar{
        position:fixed;
        left:-260px;
        top:0;
        bottom:0;
        z-index:999;
    }
    .dashboard-sidebar.show{ left:0; }
}

/* ===== TOPBAR ===== */
.topbar{
    background:var(--card);
    border-radius:14px;
    padding:12px 18px;
    margin-bottom:20px;
}

/* ===== KPI ===== */
.kpi-card-link{display:block;color:inherit;text-decoration:none;height:100%;}
.kpi-card .card{
    border-radius:16px;
    background:var(--card);
    transition:.25s;
}
.kpi-card .card:hover{
    transform:translateY(-4px);
    box-shadow:0 12px 30px rgba(0,0,0,.08)!important;
}
.kpi-icon{
    width:50px;height:50px;
    border-radius:12px;
    display:flex;align-items:center;justify-content:center;
    color:white;font-size:18px;
}

/* ===== CARDS ===== */
.card{border-radius:18px;background:var(--card);}

/* ===== NOTIFICATION DOT ===== */
.notif-dot{
    width:8px;height:8px;border-radius:50%;
    background:red;display:inline-block;margin-left:4px;
}

</style>


<div class="dashboard-app d-flex min-vh-100">

    <!-- SIDEBAR -->
    <aside id="sidebar" class="dashboard-sidebar d-flex flex-column p-3">

        <h5 class="fw-bold mb-4">Manager Panel</h5>

        <nav class="nav flex-column gap-1">

            <a href="{{ route('manager.dashboard') }}"
               class="nav-link {{ request()->routeIs('manager.dashboard')?'active':'' }}">Dashboard</a>

            <a href="{{ route('manager.products.index') }}"
               class="nav-link {{ request()->routeIs('manager.products.*')?'active':'' }}">Products</a>

            <a href="{{ route('manager.categories.index') }}"
               class="nav-link {{ request()->routeIs('manager.categories.*')?'active':'' }}">Categories</a>

            <a href="{{ route('manager.suppliers.index') }}"
               class="nav-link {{ request()->routeIs('manager.suppliers.*')?'active':'' }}">Suppliers</a>

            <a href="{{ route('manager.purchases.index') }}"
               class="nav-link {{ request()->routeIs('manager.purchases.*')?'active':'' }}">Purchases</a>

            <a href="{{ route('manager.sales.index') }}"
               class="nav-link {{ request()->routeIs('manager.sales.*')?'active':'' }}">Sales</a>

            <a href="{{ route('reports.purchases') }}"
               class="nav-link {{ request()->routeIs('reports.*')?'active':'' }}">Reports</a>

        </nav>

        <div class="mt-auto small opacity-75 pt-4">
            POS handled by Cashiers/Admins
        </div>

    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-fill p-3">

        <!-- TOPBAR -->
        <div class="topbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary d-md-none" onclick="toggleSidebar()">
                    ☰ Menu
                </button>
                <h5 class="mb-0 fw-bold">Manager Dashboard</h5>
            </div>

            <div class="d-flex align-items-center gap-3 ms-auto">

                <!-- DARK MODE -->
                <button class="btn btn-outline-secondary btn-sm" onclick="toggleDarkMode()">
                    🌙
                </button>

                <!-- NOTIFICATIONS -->
                <button class="btn btn-outline-secondary btn-sm position-relative">
                    🔔 <span class="notif-dot"></span>
                </button>

                <!-- PROFILE -->
                <div class="fw-semibold">
                    {{ auth()->user()->name ?? 'Manager' }}
                </div>

                <!-- LOGOUT -->
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fa fa-sign-out-alt me-1"></i> Logout
                    </button>
                </form>

            </div>
        </div>

        <!-- KPI -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-5 g-4 mb-4">

            <div class="col">
                <a href="{{ route('manager.products.index') }}" class="kpi-card-link" aria-label="View total products details">
                    <div class="kpi-card">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="kpi-icon bg-primary"><i class="fa fa-box"></i></div>
                                <div class="ms-3">
                                    <div class="text-muted small">Total Products</div>
                                    <div class="fs-4 fw-bold counter"
                                         data-target="{{ $totalProducts??0 }}">0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col">
                <a href="{{ route('manager.products.index', ['stock_filter' => 'low']) }}" class="kpi-card-link" aria-label="View low stock details">
                    <div class="kpi-card">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="kpi-icon bg-warning"><i class="fa fa-exclamation-triangle"></i></div>
                                <div class="ms-3">
                                    <div class="text-muted small">Low Stock (&lt;5)</div>
                                    <div class="fs-4 fw-bold counter"
                                         data-target="{{ isset($lowStock)?count($lowStock):0 }}">0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col">
                <a href="{{ route('manager.purchases.index') }}" class="kpi-card-link" aria-label="View restock details">
                    <div class="kpi-card">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="kpi-icon bg-success"><i class="fa fa-truck"></i></div>
                                <div class="ms-3">
                                    <div class="text-muted small">Restocks</div>
                                    <div class="fs-4 fw-bold counter"
                                         data-target="{{ isset($recentRestocks)?count($recentRestocks):0 }}">0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col">
                <a href="{{ route('manager.suppliers.index') }}" class="kpi-card-link" aria-label="View supplier details">
                    <div class="kpi-card">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="kpi-icon bg-info"><i class="fa fa-handshake"></i></div>
                                <div class="ms-3">
                                    <div class="text-muted small">Suppliers</div>
                                    <div class="fs-4 fw-bold counter"
                                         data-target="{{ isset($supplierSpend)?count($supplierSpend):0 }}">0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col">
                <a href="{{ route('manager.products.index', ['stock_filter' => 'overstock']) }}" class="kpi-card-link" aria-label="View overstock details">
                    <div class="kpi-card">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="kpi-icon bg-danger"><i class="fa fa-layer-group"></i></div>
                                <div class="ms-3">
                                    <div class="text-muted small">Overstock (&ge;100)</div>
                                    <div class="fs-4 fw-bold counter"
                                         data-target="{{ isset($overStock)?count($overStock):0 }}">0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

        </div>


        <!-- CHART -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Sales Trend (Last 7 Days)</h5>
                <div style="height:320px">
                    <canvas id="salesChart"
                        data-labels='@json($salesTrendLabels ?? [])'
                        data-values='@json($salesTrendData ?? [])'>
                    </canvas>
                </div>
            </div>
        </div>

    </main>
</div>

@endsection



@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

/* MOBILE SIDEBAR */
function toggleSidebar(){
    document.getElementById('sidebar').classList.toggle('show');
}

/* DARK MODE */
function toggleDarkMode(){
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode',
        document.body.classList.contains('dark-mode'));
}
if(localStorage.getItem('darkMode')==='true'){
    document.body.classList.add('dark-mode');
}

/* COUNTERS */
function animateCounters(){
    document.querySelectorAll('.counter').forEach(el=>{
        let target=+el.dataset.target,cur=0,inc=target/40;
        function update(){
            cur+=inc;
            if(cur<target){
                el.innerText=Math.floor(cur);
                requestAnimationFrame(update);
            } else el.innerText=target;
        }
        update();
    });
}
animateCounters();

/* CHART */
const ctx=document.getElementById('salesChart');
if(ctx){
    const labels=JSON.parse(ctx.dataset.labels||'[]');
    const values=JSON.parse(ctx.dataset.values||'[]');

    new Chart(ctx,{
        type:'line',
        data:{labels:labels,datasets:[{data:values,fill:true,tension:.35}]},
        options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}}}
    });
}

</script>
@endpush
