/* POS client-side logic extracted from the POS Blade view.
   Responsibilities:
   - product search, suggestions, add to cart
   - cart rendering and updates
   - add customer modal handling
   - split payment and checkout
   - receipt modal show/print

   This script assumes the page includes:
   - meta[name="csrf-token"]
   - formatCurrencyJS() from /js/formatters.js
   - Bootstrap 5 bundle loaded before this script
*/
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn, { once: true });
        else fn();
    }

    ready(function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        window.__payment_parts = window.__payment_parts || [];
        let cart = [];

        // Expose some helpers for debugging
        window.__pos_cart = cart;

        function formatCurrencySafe(v) { return (typeof formatCurrencyJS === 'function') ? formatCurrencyJS(v) : (Number(v || 0).toFixed(2)); }

        function renderCart() {
            const rawTotal = cart.reduce((s, i) => s + (i.price * i.qty), 0);
            const overallDiscount = parseFloat(document.getElementById('cartDiscount')?.value || 0) || 0;
            const totalAfter = Math.max(0, rawTotal - overallDiscount);

            const cartList = document.getElementById('cartList');
            if (!cartList) return;
            if (cart.length === 0) {
                cartList.innerHTML = '<div class="text-muted">No items</div>';
            } else {
                let html = '<table class="table table-sm mb-0"><thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Line</th><th></th></tr></thead><tbody>';
                cart.forEach((it, idx) => {
                    const line = Math.max(0, (it.price * it.qty));
                    html += `<tr><td>${it.name}</td><td><div class="btn-group btn-group-sm" role="group"><button class="btn btn-outline-secondary" data-idx="${idx}" data-delta="-1">-</button><span class="btn btn-light">${it.qty}</span><button class="btn btn-outline-secondary" data-idx="${idx}" data-delta="1">+</button></div></td><td>${formatCurrencySafe(it.price)}</td><td>${formatCurrencySafe(line)}</td><td><button class="btn btn-sm btn-danger" data-remove-idx="${idx}">x</button></td></tr>`;
                });
                html += '</tbody></table>';
                cartList.innerHTML = html;

                // wire dynamic controls (delegated)
                cartList.querySelectorAll('[data-idx][data-delta]').forEach(btn => btn.addEventListener('click', function () { changeQty(parseInt(this.dataset.idx, 10), parseInt(this.dataset.delta, 10)); }));
                cartList.querySelectorAll('[data-remove-idx]').forEach(btn => btn.addEventListener('click', function () { removeItem(parseInt(this.dataset.removeIdx, 10)); }));
            }

            const countEl = document.getElementById('cartCount'); if (countEl) countEl.innerText = cart.reduce((s, i) => s + i.qty, 0);
            const totalEl = document.getElementById('cartTotal'); if (totalEl) totalEl.innerText = formatCurrencySafe(cart.reduce((s, i) => s + (i.price * i.qty), 0) - (parseFloat(document.getElementById('cartDiscount')?.value || 0) || 0));
        }

        function changeQty(index, delta) {
            const it = cart[index]; if (!it) return;
            it.qty = Math.max(0, (it.qty || 0) + delta);
            if (it.qty === 0) cart.splice(index, 1);
            renderCart();
        }
        function removeItem(index) { cart.splice(index, 1); renderCart(); }

        // render product(s)
        function renderProductCard(product, available) {
            const container = document.getElementById('searchResults'); if (!container) return;
            container.innerHTML = '';
            const col = document.createElement('div'); col.className = 'col-12';
            const card = document.createElement('div'); card.className = 'card p-2';
            const title = document.createElement('div'); title.innerHTML = `<strong>${product.name ?? product.title ?? 'Unnamed'}</strong>`;
            const meta = document.createElement('div'); meta.className = 'text-muted small'; meta.innerText = `Price: ${product.selling_price ?? product.price ?? 0} • Available: ${available}`;
            const btn = document.createElement('button'); btn.className = 'btn btn-sm btn-primary mt-2'; btn.innerText = 'Add to cart'; btn.addEventListener('click', () => addToCart(product, available, 1));
            card.appendChild(title); card.appendChild(meta); card.appendChild(btn); col.appendChild(card); container.appendChild(col);
        }

        async function addToCart(product, available, qty = 1) {
            let existing = cart.find(c => c.product_id == product.id);
            if (existing) {
                if (existing.qty + qty > available) return alert('Insufficient stock');
                existing.qty += qty;
            } else {
                if (available < qty) return alert('Insufficient stock');
                cart.push({ product_id: product.id, name: product.name ?? product.title, price: parseFloat(product.selling_price ?? product.price ?? 0), qty: qty });
            }
            renderCart();
        }

        function renderSearchResults(products) {
            const container = document.getElementById('searchResults'); if (!container) return; container.innerHTML = '';
            if (!products || products.length === 0) { container.innerHTML = '<div class="text-muted">No products found</div>'; return; }
            products.forEach(p => {
                const col = document.createElement('div'); col.className = 'col-md-6';
                const card = document.createElement('div'); card.className = 'card p-2';
                const title = document.createElement('div'); title.innerHTML = `<strong>${p.name}</strong>`;
                const meta = document.createElement('div'); meta.className = 'text-muted small'; meta.innerText = `Price: ${formatCurrencySafe(p.selling_price)} • Available: ${p.available}`;
                const btn = document.createElement('button'); btn.className = 'btn btn-sm btn-primary mt-2'; btn.innerText = 'Add to cart';
                // prefer a modal for quantity entry when available, fallback to prompt
                btn.addEventListener('click', () => {
                    let usedModal = false;
                    if (typeof openQtyModal === 'function') {
                        usedModal = openQtyModal(p, p.available, 1, function (qty) {
                            addToCart(p, p.available, qty);
                            renderCart();
                        });
                    }

                    if (!usedModal) {
                        let q = prompt('Quantity', '1');
                        let qty = parseInt(q, 10) || 1;
                        addToCart(p, p.available, qty);
                    }
                });
                card.appendChild(title); card.appendChild(meta); card.appendChild(btn); col.appendChild(card); container.appendChild(col);
            });
        }

        // Quantity modal helper (requires #qtyModal in DOM)
        function openQtyModal(product, available, defaultQty, cb) {
            const modalEl = document.getElementById('qtyModal');
            if (!modalEl) return false;
            const qtyInput = document.getElementById('qtyModalQuantity');
            const confirmBtn = document.getElementById('qtyModalConfirm');
            const bs = new bootstrap.Modal(modalEl);
            qtyInput.value = defaultQty || 1;
            qtyInput.min = 1;
            function onConfirm() {
                let q = parseInt(qtyInput.value, 10) || 1;
                q = Math.max(1, q);
                if (available !== undefined && q > available) return alert('Insufficient stock');
                try { cb(q); } catch (e) { console.error(e); }
                confirmBtn.removeEventListener('click', onConfirm);
                bs.hide();
            }
            confirmBtn.addEventListener('click', onConfirm);
            bs.show();
            return true;
        }

        // debounce
        function debounce(fn, wait) { let t; return function (...args) { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), wait); } }

        const productSearchEl = document.getElementById('productSearch');
        const doProductSearch = debounce(async function () {
            const q = productSearchEl.value.trim(); if (!q) { document.getElementById('searchResults').innerHTML = ''; return; }
            try {
                const res = await fetch('/cashier/search', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ q }) });
                const data = await res.json(); if (data.results) renderSearchResults(data.results);
            } catch (e) { console.error(e); }
        }, 300);
        if (productSearchEl) productSearchEl.addEventListener('input', doProductSearch);
        const productSearchBtn = document.getElementById('productSearchBtn'); if (productSearchBtn) productSearchBtn.addEventListener('click', doProductSearch);

        // suggestions (lighter UI)
        const suggestionsEl = document.getElementById('productSuggestions');
        function showSuggestions(items) {
            if (!suggestionsEl) return; const suggestions = items || []; suggestionsEl.innerHTML = '';
            if (!suggestions || suggestions.length === 0) { suggestionsEl.style.display = 'none'; return; }
            suggestions.forEach(p => {
                const a = document.createElement('button'); a.type = 'button'; a.className = 'list-group-item list-group-item-action';
                a.innerHTML = `<div class="d-flex justify-content-between"><div><strong>${p.name}</strong><div class="small text-muted">SKU: ${p.sku || ''}</div></div><div class="text-end"><div>${formatCurrencySafe(p.selling_price)}</div><div class="small text-muted">Avail: ${p.available}</div></div></div>`;
                a.addEventListener('click', () => { addToCart(p, p.available, 1); hideSuggestions(); productSearchEl.value = ''; renderCart(); });
                suggestionsEl.appendChild(a);
            });
            suggestionsEl.style.display = 'block';
        }
        function hideSuggestions() { if (!suggestionsEl) return; suggestionsEl.style.display = 'none'; suggestionsEl.innerHTML = ''; }

        // Add customer modal wiring
        const addCustomerBtn = document.getElementById('addCustomerBtn');
        const addCustomerModalEl = document.getElementById('addCustomerModal');
        const addCustomerModal = (addCustomerModalEl ? new bootstrap.Modal(addCustomerModalEl) : null);
        if (addCustomerBtn && addCustomerModal) addCustomerBtn.addEventListener('click', () => addCustomerModal.show());
        const createCustomerBtn = document.getElementById('createCustomerBtn');
        if (createCustomerBtn) createCustomerBtn.addEventListener('click', async function () {
            const name = document.getElementById('newCustomerName').value.trim();
            const phone = document.getElementById('newCustomerPhone').value.trim();
            const email = document.getElementById('newCustomerEmail').value.trim();
            const address = document.getElementById('newCustomerAddress').value.trim();
            const credit_limit = document.getElementById('newCustomerCreditLimit').value.trim();
            const notes = document.getElementById('newCustomerNotes').value.trim();
            const err = document.getElementById('addCustomerError'); if (err) { err.style.display = 'none'; err.innerText = ''; }
            if (!name) { if (err) { err.style.display = 'block'; err.innerText = 'Name is required'; } return; }
            try {
                const res = await fetch('/cashier/customers', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ name, phone, email, address, credit_limit, notes }) });
                const data = await res.json();
                if (res.status === 422 && data && data.errors) {
                    const fld = Object.keys(data.errors)[0]; const msg = data.errors[fld][0] || 'Validation error'; if (err) { err.style.display = 'block'; err.innerText = msg; } return;
                }
                if (data.customer) {
                    const sel = document.getElementById('customerSelect'); if (sel) {
                        const opt = document.createElement('option'); opt.value = data.customer.id; opt.text = data.customer.name + (data.customer.phone ? ' (' + data.customer.phone + ')' : ''); sel.appendChild(opt); sel.value = data.customer.id; sel.dispatchEvent(new Event('change'));
                    }
                    if (addCustomerModal) addCustomerModal.hide();
                } else {
                    if (err) { err.style.display = 'block'; err.innerText = data.error || 'Unable to create customer'; }
                }
            } catch (e) { if (err) { err.style.display = 'block'; err.innerText = 'Request failed'; } }
        });

        // scan barcode
        const scanBtn = document.getElementById('scanBtn');
        if (scanBtn) scanBtn.addEventListener('click', async function () {
            const barcode = document.getElementById('barcode').value; if (!barcode) return alert('Enter or scan a barcode');
            try {
                const res = await fetch('/cashier/scan', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ barcode }) });
                const data = await res.json(); if (data.found) { renderProductCard(data.product, data.available ?? 0); } else alert('Product not found');
            } catch (e) { console.error(e); alert('Scan failed'); }
        });

        const barcodeEl = document.getElementById('barcode'); if (barcodeEl) barcodeEl.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); scanBtn.click(); } });

        // split payment
        const splitBtn = document.getElementById('splitBtn');
        const splitModalEl = document.getElementById('splitModal');
        const splitModal = (splitModalEl ? new bootstrap.Modal(splitModalEl) : null);
        const splitTotalEl = document.getElementById('splitTotal');
        const splitCash = document.getElementById('splitCash');
        const splitMobile = document.getElementById('splitMobile');
        const splitCredit = document.getElementById('splitCredit');
        const confirmSplit = document.getElementById('confirmSplit');
        if (splitBtn && splitModalEl && splitModal) splitBtn.addEventListener('click', function () {
            const rawTotal = cart.reduce((s, i) => s + (i.price * i.qty), 0);
            const overallDiscount = parseFloat(document.getElementById('cartDiscount')?.value || 0) || 0;
            const totalAfter = Math.max(0, rawTotal - overallDiscount);
            splitModalEl.dataset.total = totalAfter;
            if (splitTotalEl) splitTotalEl.innerText = formatCurrencySafe(totalAfter);
            if (splitCash) splitCash.value = totalAfter.toFixed(2);
            if (splitMobile) splitMobile.value = '0.00';
            if (splitCredit) splitCredit.value = '0.00';
            splitModal.show();
        });
        if (confirmSplit) confirmSplit.addEventListener('click', function () {
            const total = parseFloat(splitModalEl.dataset.total || 0);
            const cash = parseFloat(splitCash.value || 0) || 0;
            const mobile = parseFloat(splitMobile.value || 0) || 0;
            const credit = parseFloat(splitCredit.value || 0) || 0;
            const sum = +(cash + mobile + credit).toFixed(2);
            if (Math.abs(sum - total) > 0.01) return alert('Split amounts must add up to total');
            window.__payment_parts = [];
            if (cash > 0) window.__payment_parts.push({ method: 'cash', amount: cash });
            if (mobile > 0) window.__payment_parts.push({ method: 'mobile_money', amount: mobile });
            if (credit > 0) window.__payment_parts.push({ method: 'credit', amount: credit });
            if (splitModal) splitModal.hide();
            alert('Split payment configured');
        });

        // checkout
        const checkoutBtn = document.getElementById('checkoutBtn');
        if (checkoutBtn) checkoutBtn.addEventListener('click', async function () {
            if (cart.length === 0) return alert('Cart is empty');
            const discount = parseFloat(document.getElementById('cartDiscount')?.value || 0) || 0;
            let payment = document.getElementById('paymentMethod').value || 'cash';
            let customerId = null;
            const creditToggle = document.getElementById('creditToggle');
            if (creditToggle && creditToggle.checked) { payment = 'credit'; customerId = document.getElementById('customerSelect').value || null; if (!customerId) return alert('Please select a customer for credit sale'); }
            else customerId = document.getElementById('customerSelect').value || null;
            const payload = { cart, payment, discount }; if (customerId) payload.customer_id = parseInt(customerId, 10);
            if (window.__payment_parts && Array.isArray(window.__payment_parts) && window.__payment_parts.length) payload.payment_parts = window.__payment_parts;
            try {
                const res = await fetch('/cashier/checkout', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify(payload) });
                const data = await res.json();
                if (data.success) {
                    if (data.sale_id) {
                        try {
                            const r = await fetch('/cashier/sales/' + data.sale_id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                            const html = await r.text(); showReceiptModal(html);
                        } catch (e) { try { window.open('/cashier/sales/' + data.sale_id, '_blank'); } catch (_) { } }
                    }
                    alert('Sale complete, id: ' + data.sale_id);
                    cart = [];
                    const discEl = document.getElementById('cartDiscount'); if (discEl) discEl.value = '';
                    renderCart();
                    const sr = document.getElementById('searchResults'); if (sr) sr.innerHTML = '';
                    const bc = document.getElementById('barcode'); if (bc) bc.value = '';
                    if (payment === 'credit' && customerId) {
                        fetch('/cashier/credits/' + customerId).then(r => r.json()).then(d => {
                            try { if (d.balance !== undefined) document.getElementById('customerBalance').innerText = 'Balance: ' + parseFloat(d.balance || 0).toFixed(2); if (d.credit_limit !== undefined) document.getElementById('customerLimit').innerText = 'Credit limit: ' + (d.credit_limit === null ? 'n/a' : parseFloat(d.credit_limit).toFixed(2)); if (Array.isArray(d.credits)) document.getElementById('customerCredits').innerText = 'Open credits: ' + d.credits.length; } catch (e) { }
                        }).catch(() => { });
                    }
                } else {
                    alert('Checkout failed: ' + (data.error || data.message || JSON.stringify(data)));
                }
            } catch (e) { console.error(e); alert('Checkout request failed'); }
        });

        // customer selection change
        const customerSelect = document.getElementById('customerSelect');
        if (customerSelect) customerSelect.addEventListener('change', function () {
            const id = this.value; const info = document.getElementById('customerInfo'); if (!id) { if (info) info.style.display = 'none'; return; }
            fetch('/cashier/credits/' + id).then(r => r.json()).then(d => { if (!info) return; info.style.display = 'block'; try { if (d.balance !== undefined) document.getElementById('customerBalance').innerText = 'Balance: ' + (d.balance ? parseFloat(d.balance).toFixed(2) : '0.00'); if (d.credit_limit !== undefined) document.getElementById('customerLimit').innerText = 'Credit limit: ' + (d.credit_limit === null ? 'n/a' : parseFloat(d.credit_limit).toFixed(2)); if (Array.isArray(d.credits)) document.getElementById('customerCredits').innerText = 'Open credits: ' + (d.credits.length); } catch (e) { } }).catch(() => { if (info) info.style.display = 'none'; });
        });

        // receipt modal helper
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
        if (!document.getElementById('receiptModal')) { const div = document.createElement('div'); div.innerHTML = receiptModalHtml; document.body.appendChild(div); }
        const receiptModalEl = document.getElementById('receiptModal'); const bsReceiptModal = (receiptModalEl ? new bootstrap.Modal(receiptModalEl) : null);
        function showReceiptModal(html) { if (!receiptModalEl) return; document.getElementById('receiptModalBody').innerHTML = html; if (bsReceiptModal) bsReceiptModal.show(); const printBtn = document.getElementById('printReceiptBtn'); if (printBtn) printBtn.onclick = function () { const content = document.getElementById('receiptModalBody').innerHTML; const w = window.open('', '_blank'); w.document.open(); w.document.write('<html><head><title>Receipt</title>'); w.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">'); w.document.write('</head><body>'); w.document.write(content); w.document.write('</body></html>'); w.document.close(); w.focus(); setTimeout(() => { try { w.print(); } catch (e) { } }, 300); }; }

        // initial render
        renderCart();
    });
})();
