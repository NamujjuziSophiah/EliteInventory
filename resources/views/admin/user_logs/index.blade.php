@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>User logs</h3>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <div class="card p-3 mb-3">
        <form action="{{ route('admin.user_logs.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input name="title" class="form-control" placeholder="Short title for this log (optional)">
            </div>
            <div class="mb-3">
                <label class="form-label">Log file</label>
                <input name="logfile" type="file" class="form-control" required>
                <div class="small text-muted">Max 10MB.</div>
            </div>
            <button class="btn btn-primary">Upload</button>
        </form>
    </div>

    <div class="card p-3">
        <h6>Recent uploads</h6>
        <ul class="list-unstyled mt-2">
            @forelse($logs as $l)
                <li class="py-2 d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $l->title }}</strong>
                        <div class="small text-muted">Uploaded by: {{ optional($l->user)->name ?? 'Unknown' }} — {{ $l->created_at->diffForHumans() }}</div>
                    </div>
                    <div>
                        <a href="{{ route('admin.user_logs.download', $l->id) }}" class="btn btn-sm btn-outline-primary">Download</a>
                    </div>
                </li>
            @empty
                <li class="text-muted">No uploads yet</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
