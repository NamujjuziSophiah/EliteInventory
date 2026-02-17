@extends('layouts.app')

@section('content')
<div class="dashboard-app d-flex vh-100">

    <!-- Drawer (Bootstrap offcanvas for small screens) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="managerDrawer" aria-labelledby="managerDrawerLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="managerDrawerLabel">Actions</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="list-group">
                <a href="#section-overview" data-section="section-overview" class="list-group-item list-group-item-action">Overview</a>
                <a href="{{ route('manager.products.index') }}" class="list-group-item list-group-item-action">Products</a>
                <a href="{{ route('manager.categories.index') }}" class="list-group-item list-group-item-action">Categories</a>
                <a href="{{ route('manager.suppliers.index') }}" class="list-group-item list-group-item-action">Suppliers</a>
                <a href="{{ route('manager.purchases.index') }}" class="list-group-item list-group-item-action">Purchases</a>
                <a href="{{ route('manager.sales.index') }}" class="list-group-item list-group-item-action">Sales (read-only)</a>
                <a href="{{ route('reports.purchases') }}" class="list-group-item list-group-item-action">Purchase Reports</a>
            </div>
        </div>

    <!-- Static sidebar for wider screens -->
    <aside class="d-none d-md-block dashboard-sidebar p-3">
        <div class="mb-3">
            <h5 class="mb-0">Manager</h5>
            <div class="small text-muted">Navigation</div>
        </div>
        <nav class="nav flex-column">
            <a href="#section-overview" data-section="section-overview" class="nav-link">Overview</a>
            <a href="{{ route('manager.products.index') }}" class="nav-link">Products</a>
            <a href="{{ route('manager.categories.index') }}" class="nav-link">Categories</a>
            <a href="{{ route('manager.suppliers.index') }}" class="nav-link">Suppliers</a>
            <a href="{{ route('manager.purchases.index') }}" class="nav-link">Purchases</a>
            <a href="{{ route('manager.sales.index') }}" class="nav-link">Sales (read-only)</a>
            <a href="{{ route('reports.purchases') }}" class="nav-link">Purchase Reports</a>
        </nav>
        <hr>
        <small class="text-muted">Sales creation via POS (Cashiers & Admins only)</small>
    </aside>
    <div class="modal fade" id="managerReceiptModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Receipt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="managerReceiptModalBody">
                    <div class="text-center text-muted">Loading...</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="managerReceiptPrintBtn">Print</button>
                </div>
            </div>
        </div>
    </div>

    {{-- manager JS loaded externally for maintainability --}}

    <!-- Main content area that will be updated when manager menu items are clicked -->
    <style>
    /* Show manager sections by default so all content appears on the dashboard */
    #managerMainContent .manager-section { display: block; }
    /* hide the placeholder since sections are visible */
    #managerMainContent .manager-placeholder { display: none; }
    #managerMainContent .manager-placeholder { padding: 3rem; text-align: center; color: #6c757d; }
    </style>

    <!-- Main content -->
    <main class="flex-fill p-3 overflow-auto">
        <!-- Topbar for small screens -->
        <div class="d-flex align-items-center mb-3 d-md-none">
            <button class="btn btn-outline-secondary me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#managerDrawer" aria-controls="managerDrawer">
                <span class="navbar-toggler-icon"></span>
            </button>
            <h5 class="mb-0">Manager Dashboard</h5>
        </div>

        <div class="container-fluid" id="managerMainContent">
            <div class="row g-3 mb-3">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Manager Dashboard</h4>
                    <small class="text-muted">Last updated: {{ now()->diffForHumans() }}</small>
                </div>
            </div>

            <!-- KPI Row -->
            <div class="row g-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card p-3 h-100">
                        <div class="d-flex align-items-center">
                            <div class="me-3 display-6 text-primary"><i class="fa fa-box"></i></div>
                            <div>
                                <div class="small-muted">Total products</div>
                                <div class="h3 mb-0">{{ $totalProducts ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card p-3 h-100">
                        <div class="d-flex align-items-center">
                            <div class="me-3 display-6 text-warning"><i class="fa fa-exclamation-triangle"></i></div>
                            <div>
                                <div class="small-muted">Low stock</div>
                                <div class="h3 mb-0">{{ count($lowStock ?? []) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card p-3 h-100">
                        <div class="d-flex align-items-center">
                            <div class="me-3 display-6 text-success"><i class="fa fa-truck"></i></div>
                            <div>
                                <div class="small-muted">Recent restocks</div>
                                <div class="h3 mb-0">{{ count($recentRestocks ?? []) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card p-3 h-100">
                        <div class="d-flex align-items-center">
                            <div class="me-3 display-6 text-info"><i class="fa fa-handshake"></i></div>
                            <div>
                                <div class="small-muted">Top suppliers</div>
                                <div class="h3 mb-0">{{ count($supplierSpend ?? []) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clickable quick-cards (moved up so they appear beside the sidebar) -->
            <div class="row g-3 mt-3">
                <div class="col-6 col-md-3">
                    <a href="{{ Route::has('manager.sales.index') ? route('manager.sales.index') : '#' }}" class="text-decoration-none">
                        <div class="card bg-primary text-white p-3 h-100">
                            <h6 class="mb-1">Sales</h6>
                            <div class="display-6">{{ $totalSales ?? 0 }}</div>
                            <div class="small">View sales</div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ Route::has('manager.purchases.index') ? route('manager.purchases.index') : '#' }}" class="text-decoration-none">
                        <div class="card bg-success text-white p-3 h-100">
                            <h6 class="mb-1">Purchases</h6>
                            <div class="display-6">{{ $totalPurchases ?? 0 }}</div>
                            <div class="small">Manage purchases</div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ Route::has('manager.products.index') ? route('manager.products.index') : '#' }}" class="text-decoration-none">
                        <div class="card bg-info text-white p-3 h-100">
                            <h6 class="mb-1">Products</h6>
                            <div class="display-6">{{ $totalProducts ?? 0 }}</div>
                            <div class="small">Manage products</div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ Route::has('manager.suppliers.index') ? route('manager.suppliers.index') : '#' }}" class="text-decoration-none">
                        <div class="card bg-warning text-dark p-3 h-100">
                            <h6 class="mb-1">Suppliers</h6>
                            <div class="display-6">{{ $totalSuppliers ?? 0 }}</div>
                            <div class="small">Manage suppliers</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Main grid: chart + side column -->
            <div class="row g-3 mt-3">
                <div class="col-lg-8">
                    <div class="card p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0">Sales (last 7 days)</h5>
                            <div class="small-muted">Trend</div>
                        </div>
                        <div style="height:320px;">
                            <canvas id="salesChart" style="width:100%;height:100%;" data-labels='@json($salesTrendLabels ?? [])' data-values='@json($salesTrendData ?? [])'></canvas>
                        </div>
                        <hr>
                        <h6 class="mb-2">Recent Purchases</h6>
                        @include('partials.transactions-table', ['rows' => $recentPurchases])
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card p-3 mb-3">
                        <h6 class="mb-2">Quick Actions</h6>
                        <div class="d-grid gap-2">
                            <a href="{{ route('manager.products.create') }}" class="btn btn-outline-primary">Add product</a>
                            <a href="{{ route('manager.purchases.create') }}" class="btn btn-primary">Create Purchase (Restock)</a>
                            <a href="{{ route('manager.suppliers.index') }}" class="btn btn-outline-secondary">Manage Suppliers</a>
                        </div>
                    </div>

                    <div class="card p-3 mb-3">
                        <h6 class="mb-2">Low stock items</h6>
                        @if(count($lowStock) === 0)
                            <p class="text-muted">No low stock items.</p>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach($lowStock as $p)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold">{{ data_get($p, 'name', data_get($p, 'product_name', 'n/a')) }}</div>
                                            <small class="text-muted">{{ data_get($p, $stockColumn, 'n/a') }} in stock</small>
                                        </div>
                                        <a href="{{ route('manager.purchases.create') }}?product_id={{ data_get($p, 'id') }}" class="btn btn-sm btn-primary">Restock</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="card p-3">
                        <h6 class="mb-2">Top Suppliers</h6>
                        @if(($supplierSpend ?? collect())->count() === 0)
                            <p class="text-muted">No supplier spend yet.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        @foreach($supplierSpend as $row)
                                            <tr>
                                                <td>{{ $row->supplier_name ?? 'Unknown' }}</td>
                                                <td class="text-end">{{ format_currency($row->total_spend ?? 0) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
// Intercept clicks inside the manager drawer and load content via AJAX into #managerMainContent
(function(){
    const drawer = document.getElementById('managerDrawer');
    const links = document.getElementById('managerDrawerLinks');
    const drawerContent = document.getElementById('managerDrawerContent');
    if (! links || ! drawerContent) return;

    links.addEventListener('click', function(e){
        const a = e.target.closest && e.target.closest('a');
        if (! a) return;
        const href = a.getAttribute('href');
        // only intercept local same-origin links and those that are not anchors
        if (! href || href.indexOf(location.origin) === 0 || href.startsWith('/')) {
            // avoid intercepting links that are JavaScript or mailto
            if (href.startsWith('mailto:') || href.startsWith('javascript:')) return;
            e.preventDefault();

            // If link has data-section or hash, show that drawer section
            const section = a.dataset.section || (href && href.startsWith('#') ? href.replace('#','') : null);
            if (section) {
                // show drawer section (support 'section-overview' -> 'drawer-section-overview')
                const targetId = document.getElementById(section) ? section : ('drawer-' + section);
                if (targetId && document.getElementById(targetId)) {
                    // hide others
                    drawerContent.querySelectorAll('.drawer-section').forEach(s => s.style.display = 'none');
                    document.getElementById(targetId).style.display = '';
                    return;
                }
            }

            // otherwise fetch into drawerContent
            fetch(href, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, credentials: 'same-origin' })
                .then(r => {
                    if (! r.ok) throw new Error('Failed to load');
                    return r.text();
                })
                .then(html => {
                    try {
                        drawerContent.innerHTML = html;
                    } catch (err) {
                        console.error('Inject error', err);
                        location.href = href; // fallback
                    }
                }).catch(err => {
                    console.error(err);
                    // fallback: navigate
                    location.href = href;
                });
        }
    });
})();
</script>
@endpush

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/manager-dashboard.js') }}"></script>
@endpush
