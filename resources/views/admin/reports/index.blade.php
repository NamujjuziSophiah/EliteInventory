@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>Reports</h1>

    <div class="card p-3 mb-3">
        <form method="GET" action="{{ route('admin.reports.export') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') ?: now()->subDays(30)->toDateString() }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') ?: now()->toDateString() }}">
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary">Export Profit &amp; Loss CSV</button>
                </div>
            </div>
        </form>
    </div>

    <div class="mb-3">
        <form action="{{ route('admin.reports.export') }}" method="GET" style="display:inline">
            <input type="hidden" name="type" value="sales">
            <button class="btn btn-outline-primary">Export Sales CSV</button>
        </form>

        @if(($canExport ?? false) || (auth()->check() && (auth()->user()->role ?? null) === 'admin'))
            <form action="{{ route('admin.reports.export') }}" method="GET" style="display:inline;margin-left:8px">
                <input type="hidden" name="type" value="purchases">
                <button class="btn btn-outline-secondary">Export Purchases CSV</button>
            </form>
        @else
            <button class="btn btn-outline-secondary" disabled style="margin-left:8px">Export Purchases CSV (admins only)</button>
        @endif
    </div>

    <p class="text-muted">More report types and export formats (PDF, Excel) can be added later.</p>

    @if(!empty($rows))
        <div class="card mt-3 p-3">
            <h5 class="mb-3">Recent Purchases</h5>
            @include('partials.transactions-table', ['rows' => $rows])
        </div>
    @endif

</div>
@endsection
