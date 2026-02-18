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
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card card-modern">
                <div class="text-muted small">Today's Sales</div>
                <div class="display-6">{{ format_currency($salesTotal ?? 0) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-modern">
                <div class="text-muted small">Transactions</div>
                <div class="display-6">{{ $salesCount ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-modern">
                <div class="text-muted small">Low Stock</div>
                <div class="display-6">{{ count($lowStock ?? []) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-modern">
                <div class="text-muted small">Quick Actions</div>
                <div><a href="{{ route('cashier.sales.index') }}" class="btn btn-sm btn-outline-secondary">Sales History</a></div>
            </div>
        </div>
    </div>
    <div class="row">
            <div class="col-12 mb-2">
                <div class="input-group mb-2">
                    <input id="barcode" class="form-control" placeholder="Scan barcode" autofocus>
                    <button id="scanBtn" class="btn btn-primary"><i class="fa-solid fa-barcode"></i> Scan</button>
                </div>

                <div class="input-group">
                    <input id="productSearch" class="form-control" placeholder="Type product name, sku or barcode to search">
                    <button id="productSearchBtn" class="btn btn-outline-secondary">Search</button>
                </div>
                <div id="productSuggestions" class="list-group position-relative" style="z-index:1050; display:none; max-height:240px; overflow:auto;">
                </div>
            </div>

                    <div class="col-12 mb-2">
                        <div class="card card-modern">
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
                                            <option value="{{ $c->id }}">{{ $c->name }} @if(!empty($c->phone)) ({{ $c->phone }}) @endif @if(isset($c->credit_limit)) — Limit: {{ number_format($c->credit_limit,2) }}@endif</option>
                                        @endforeach
                                        </select>
                                        <button id="addCustomerBtn" class="btn btn-sm btn-outline-primary ms-2" title="Add customer">+</button>
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

                                    <!-- Quantity modal (edit line quantity / discount) -->
                                    <div class="modal fade" id="qtyModal" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Quantity</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-2">
                                                        <label class="form-label">Quantity</label>
                                                        <input id="qtyModalQuantity" type="number" class="form-control" min="1" value="1">
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Per-line discount (optional)</label>
                                                        <input id="qtyModalDiscount" type="number" class="form-control" step="0.01" min="0" value="0">
                                                    </div>
                                                    <div class="text-muted small">Enter quantity and optional per-line discount, then confirm.</div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="button" id="qtyModalConfirm" class="btn btn-primary">Confirm</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Add Customer modal -->
                                    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Add Customer</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-2"><label class="form-label">Name</label><input id="newCustomerName" class="form-control"/></div>
                                                    <div class="mb-2"><label class="form-label">Phone</label><input id="newCustomerPhone" class="form-control"/></div>
                                                    <div class="mb-2"><label class="form-label">Email</label><input id="newCustomerEmail" class="form-control"/></div>
                                                    <div class="mb-2"><label class="form-label">Phone</label><input id="newCustomerPhone" class="form-control"/></div>
                                                    <div class="mb-2"><label class="form-label">Address</label><textarea id="newCustomerAddress" class="form-control" rows="2"></textarea></div>
                                                    <div class="mb-2"><label class="form-label">Credit limit</label><input id="newCustomerCreditLimit" class="form-control" type="number" step="0.01" value="0"/></div>
                                                    <div class="mb-2"><label class="form-label">Notes</label><textarea id="newCustomerNotes" class="form-control" rows="2"></textarea></div>
                                                    <div id="addCustomerError" class="text-danger small" style="display:none"></div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="button" id="createCustomerBtn" class="btn btn-primary">Create</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

        <div class="col-8">
            <div id="searchResults" class="row g-2"></div>
        </div>
        <div class="col-4">
            <div id="cartItems" class="card card-modern" style="min-height:120px">
                <h6>Cart</h6>
                <div id="cartList">No items</div>
                <div class="cart-actions d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <input id="cartDiscount" type="number" min="0" step="0.01" class="form-control form-control-sm" style="width:140px" placeholder="Discount">
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button id="splitBtn" class="btn btn-sm btn-secondary">Split</button>
                        <button id="checkoutBtn" class="btn btn-sm btn-success">Checkout</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cart summary sticky bottom for mobile -->
<div class="cart-summary bg-white border-top p-2">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <strong id="cartCount">0</strong> items
            <div class="text-muted small">Total: <span id="cartTotal">0.00</span></div>
        </div>
        <div class="d-none d-md-flex align-items-center gap-2">
            <!-- On larger screens the main controls live in the cart card; this sticky bar is minimal on desktop -->
        </div>
    </div>
</div>

<!-- Split payment modal -->
<div class="modal fade" id="splitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Split Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">Total: <strong id="splitTotal">0.00</strong></div>
                <div class="mb-2"><label>Cash amount</label><input id="splitCash" type="number" min="0" step="0.01" class="form-control" value="0"></div>
                <div class="mb-2"><label>Mobile money amount</label><input id="splitMobile" type="number" min="0" step="0.01" class="form-control" value="0"></div>
                <div class="mb-2"><label>Credit amount</label><input id="splitCredit" type="number" min="0" step="0.01" class="form-control" value="0"></div>
                <div class="text-muted small">Ensure amounts add up to total before confirming.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmSplit" class="btn btn-primary">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/pos.js') }}"></script>
</body>
</html>
