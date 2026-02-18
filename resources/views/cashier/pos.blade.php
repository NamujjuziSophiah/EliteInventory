<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Cashier POS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="{{ asset('css/pos.css') }}" rel="stylesheet">
<script src="/js/formatters.js"></script>

<style>

/* SAFE CART IMPROVEMENT ONLY */
#cartItems{
    border-radius:14px;
}

#cartList{
    max-height:380px;
    overflow:auto;
    padding:15px;
    background:#fafafa;
    border-radius:10px;
    margin-bottom:12px;
}

#cartList .cart-row{
    background:white;
    padding:12px;
    border-radius:10px;
    margin-bottom:10px;
    box-shadow:0 2px 4px rgba(0,0,0,0.05);
}

.cart-actions{
    padding:10px 15px 15px 15px;
    border-top:1px solid #eee;
}

#checkoutBtn{
    padding:10px 18px;
    font-weight:600;
}

</style>
</head>


<body class="bg-light">

<nav class="navbar navbar-light bg-white border-bottom">
<div class="container-fluid">
<a class="navbar-brand" href="#">POS</a>

<div class="d-flex ms-3">
<a href="{{ route('cashier.dashboard') }}" class="btn btn-sm btn-outline-primary me-2">Dashboard</a>

<form id="pos-logout-form" action="{{ route('logout') }}" method="POST" class="d-inline">
@csrf
<button type="submit" class="btn btn-sm btn-outline-danger me-2">Logout</button>
</form>
</div>

<div class="d-flex align-items-center">
<span class="me-3"><i class="fa-solid fa-cash-register"></i> Cashier</span>
</div>
</div>
</nav>


<div class="container py-3">

<!-- DASHBOARD CARDS -->
<div class="row mb-3">

<div class="col-md-3">
<div class="card card-modern p-3">
<div class="text-muted small">Today's Sales</div>
<div class="display-6">{{ format_currency($salesTotal ?? 0) }}</div>
</div>
</div>

<div class="col-md-3">
<div class="card card-modern p-3">
<div class="text-muted small">Transactions</div>
<div class="display-6">{{ $salesCount ?? 0 }}</div>
</div>
</div>

<div class="col-md-3">
<div class="card card-modern p-3">
<div class="text-muted small">Low Stock</div>
<div class="display-6">{{ count($lowStock ?? []) }}</div>
</div>
</div>

<div class="col-md-3">
<div class="card card-modern p-3">
<div class="text-muted small">Quick Actions</div>
<div>
<a href="{{ route('cashier.sales.index') }}" class="btn btn-sm btn-outline-secondary">Sales History</a>
</div>
</div>
</div>

</div>



<div class="row">

<!-- SEARCH -->
<div class="col-12 mb-2">

<div class="input-group mb-2">
<input id="barcode" class="form-control" placeholder="Scan barcode" autofocus>
<button id="scanBtn" class="btn btn-primary"><i class="fa-solid fa-barcode"></i> Scan</button>
</div>

<div class="input-group">
<input id="productSearch" class="form-control" placeholder="Type product name, sku or barcode to search">
<button id="productSearchBtn" class="btn btn-outline-secondary">Search</button>
</div>

<div id="productSuggestions" class="list-group position-relative" style="z-index:1050; display:none; max-height:240px; overflow:auto;"></div>

</div>



<!-- PAYMENT -->
<div class="col-12 mb-2">

<div class="card card-modern p-3">

<div class="row g-2 align-items-center">

<div class="col-auto">Payment:</div>

<div class="col-auto">
<select id="paymentMethod" class="form-select">
<option value="cash">Cash</option>
<option value="mobile_money">Mobile Money</option>
</select>
</div>

<div class="col-auto">
<div class="form-check">
<input class="form-check-input" type="checkbox" value="1" id="creditToggle">
<label class="form-check-label" for="creditToggle">Sell on credit</label>
</div>
</div>

<div class="col-auto" id="customerSelectWrap">

<div class="d-flex">
<select id="customerSelect" class="form-select">
<option value="">-- Select customer --</option>
@foreach(collect($customers ?? []) as $c)
<option value="{{ $c->id }}">{{ $c->name }}</option>
@endforeach
</select>

<button id="addCustomerBtn" class="btn btn-sm btn-outline-primary ms-2">+</button>
</div>

<div id="customerInfo" class="small text-muted mt-2" style="display:none">
<div id="customerBalance">Balance: -</div>
<div id="customerLimit">Credit limit: -</div>
<div id="customerCredits">Open credits: -</div>
</div>

</div>

</div>
</div>
</div>



<!-- PRODUCTS -->
<div class="col-8">
<div id="searchResults" class="row g-2"></div>
</div>



<!-- CART -->
<div class="col-4">

<div id="cartItems" class="card card-modern shadow-sm">

<div class="card-header bg-white fw-semibold">
<i class="fa-solid fa-cart-shopping me-2"></i>Cart
</div>

<div id="cartList">
No items
</div>

<div class="cart-actions d-flex justify-content-between align-items-center">

<input id="cartDiscount" type="number" min="0" step="0.01"
class="form-control form-control-sm"
style="width:140px"
placeholder="Discount">

<div class="d-flex gap-2">
<button id="splitBtn" class="btn btn-sm btn-secondary">Split</button>
<button id="checkoutBtn" class="btn btn-success">Checkout</button>
</div>

</div>

</div>

</div>

</div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/pos.js') }}"></script>

</body>
</html>
