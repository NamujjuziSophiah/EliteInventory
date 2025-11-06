@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>System Settings</h1>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">System Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $settings->name ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Currency</label>
            <input type="text" name="currency" class="form-control" value="{{ old('currency', $settings->currency ?? '') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Logo</label>
            <input type="file" name="logo" class="form-control">
            @if(!empty($settings->logo))
                <div class="mt-2"><img src="{{ Storage::url($settings->logo) }}" alt="logo" style="max-height:80px"></div>
            @endif
        </div>

        <div class="mb-3 form-check">
            <input type="hidden" name="auto_redirect" value="0">
            <input type="checkbox" name="auto_redirect" value="1" class="form-check-input" id="auto_redirect" {{ old('auto_redirect', $settings->auto_redirect ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="auto_redirect">Enable auto-redirect authenticated users (dev toggle)</label>
        </div>

        <div class="mb-3">
            <label class="form-label">Default markup percent (%)</label>
            <input type="number" step="0.01" min="0" name="default_markup_percent" class="form-control" value="{{ old('default_markup_percent', $settings->default_markup_percent ?? 20) }}">
            <div class="small-muted">This percentage will be used to calculate selling price from cost price when creating products if no per-product markup is set.</div>
        </div>

        <hr>
        <h5>SKU Settings</h5>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">SKU Prefix</label>
                <input type="text" name="sku_prefix" class="form-control" value="{{ old('sku_prefix', $settings->sku_prefix ?? 'PR') }}">
                <div class="small-muted">Short prefix for generated SKUs (e.g. PR)</div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">SKU Padding</label>
                <input type="number" name="sku_padding" class="form-control" min="1" value="{{ old('sku_padding', $settings->sku_padding ?? 6) }}">
                <div class="small-muted">Number of digits to pad the numeric sequence to (e.g. 6 → PR000001)</div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Next SKU Sequence</label>
                <input type="number" name="sku_next" class="form-control" min="1" value="{{ old('sku_next', $settings->sku_next ?? 1) }}">
                <div class="small-muted">Next numeric value to use when auto-generating SKUs</div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary">Save</button>

            <form action="{{ route('admin.settings.force_logout') }}" method="POST" onsubmit="return confirm('Force logout all users? This will invalidate current sessions and remember-me tokens.');">
                @csrf
                <button type="submit" class="btn btn-outline-danger">Force Logout All Users</button>
            </form>
        </div>
    </form>

    <div class="mt-3">
        <div class="alert alert-secondary small">
            Footer copyright is managed by the system and cannot be changed via this settings page.
        </div>
    </div>
</div>
@endsection
