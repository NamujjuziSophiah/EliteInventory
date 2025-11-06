@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1>Purchases</h1>
    <table class="table table-sm">
        <thead><tr><th>ID</th><th>Total</th><th>Date</th></tr></thead>
        <tbody>
        @foreach($purchases as $p)
            <tr>
                <td>{{ $p->id }}</td>
                <td>{{ $p->total }}</td>
                <td>{{ $p->created_at }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@endsection
