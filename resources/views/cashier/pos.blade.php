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
                                            <option value="{{ $c->id }}">{{ $c->name }} @if(!empty($c->phone)) ({{ $c->phone }}) @endif</option>
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
                <div class="mt-3 d-flex justify-content-between align-items-center">
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
                sel.appendChild(opt);
                sel.value = data.customer.id;
                addCustomerModal.hide();
                // trigger change to fetch balances
                sel.dispatchEvent(new Event('change'));
            } else {
                err.style.display = 'block'; err.innerText = data.error || 'Unable to create customer';
            }
        } catch(e) { err.style.display = 'block'; err.innerText = 'Request failed'; }
    });

    document.getElementById('scanBtn').addEventListener('click', async function(){
        const barcode = document.getElementById('barcode').value;
        if (!barcode) return alert('Enter or scan a barcode');

        const res = await fetch('/cashier/scan', {method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrfToken}, body: JSON.stringify({barcode})});
        const data = await res.json();
        if (data.found) {
            renderProductCard(data.product, data.available ?? 0);
        } else {
            alert('Product not found');
        }
    });

    // allow barcode scanners (which send an Enter) to trigger scan
    document.getElementById('barcode').addEventListener('keydown', function(e){
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('scanBtn').click();
        }
    });
    // credit toggle still exists but customer selector is always visible now
    const creditToggle = document.getElementById('creditToggle');
    const customerWrap = document.getElementById('customerSelectWrap');
    creditToggle.addEventListener('change', function(){
        // no longer hide the customer selector; selection is optional for cash/mobile payments
    });

    // split payment modal wiring
    const splitBtn = document.getElementById('splitBtn');
    const splitModalEl = document.getElementById('splitModal');
    const splitTotalEl = document.getElementById('splitTotal');
    const splitCash = document.getElementById('splitCash');
    const splitMobile = document.getElementById('splitMobile');
    const splitCredit = document.getElementById('splitCredit');
    const confirmSplit = document.getElementById('confirmSplit');
    const splitModal = new bootstrap.Modal(splitModalEl);
    splitBtn.addEventListener('click', function(){
        const rawTotal = cart.reduce((s,i)=>s + (i.price * i.qty),0);
        const perItemDiscountTotal = cart.reduce((s,i)=>s + (parseFloat(i.discount || 0) || 0),0);
        const overallDiscount = parseFloat(document.getElementById('cartDiscount')?.value || 0) || 0;
        const totalAfter = Math.max(0, rawTotal - perItemDiscountTotal - overallDiscount);
        // store numeric total on modal element and show formatted text
        splitModalEl.dataset.total = totalAfter;
        splitTotalEl.innerText = formatCurrencyJS(totalAfter);
        splitCash.value = totalAfter.toFixed(2);
        splitMobile.value = '0.00';
        splitCredit.value = '0.00';
        splitModal.show();
    });

    confirmSplit.addEventListener('click', function(){
        const total = parseFloat(splitModalEl.dataset.total || 0);
        const cash = parseFloat(splitCash.value || 0) || 0;
        const mobile = parseFloat(splitMobile.value || 0) || 0;
        const credit = parseFloat(splitCredit.value || 0) || 0;
        const sum = +(cash + mobile + credit).toFixed(2);
        if (Math.abs(sum - total) > 0.01) {
            return alert('Split amounts must add up to total');
        }
        // store parts temporarily on window so checkout reads them
        window.__payment_parts = [];
        if (cash > 0) window.__payment_parts.push({method: 'cash', amount: cash});
        if (mobile > 0) window.__payment_parts.push({method: 'mobile_money', amount: mobile});
        if (credit > 0) window.__payment_parts.push({method: 'credit', amount: credit});
        splitModal.hide();
        alert('Split payment configured');
    });

    document.getElementById('checkoutBtn').addEventListener('click', async function(){
        if (cart.length === 0) return alert('Cart is empty');
        const discount = parseFloat(document.getElementById('cartDiscount')?.value || 0) || 0;
        let payment = document.getElementById('paymentMethod').value || 'cash';
        let customerId = null;
        if (creditToggle.checked) {
            payment = 'credit';
            customerId = document.getElementById('customerSelect').value || null;
            if (! customerId) return alert('Please select a customer for credit sale');
        } else {
            // for cash/mobile payments, allow selecting or adding a customer optionally
            customerId = document.getElementById('customerSelect').value || null;
        }

        const payload = {cart, payment, discount};
        if (customerId) payload.customer_id = parseInt(customerId, 10);

        // include split payment parts if configured
        if (window.__payment_parts && Array.isArray(window.__payment_parts) && window.__payment_parts.length) {
            payload.payment_parts = window.__payment_parts;
        }

        const res = await fetch('/cashier/checkout', {method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrfToken}, body: JSON.stringify(payload)});
        const data = await res.json();
        if (data.success) {
            // fetch receipt HTML and show in a modal (pop-out) for printing instead of opening a new tab
            if (data.sale_id) {
                try {
                    const r = await fetch('/cashier/sales/' + data.sale_id, {headers: {'X-Requested-With':'XMLHttpRequest'}});
                    const html = await r.text();
                    showReceiptModal(html);
                } catch(e) {
                    // fallback to opening a new tab
                    try { window.open('/cashier/sales/' + data.sale_id, '_blank'); } catch(_) {}
                }
            }

            alert('Sale complete, id: ' + data.sale_id);
            cart = [];
            document.getElementById('cartDiscount').value = '';
            renderCart();
            document.getElementById('searchResults').innerHTML = '';
            document.getElementById('barcode').value = '';

            // if sale was credit, optionally refresh customer credit info (if a UI exists)
            if (payment === 'credit' && customerId) {
                // try to update any credit widgets via a standard endpoint
                fetch('/cashier/credits/' + customerId).then(r=>r.json()).then(d => {
                    // update any customer info widgets
                    try {
                        if (d.balance !== undefined) document.getElementById('customerBalance').innerText = 'Balance: ' + parseFloat(d.balance||0).toFixed(2);
                        if (d.credit_limit !== undefined) document.getElementById('customerLimit').innerText = 'Credit limit: ' + (d.credit_limit===null? 'n/a' : parseFloat(d.credit_limit).toFixed(2));
                        if (Array.isArray(d.credits)) document.getElementById('customerCredits').innerText = 'Open credits: ' + d.credits.length;
                    } catch(e) {}
                }).catch(()=>{});
            }
        } else {
            alert('Checkout failed: ' + (data.error || data.message || JSON.stringify(data)));
        }
    });

    // When customer is selected, fetch and display balance/limits
    document.getElementById('customerSelect').addEventListener('change', function(){
        const id = this.value;
        const info = document.getElementById('customerInfo');
        if (!id) { info.style.display='none'; return; }
        fetch('/cashier/credits/' + id).then(r=>r.json()).then(d => {
            info.style.display = 'block';
            document.getElementById('customerBalance').innerText = 'Balance: ' + (d.balance ? parseFloat(d.balance).toFixed(2) : '0.00');
            document.getElementById('customerLimit').innerText = 'Credit limit: ' + (d.credit_limit===null? 'n/a' : parseFloat(d.credit_limit).toFixed(2));
            document.getElementById('customerCredits').innerText = 'Open credits: ' + (Array.isArray(d.credits)? d.credits.length : 0);
        }).catch(()=>{ info.style.display='none'; });
    });

        // Receipt modal container + helper to show and print
        const receiptModalHtml = `
        <div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Receipt</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="receiptModalBody"></div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button id="printReceiptBtn" class="btn btn-primary">Print</button>
                    </div>
                </div>
            </div>
        </div>`;

        // append once
        if (!document.getElementById('receiptModal')) {
                const div = document.createElement('div'); div.innerHTML = receiptModalHtml; document.body.appendChild(div);
        }

        const receiptModalEl = document.getElementById('receiptModal');
        const bsReceiptModal = new bootstrap.Modal(receiptModalEl);

        function showReceiptModal(html) {
                document.getElementById('receiptModalBody').innerHTML = html;
                bsReceiptModal.show();
                // wire print button to print only the receipt content
                document.getElementById('printReceiptBtn').onclick = function(){
                        const content = document.getElementById('receiptModalBody').innerHTML;
                        const w = window.open('', '_blank');
                        w.document.open();
                        w.document.write('<html><head><title>Receipt</title>');
                        w.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
                        w.document.write('</head><body>');
                        w.document.write(content);
                        w.document.write('</body></html>');
                        w.document.close();
                        w.focus();
                        setTimeout(()=>{ try { w.print(); } catch(e){} }, 300);
                };
        }
    </script>
</body>
</html>
