@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1>Sales Report</h1>

    <div class="alert alert-info">
        Sales reports and exports are restricted to Admin and Cashier roles only.
    </div>

    <p class="text-muted">If you believe you should have access to reports, please contact your administrator.</p>
</div>

@endsection
