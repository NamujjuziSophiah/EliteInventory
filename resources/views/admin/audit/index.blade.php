@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 fw-bold">📝 Audit Logs</h3>
            <p class="text-muted mb-0">Recent system audit events</p>
        </div>
        <a href="{{ route('logs.export') }}" class="btn btn-outline-secondary">
            <i class="bi bi-download"></i> Export Logs
        </a>
    </div>

    <form method="GET" class="row g-3 align-items-end mb-4">
        <div class="col-md-3">
            <label class="form-label">Action</label>
            <select name="action" class="form-select">
                <option value="">All actions</option>
                @foreach($actions as $a)
                    <option value="{{ $a }}" {{ request('action') == $a ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">User</label>
            <select name="user_id" class="form-select">
                <option value="">All users</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">From</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">To</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">
                <i class="bi bi-funnel-fill"></i> Filter
            </button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>When</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>IP</th>
                        <th>User Agent</th>
                        <th>Meta</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td>{{ optional($log->user)->name ?? 'System' }}</td>
                            <td><span class="badge bg-info text-dark">{{ $log->action }}</span></td>
                            <td>{{ $log->ip }}</td>
                            <td class="text-truncate" style="max-width: 200px;">{{ $log->user_agent }}</td>
                            <td class="text-truncate" style="max-width: 200px;">{{ json_encode($log->meta) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No audit events found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $logs->links() }}
    </div>
</div>
@endsection