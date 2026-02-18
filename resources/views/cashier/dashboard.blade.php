@extends('layouts.app')

@section('content')
<div class="dashboard-app d-flex vh-100">
    <aside class="d-none d-md-block dashboard-sidebar p-3">
        <div class="mb-3">
            <h5 class="mb-0">Cashier</h5>
            <div class="small text-muted">Quick actions</div>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link" href="{{ route('cashier.pos') }}">Open POS</a>
            <a class="nav-link" href="{{ route('cashier.sales.index') }}">Sales History</a>
            <a class="nav-link" href="#">Reprint Receipt</a>
            <a class="nav-link" href="{{ route('password.request') }}">Change Password</a>
        </nav>
    </aside>

    <main class="flex-fill p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Cashier Dashboard</h3>
            <small class="text-muted">Updated: {{ now()->diffForHumans() }}</small>
        </div>

        @if(!empty($error))
            <div class="alert alert-warning">{{ $error }}</div>
        @endif

        <div class="row g-3">
            <div class="col-md-3">
                <div class="card text-white p-3 h-100 bg-gradient-blue">
                    <div class="small">Today's Sales</div>
                    <div class="h3 mb-0">{{ format_currency($salesTotal ?? 0) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white p-3 h-100 bg-gradient-orange">
                    <div class="small">Transactions</div>
                    <div class="h3 mb-0">{{ $salesCount ?? 0 }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-modern p-3 h-100">
                    <div class="small-muted">Low Stock Alerts</div>
                    <div class="h3 mb-0">{{ is_countable($lowStock ?? null) ? count($lowStock) : 0 }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-modern p-3 h-100">
                    <div class="small-muted">Profile</div>
                    <div class="small">Branch: {{ optional(auth()->user())->branch ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card card-modern p-3">
                    <h5>Low stock items</h5>
                    @php $ls = $lowStock ?? collect(); @endphp
                    @if($ls instanceof \Illuminate\Support\Collection ? $ls->isEmpty() : (is_countable($ls) ? count($ls) === 0 : true))
                        <p class="text-muted">No low stock items.</p>
                    @else
                        <table class="table table-sm">
                            <thead><tr><th>Name</th><th>Stock</th></tr></thead>
                            <tbody>
                            @foreach($ls as $p)
                                <tr>
                                    <td>{{ $p->name ?? $p->title ?? 'n/a' }}</td>
                                    <td>{{ $p->stock ?? $p->quantity ?? $p->qty ?? 'n/a' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-modern p-3">
                    <h5>Quick Actions</h5>
                    <div class="d-grid gap-2">
                        <a href="{{ route('cashier.pos') }}" class="btn btn-success">Open POS</a>
                        <a href="{{ route('cashier.sales.index') }}" class="btn btn-outline-secondary">Sales History</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

@endsection
