@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>Reports</h1>

    <div class="card p-3 mb-3">
        <form id="chartFilterForm" class="js-export-form" method="GET" action="{{ route('admin.reports.export') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">From</label>
                    <input type="date" name="date_from" class="form-control"
                        value="{{ request('date_from') ?: now()->subDays(29)->toDateString() }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">To</label>
                    <input type="date" name="date_to" class="form-control"
                        value="{{ request('date_to') ?: now()->toDateString() }}">
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Apply</button>
                    <button type="submit" name="type" value="profit_loss" class="btn btn-outline-secondary">
                        Export Profit/Loss CSV
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="card mb-3 p-3">
        <h5 class="mb-3">Profit / Loss (trend)</h5>

        <div class="chart-wrap-lg">
            <canvas
                id="profitChart"
                height="120"
                data-labels='{{ json_encode($profitLabels ?? []) }}'
                data-values='{{ json_encode($profitSeries ?? []) }}'
            ></canvas>
        </div>

        <p class="small text-muted mt-2">
            Showing profit (sales &minus; COGS) per day for the selected range.
        </p>
    </div>

    <div class="mb-3">

        <form action="{{ route('admin.reports.export') }}" method="GET"
              class="js-export-form" style="display:inline">

            <input type="hidden" name="date_from"
                value="{{ request('date_from') ?: now()->subDays(29)->toDateString() }}">

            <input type="hidden" name="date_to"
                value="{{ request('date_to') ?: now()->toDateString() }}">

            <input type="hidden" name="type" value="sales">

            <button class="btn btn-outline-primary">Export Sales CSV</button>
        </form>

        @if(($canExport ?? false) || (auth()->check() && (auth()->user()->role ?? null) === 'admin'))

            <form action="{{ route('admin.reports.export') }}" method="GET"
                  class="js-export-form"
                  style="display:inline;margin-left:8px">

                <input type="hidden" name="date_from"
                    value="{{ request('date_from') ?: now()->subDays(29)->toDateString() }}">

                <input type="hidden" name="date_to"
                    value="{{ request('date_to') ?: now()->toDateString() }}">

                <input type="hidden" name="type" value="purchases">

                <button class="btn btn-outline-secondary">Export Purchases CSV</button>
            </form>

        @else
            <button class="btn btn-outline-secondary" disabled style="margin-left:8px">
                Export Purchases CSV (admins only)
            </button>
        @endif

    </div>

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
const SERIES_URL = "{{ route('admin.reports.series') }}";

(function () {
    const canvas = document.getElementById('profitChart');
    if (!canvas || typeof Chart === 'undefined') return;

    const seriesUrl = SERIES_URL;

    let labels = [];
    let values = [];

    try {
        labels = JSON.parse(canvas.dataset.labels || '[]');
        values = JSON.parse(canvas.dataset.values || '[]');
    } catch (e) {
        console.error('Invalid chart data:', e);
    }

    const chart = new Chart(canvas.getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Profit (daily)',
                data: values,
                fill: true,
                backgroundColor: 'rgba(54,162,235,0.08)',
                borderColor: 'rgba(54,162,235,1)',
                tension: 0.2,
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { display: false } }
        }
    });

    const form = document.getElementById('chartFilterForm');
    if (!form) return;

    form.addEventListener('submit', async function (ev) {
        if (ev.submitter && ev.submitter.value === 'profit_loss') return;
        ev.preventDefault();

        const fd = new FormData(form);
        const params = new URLSearchParams(fd);

        try {
            const res = await fetch(seriesUrl + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) throw new Error('HTTP ' + res.status);
            const json = await res.json();

            chart.data.labels = json.labels || [];
            chart.data.datasets[0].data = json.values || [];
            chart.update();

            document.querySelectorAll('.js-export-form input[name="date_from"]')
                .forEach(function(i) { i.value = fd.get('date_from') || ''; });

            document.querySelectorAll('.js-export-form input[name="date_to"]')
                .forEach(function(i) { i.value = fd.get('date_to') || ''; });
        } catch (err) {
            console.error(err);
            alert('Could not load chart data.');
        }
    });
})();
</script>
@endpush
