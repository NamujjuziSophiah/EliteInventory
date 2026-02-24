@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1>Sales Report</h1>

    <div class="mb-3 d-flex gap-2 align-items-center">
        <form class="d-flex gap-2" method="GET" action="{{ route('reports.purchases') }}">
            <label class="small text-muted">From</label>
            <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
            <label class="small text-muted">To</label>
            <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
            <button class="btn btn-primary btn-sm">Filter</button>
        </form>
        <a href="{{ route('manager.reports.purchases') ?? route('reports.purchases') }}?from={{ $from }}&to={{ $to }}" class="btn btn-outline-secondary btn-sm ms-auto">Export CSV</a>
    </div>

    <div class="card border-0 shadow-sm mb-3">
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

    <div class="card">
        <div class="card-body">
            <h5 class="mb-3">Raw sales rows ({{ $rows->count() }})</h5>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>ID</th><th>Date</th><th>Total</th><th>Payment</th></tr></thead>
                    <tbody>
                    @foreach($rows as $r)
                        <tr>
                            <td>{{ $r->id }}</td>
                            <td>{{ $r->created_at }}</td>
                            <td>{{ $r->total }}</td>
                            <td>{{ $r->payment_type ?? ($r->method ?? '') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function(){
        const ctx = document.getElementById('salesChart');
        if(!ctx) return;
        const labels = JSON.parse(ctx.dataset.labels || '[]');
        const values = JSON.parse(ctx.dataset.values || '[]');
        try{ if(ctx._chartInstance && typeof ctx._chartInstance.destroy==='function') ctx._chartInstance.destroy(); }catch(e){}
        new Chart(ctx,{
            type:'line',
            data:{ labels: labels, datasets: [{ label: 'Sales', data: values, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,0.1)', fill:true, tension:0.2 }] },
            options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, animation:false }
        });
    })();
</script>
@endpush
