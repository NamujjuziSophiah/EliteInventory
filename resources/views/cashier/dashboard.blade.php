@extends('layouts.app')

@section('content')

<div class="d-flex min-vh-100 bg-light">

    <!-- SIDEBAR -->
    <aside class="d-none d-md-flex flex-column bg-white border-end p-3" style="width:240px">

        <h5 class="fw-semibold mb-4">Cashier Panel</h5>

        <nav class="nav flex-column gap-1">

            <a class="nav-link" href="{{ route('cashier.pos') }}">
                <i class="fa fa-cash-register me-2"></i>Point of Sale
            </a>

            <a class="nav-link" href="{{ route('cashier.sales.index') }}">
                <i class="fa fa-clock-rotate-left me-2"></i>Sales History
            </a>

            <a class="nav-link" href="#">
                <i class="fa fa-receipt me-2"></i>Reprint Receipt
            </a>

            <a class="nav-link" href="{{ route('password.request') }}">
                <i class="fa fa-key me-2"></i>Change Password
            </a>

        </nav>

    </aside>



    <!-- MAIN -->
    <main class="flex-fill p-4">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-semibold mb-0">Cashier Dashboard</h3>
            <small class="text-muted">Updated: {{ now()->diffForHumans() }}</small>
        </div>

        @if(!empty($error))
            <div class="alert alert-warning">{{ $error }}</div>
        @endif



        <!-- COLOURED SUMMARY CARDS -->
        <div class="row g-3 mb-4">

            <!-- SALES -->
            <div class="col-md-4">
                <div class="card text-white shadow-sm h-100"
                     style="background:linear-gradient(135deg,#2b7cff,#1c54d3)">
                    <div class="card-body">
                        <div class="small opacity-75">Today's Sales</div>
                        <div class="h3 mb-0">
                            {{ format_currency($salesTotal ?? 0) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- TRANSACTIONS -->
            <div class="col-md-4">
                <div class="card text-white shadow-sm h-100"
                     style="background:linear-gradient(135deg,#ff8a00,#e56b00)">
                    <div class="card-body">
                        <div class="small opacity-75">Transactions Today</div>
                        <div class="h3 mb-0">
                            {{ $salesCount ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- LOW STOCK -->
            <div class="col-md-4">
                <div class="card text-white shadow-sm h-100"
                     style="background:linear-gradient(135deg,#dc3545,#b02a37)">
                    <div class="card-body">
                        <div class="small opacity-75">Low Stock Alerts</div>
                        <div class="h3 mb-0">
                            {{ is_countable($lowStock ?? null) ? count($lowStock) : 0 }}
                        </div>
                    </div>
                </div>
            </div>

        </div>



        <!-- LOW STOCK TABLE -->
        <div class="card shadow-sm">

            <div class="card-header bg-white fw-semibold">
                Low Stock Products
            </div>

            <div class="card-body p-0">

                @php $ls = $lowStock ?? collect(); @endphp

                @if($ls instanceof \Illuminate\Support\Collection ? $ls->isEmpty() : (is_countable($ls) ? count($ls) === 0 : true))

                    <p class="text-muted p-3 mb-0">No low stock items.</p>

                @else

                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Product</th>
                                <th>Stock Remaining</th>
                            </tr>
                        </thead>

                        <tbody>

                        @foreach($ls as $p)
                            <tr>
                                <td class="ps-3">
                                    {{ $p->name ?? $p->title ?? 'n/a' }}
                                </td>
                                <td>
                                    {{ $p->stock ?? $p->quantity ?? $p->qty ?? 'n/a' }}
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>

                @endif

            </div>

        </div>

    </main>

</div>

@endsection
