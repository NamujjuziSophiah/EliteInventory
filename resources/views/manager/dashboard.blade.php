@extends('layouts.app')

@section('content')
<div class="dashboard-app">
    <div class="d-flex align-items-start mb-3">
        <button class="btn btn-outline-secondary me-2 d-none d-md-inline" data-bs-toggle="offcanvas" data-bs-target="#managerDrawer" aria-controls="managerDrawer">
            <i class="fa fa-bars"></i>
        </button>
        <h1 class="mb-0">Manager Dashboard</h1>
    </div>

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
    <div id="managerDrawerContent" style="margin-top:1rem; display:none;">
        <div id="drawer-section-overview" class="drawer-section">
            <h6 class="mb-2">Overview</h6>
            <div class="small-muted">Total products</div>
            <div class="h4 mb-2">{{ $totalProducts ?? 0 }}</div>
            <div class="small-muted">Low stock</div>
            <div class="h4 mb-2">{{ count($lowStock ?? []) }}</div>
            <div class="small-muted">Recent restocks</div>
            <div class="h4 mb-2">{{ count($recentRestocks ?? []) }}</div>
        </div>

        <div id="drawer-section-lowstock" class="drawer-section">
            <h6 class="mb-2">Low stock items</h6>
            @if(count($lowStock) === 0)
                <p class="text-muted">No low stock items.</p>
            @else
                <ul class="list-unstyled">
                @foreach($lowStock as $p)
                    <li class="mb-2">
                        <div class="fw-bold">{{ data_get($p, 'name', data_get($p, 'product_name', 'n/a')) }}</div>
                        <small class="text-muted">{{ data_get($p, $stockColumn, 'n/a') }} in stock</small>
                    </li>
                @endforeach
                </ul>
            @endif
        </div>

        <div id="drawer-section-purchases" class="drawer-section">
            <h6 class="mb-2">Recent Purchases</h6>
            @include('partials.transactions-table', ['rows' => $recentPurchases])
        </div>
    </div>
        </div>
    </div>

    <!-- Main content area that will be updated when manager menu items are clicked -->
    <style>
    /* Show manager sections by default so all content appears on the dashboard */
    #managerMainContent .manager-section { display: block; }
    /* hide the placeholder since sections are visible */
    #managerMainContent .manager-placeholder { display: none; }
    #managerMainContent .manager-placeholder { padding: 3rem; text-align: center; color: #6c757d; }
    </style>

    <div id="managerMainContent">
        <div id="section-overview" class="manager-section">
            <div class="row g-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">Overview</h4>
                        <small class="text-muted">Last updated: {{ now()->diffForHumans() }}</small>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card shadow-sm rounded-lg p-3 kpi-card">
                        <div class="d-flex align-items-center">
                            <div class="me-3 display-6 text-primary"><i class="fa fa-box"></i></div>
                            <div>
                                <div class="small text-muted">Total products</div>
                                <div class="h3 mb-0">{{ $totalProducts ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card shadow-sm rounded-lg p-3 kpi-card">
                        <div class="d-flex align-items-center">
                            <div class="me-3 display-6 text-warning"><i class="fa fa-exclamation-triangle"></i></div>
                            <div>
                                <div class="small text-muted">Low stock</div>
                                <div class="h3 mb-0">{{ count($lowStock ?? []) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card shadow-sm rounded-lg p-3 kpi-card">
                        <div class="d-flex align-items-center">
                            <div class="me-3 display-6 text-success"><i class="fa fa-truck"></i></div>
                            <div>
                                <div class="small text-muted">Recent restocks</div>
                                <div class="h3 mb-0">{{ count($recentRestocks ?? []) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card shadow-sm rounded-lg p-3 kpi-card">
                        <div class="d-flex align-items-center">
                            <div class="me-3 display-6 text-info"><i class="fa fa-handshake"></i></div>
                            <div>
                                <div class="small text-muted">Top suppliers</div>
                                <div class="h3 mb-0">{{ count($supplierSpend ?? []) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="section-lowstock" class="manager-section">
            <div class="row mt-4">
                <div class="col-lg-8">
                    <div class="card p-3">
                        <h5 class="mb-3">Sales (last 7 days)</h5>
                        <canvas id="salesChart" height="120" data-labels='@json($salesTrendLabels ?? [])' data-values='@json($salesTrendData ?? [])'></canvas>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card p-3">
                        <h5 class="mb-3">Low stock items</h5>
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
                                        <a href="{{ route('manager.purchases.create') }}" class="btn btn-sm btn-primary">Restock</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div id="section-purchases" class="manager-section">
            <div class="row mt-4">
                <div class="col-lg-6">
                    <div class="card p-3">
                        <h5 class="mb-3">Recent Purchases</h5>
                        @include('partials.transactions-table', ['rows' => $recentPurchases])
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card p-3">
                        <h5 class="mb-3">Top Supplier Spend</h5>
                        @if(($supplierSpend ?? collect())->count() === 0)
                            <p class="text-muted">No supplier spend yet.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead><tr><th>Supplier</th><th>Total</th></tr></thead>
                                    <tbody>
                                        @foreach($supplierSpend as $row)
                                            <tr>
                                                <td>{{ $row->supplier_name ?? 'Unknown' }}</td>
                                                <td>{{ format_currency($row->total_spend ?? 0) }}</td>
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

        <div class="manager-placeholder" id="managerPlaceholder">Select a section from the menu</div>


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
