@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="alert alert-warning">Managers are not permitted to create sales. Please use the cashier POS for checkout.</div>
    <a href="{{ route('manager.sales.index') }}" class="btn btn-sm btn-outline-primary">Back to Sales</a>
</div>

@endsection

@endsection
