@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Create Purchase</h3>
                <div class="small-muted">Use this form to record supplier purchases and restocks</div>
            </div>

            <form method="POST" action="{{ route('manager.purchases.store') }}" class="form-section">
                @csrf

                <div class="mb-3 row align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" id="supplier_id" class="form-select">
                            <option value="">-- Select existing supplier --</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                            <option value="__new">-- Add new supplier --</option>
                        </select>
                    </div>
                    <div class="col-md-6" id="newSupplierFields" style="display:none;">
                        <label class="form-label">New supplier name</label>
                        <input name="supplier_new_name" class="form-control mb-2" />
                        <label class="form-label">Contact / notes</label>
                        <input name="supplier_new_contact" class="form-control" />
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Line items</label>
                    <div class="table-responsive">
                        <table class="table table-sm line-items-table">
                            <thead>
                                <tr>
                                    <th style="width:48%">Product</th>
                                    <th style="width:12%">Qty</th>
                                    <th style="width:18%">Cost</th>
                                    <th style="width:18%">Line total</th>
                                    <th style="width:4%"></th>
                                </tr>
                            </thead>
                            <tbody id="items">
                                <tr class="item-row">
                                    <td>
                                        <select name="items[0][product_id]" class="form-select product-select">
                                            <option value="">-- Select product --</option>
                                            @foreach($products as $p)
                                                <option value="{{ $p->id }}" data-cost="{{ $p->cost_price ?? '' }}" data-supplier="{{ $p->supplier_id ?? '' }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input name="items[0][qty]" class="form-control item-qty" placeholder="Qty" type="number" min="1" step="1" /></td>
                                    <td><input name="items[0][cost_price]" class="form-control item-cost" placeholder="Cost per unit" type="number" min="0" step="0.01" /></td>
                                    <td>
                                        <div class="input-group">
                                            <input name="items[0][line_total_visible]" class="form-control line-total-visible" placeholder="Line total" readonly />
                                            <input type="hidden" name="items[0][line_total]" class="line-total-hidden" />
                                        </div>
                                    </td>
                                    <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger btn-icon remove-item" title="Remove"><i class="fa fa-times"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <button type="button" id="addItem" class="btn btn-sm btn-secondary"><i class="fa fa-plus"></i> Add item</button>
                    </div>
                    <div class="text-end">
                        <div class="mb-1 small-muted">Grand Total</div>
                        <div class="h4" id="grandTotal">0.00</div>
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-primary">Save Purchase</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('supplier_id').addEventListener('change', function(e){
        const v = e.target.value;
        const ns = document.getElementById('newSupplierFields');
        if (v === '__new') {
            ns.style.display = '';
        } else {
            ns.style.display = 'none';
        }
    });

document.getElementById('addItem').addEventListener('click', function(){
    const tbody = document.getElementById('items');
    const idx = tbody.querySelectorAll('tr.item-row').length;
    const tr = document.createElement('tr');
    tr.className = 'item-row';
    tr.innerHTML = `
        <td>
            <select name="items[${idx}][product_id]" class="form-select product-select">
                <option value="">-- Select product --</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" data-cost="{{ $p->cost_price ?? '' }}" data-supplier="{{ $p->supplier_id ?? '' }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </td>
        <td><input name="items[${idx}][qty]" class="form-control item-qty" placeholder="Qty" type="number" min="1" step="1" /></td>
        <td><input name="items[${idx}][cost_price]" class="form-control item-cost" placeholder="Cost per unit" type="number" min="0" step="0.01" /></td>
        <td>
            <div class="input-group">
                <input name="items[${idx}][line_total_visible]" class="form-control line-total-visible" placeholder="Line total" readonly />
                <input type="hidden" name="items[${idx}][line_total]" class="line-total-hidden" />
            </div>
        </td>
        <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger btn-icon remove-item" title="Remove"><i class="fa fa-times"></i></button></td>
    `;
    tbody.appendChild(tr);
    attachProductListeners();
    attachCalcListeners();
    recalcGrandTotal();
});

// Helper: attach product-id listeners to all product id inputs
function attachProductListeners() {
    document.querySelectorAll('.product-select').forEach(function(select){
        if (select.dataset.listenerAttached) return;
        select.dataset.listenerAttached = '1';
        select.addEventListener('change', onProductSelectChange);
    });
}

function attachCalcListeners() {
    document.querySelectorAll('.item-qty').forEach(function(input){
        if (input.dataset.calcAttached) return;
        input.dataset.calcAttached = '1';
        input.addEventListener('input', onLineChange);
    });
    document.querySelectorAll('.item-cost').forEach(function(input){
        if (input.dataset.calcAttached) return;
        input.dataset.calcAttached = '1';
        input.addEventListener('input', onLineChange);
    });
}

// Remove item handler (delegated)
(function(){
    const tbody = document.getElementById('items');
    if (! tbody) return;
    tbody.addEventListener('click', function(e){
        const btn = e.target.closest && e.target.closest('.remove-item');
        if (! btn) return;
        e.preventDefault();
        const row = btn.closest('.item-row');
        if (row) {
            row.remove();
            reindexItems();
            recalcGrandTotal();
        }
    });
})();

function reindexItems(){
    const rows = document.querySelectorAll('#items tr.item-row');
    rows.forEach(function(row, i){
        const select = row.querySelector('.product-select');
        const qty = row.querySelector('.item-qty');
        const cost = row.querySelector('.item-cost');
        const visible = row.querySelector('.line-total-visible');
        const hidden = row.querySelector('.line-total-hidden');
        if (select) select.name = `items[${i}][product_id]`;
        if (qty) qty.name = `items[${i}][qty]`;
        if (cost) cost.name = `items[${i}][cost_price]`;
        if (visible) visible.name = `items[${i}][line_total_visible]`;
        if (hidden) hidden.name = `items[${i}][line_total]`;
    });
}

function onLineChange(e) {
    const row = e.target.closest('.item-row');
    if (! row) return;
    const qtyInput = row.querySelector('.item-qty');
    const costInput = row.querySelector('.item-cost');
    const visible = row.querySelector('.line-total-visible');
    const hidden = row.querySelector('.line-total-hidden');

    const qty = parseFloat(qtyInput && qtyInput.value) || 0;
    const cost = parseFloat(costInput && costInput.value) || 0;
    const total = qty * cost;
    if (visible) visible.value = total.toFixed(2);
    if (hidden) hidden.value = total.toFixed(2);
    recalcGrandTotal();
}

function recalcGrandTotal() {
    let sum = 0;
    document.querySelectorAll('.line-total-hidden').forEach(function(h){
        const v = parseFloat(h.value) || 0;
        sum += v;
    });
    const el = document.getElementById('grandTotal');
    if (el) el.textContent = sum.toFixed(2);
}

function onProductSelectChange(e) {
    const select = e.target;
    const val = select.value;
    if (! val) return;
    const opt = select.options[select.selectedIndex];
    const cost = opt ? opt.dataset.cost : null;
    const supplierId = opt ? opt.dataset.supplier : null;

    const row = select.closest('.item-row');
    if (row) {
        const costInput = row.querySelector('.item-cost');
        if (costInput && (!costInput.value || costInput.value === '')) {
            if (cost) costInput.value = cost;
        }

        if (supplierId) {
            const supplierSelect = document.getElementById('supplier_id');
            if (supplierSelect && (!supplierSelect.value || supplierSelect.value === '')) {
                const optSup = supplierSelect.querySelector('option[value="' + supplierId + '"]');
                if (optSup) supplierSelect.value = supplierId;
            }
        }
    }
}

// attach for initial row
attachProductListeners();
attachCalcListeners();
recalcGrandTotal();
</script>

@endsection
