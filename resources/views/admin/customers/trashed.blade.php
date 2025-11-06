@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Deleted Customers</h3>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">Back to Customers</a>
    </div>
    <form method="POST" action="{{ route('admin.customers.restore_bulk') }}">
        @csrf
        <div class="mb-3">
            <button type="submit" class="btn btn-success" onclick="return confirm('Restore selected customers?')">Restore Selected</button>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary ms-2">Back to Customers</a>
        </div>

        <div class="list-group">
            <div class="list-group-item">
                <input type="checkbox" id="select_all_customers"> <label for="select_all_customers">Select all</label>
            </div>
            @foreach($customers as $c)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <input type="checkbox" name="ids[]" value="{{ $c->id }}" class="select-customer me-2">
                        <strong>{{ $c->name }}</strong>
                        <div class="small text-muted">{{ $c->email }} {{ $c->phone ? '• '.$c->phone : '' }}</div>
                    </div>
                    <div>
                        <form action="{{ route('admin.customers.restore', $c->id) }}" method="POST" style="display:inline-block">
                            @csrf
                            <button class="btn btn-sm btn-success">Restore</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3">{{ $customers->links() }}</div>
    </form>

    <script>
        document.getElementById('select_all_customers').addEventListener('change', function(e){
            document.querySelectorAll('.select-customer').forEach(cb => cb.checked = e.target.checked);
        });
    </script>
</div>
@endsection
