@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Deleted Suppliers</h3>
        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">Back to Suppliers</a>
    </div>
    <form method="POST" action="{{ route('admin.suppliers.restore_bulk') }}">
        @csrf
        <div class="mb-3">
            <button type="submit" class="btn btn-success" onclick="return confirm('Restore selected suppliers?')">Restore Selected</button>
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary ms-2">Back to Suppliers</a>
        </div>

        <div class="list-group">
            <div class="list-group-item">
                <input type="checkbox" id="select_all_suppliers"> <label for="select_all_suppliers">Select all</label>
            </div>
            @foreach($suppliers as $s)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <input type="checkbox" name="ids[]" value="{{ $s->id }}" class="select-supplier me-2">
                        <strong>{{ $s->name }}</strong>
                        <div class="small text-muted">{{ $s->email }} {{ $s->phone ? '• '.$s->phone : '' }}</div>
                    </div>
                    <div>
                        <form action="{{ route('admin.suppliers.restore', $s->id) }}" method="POST" style="display:inline-block">
                            @csrf
                            <button class="btn btn-sm btn-success">Restore</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3">{{ $suppliers->links() }}</div>
    </form>

    <script>
        document.getElementById('select_all_suppliers').addEventListener('change', function(e){
            document.querySelectorAll('.select-supplier').forEach(cb => cb.checked = e.target.checked);
        });
    </script>
</div>
@endsection
