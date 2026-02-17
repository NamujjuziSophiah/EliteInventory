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

    {{-- Profit / Loss trend chart --}}
    <div class="card mb-3 p-3">
        <h5 class="mb-3">Profit / Loss (trend)</h5>
        <div class="row g-2 align-items-end mb-3">
            <div class="col-auto">
                <form method="GET" action="{{ route('admin.reports.index') }}" id="chartFilterForm" class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label">From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') ?: now()->subDays(29)->toDateString() }}">
                    </div>
                    <div class="col-auto">
                        <label class="form-label">To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') ?: now()->toDateString() }}">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary">Filter Chart</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="chart-wrap-lg">
            <canvas id="profitChart" height="120" 
                data-labels='{{ json_encode($labels ?? []) }}'
                data-values='{{ json_encode($profitSeries ?? []) }}'></canvas>
        </div>
        <p class="small text-muted mt-2">Showing profit (sales &minus; COGS) per day for the selected range (default last 30 days).</p>
    </div>

    <div class="mb-3">
        <form action="{{ route('admin.reports.export') }}" method="GET" style="display:inline">
            <input type="hidden" name="date_from" value="{{ request('date_from') ?: now()->subDays(29)->toDateString() }}">
            <input type="hidden" name="date_to" value="{{ request('date_to') ?: now()->toDateString() }}">
            <input type="hidden" name="type" value="sales">
            <button class="btn btn-outline-primary">Export Sales CSV</button>
        </form>

        @if(($canExport ?? false) || (auth()->check() && (auth()->user()->role ?? null) === 'admin'))
            <form action="{{ route('admin.reports.export') }}" method="GET" style="display:inline;margin-left:8px">
                <input type="hidden" name="date_from" value="{{ request('date_from') ?: now()->subDays(29)->toDateString() }}">
                <input type="hidden" name="date_to" value="{{ request('date_to') ?: now()->toDateString() }}">
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

@push('scripts')
<script>
    (function () {
        const canvas = document.getElementById('profitChart');
        if (!canvas) return;

        // parse initial data from data-* attributes
        const initialLabels = JSON.parse(canvas.getAttribute('data-labels') || '[]');
        const initialValues = JSON.parse(canvas.getAttribute('data-values') || '[]');

        const ctx = canvas.getContext('2d');
        const config = {
            type: 'line',
            data: {
                labels: initialLabels,
                datasets: [{
                    label: 'Profit (daily)',
                    data: initialValues,
                    fill: true,
                    backgroundColor: 'rgba(54,162,235,0.08)',
                    borderColor: 'rgba(54,162,235,1)',
                    tension: 0.2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                }]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (ctx) { return ctx.dataset.label + ': ' + ctx.formattedValue; } } }
                },
                scales: {
                    x: { display: true, title: { display: false } },
                    y: { display: true, title: { display: true, text: 'Amount' } }
                }
            }
        };

        const chart = new Chart(ctx, config);

        // update export hidden inputs when range changes
        function setExportInputs(from, to) {
            document.querySelectorAll('form[action="{{ route('admin.reports.export') }}"] input[name="date_from"]').forEach(i => i.value = from);
            document.querySelectorAll('form[action="{{ route('admin.reports.export') }}"] input[name="date_to"]').forEach(i => i.value = to);
        }

        const form = document.getElementById('chartFilterForm');
        if (!form) return;

        form.addEventListener('submit', function (ev) {
            ev.preventDefault();

            const fd = new FormData(form);
            const params = new URLSearchParams();
            if (fd.get('date_from')) params.append('date_from', fd.get('date_from'));
            if (fd.get('date_to')) params.append('date_to', fd.get('date_to'));

            // fetch time-series JSON from server
            fetch('{{ route('admin.reports.series') }}?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            }).then(r => r.json()).then(json => {
                const labels = json.labels || [];
                const values = json.values || [];

                chart.data.labels = labels;
                chart.data.datasets[0].data = values;
                chart.update();

                // update export inputs so CSVs reflect the currently selected range
                const from = fd.get('date_from') || labels[0] || '';
                const to = fd.get('date_to') || labels[labels.length - 1] || '';
                setExportInputs(from, to);
            }).catch(err => {
                console.error('Failed to fetch series:', err);
                alert('Could not load chart data. Please try again or reload the page.');
            });
        });
    })();
</script>
@endpush
