(function () {
    'use strict';

    // Single initialiser that sets up receipt modal, drawer handlers and charts.
    function initialiseReceiptModal() {
        const modalEl = document.getElementById('managerReceiptModal');
        const modalBody = document.getElementById('managerReceiptModalBody');
        const printBtn = document.getElementById('managerReceiptPrintBtn');
        if (!modalEl || !modalBody) return;

        let bsModal = null;
        function ensureModal() {
            if (!bsModal) bsModal = new bootstrap.Modal(modalEl);
        }

        document.addEventListener('click', function (e) {
            const btn = e.target.closest && e.target.closest('.view-receipt-btn');
            if (!btn) return;
            e.preventDefault();
            const id = btn.getAttribute('data-sale-id');
            if (!id) return;
            ensureModal();
            modalBody.innerHTML = '<div class="text-center py-4">Loading receipt…</div>';
            bsModal.show();

            fetch('/cashier/sales/' + encodeURIComponent(id), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                credentials: 'same-origin'
            }).then(r => {
                if (!r.ok) throw new Error('Failed to load receipt');
                return r.text();
            }).then(html => {
                modalBody.innerHTML = html;
            }).catch(err => {
                modalBody.innerHTML = '<div class="text-danger">Error loading receipt: ' + (err.message || err) + '</div>';
            });
        });

        if (printBtn) printBtn.addEventListener('click', function () {
            const content = modalBody.innerHTML;
            const w = window.open('', '_blank');
            w.document.open();
            w.document.write('<html><head><title>Receipt</title>');
            w.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
            w.document.write('</head><body>');
            w.document.write(content);
            w.document.write('</body></html>');
            w.document.close();
            w.focus();
            setTimeout(() => { w.print(); }, 250);
        });
    }

    function initialiseChart() {
        const canvas = document.getElementById('salesChart');
        if (!canvas || typeof Chart === 'undefined') return;

        const labelPayload = canvas.dataset.labels || '[]';
        const dataPayload = canvas.dataset.values || '[]';
        let labels = [];
        let values = [];

        try { labels = JSON.parse(labelPayload); } catch (_) { labels = []; }
        try {
            const parsed = JSON.parse(dataPayload);
            values = Array.isArray(parsed) ? parsed.map(v => { const n = Number(v); return Number.isFinite(n) ? n : 0; }) : [];
        } catch (_) { values = []; }

        try { if (canvas._chartInstance && typeof canvas._chartInstance.destroy === 'function') canvas._chartInstance.destroy(); } catch (e) { }

        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        canvas._chartInstance = new Chart(ctx, {
            type: 'line',
            data: { labels, datasets: [{ label: 'Sales', data: values, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,0.1)', fill: true, tension: 0.2 }] },
            options: { responsive: true, maintainAspectRatio: false }
        });

        try { canvas.dataset.lastPayload = JSON.stringify({ labels, values }); } catch (e) { }
        canvas.dataset.chartInitialised = '1';
    }

    function initialiseDrawerHandlers() {
        const links = document.getElementById('managerDrawerLinks');
        const drawerContent = document.getElementById('managerDrawerContent');
        if (!links || !drawerContent) return;

        function hideAllDrawerSections() {
            drawerContent.querySelectorAll('.drawer-section').forEach(s => s.style.display = 'none');
        }

        function showDrawerSection(id) {
            hideAllDrawerSections();
            const el = document.getElementById(id) || document.getElementById('drawer-' + id) || drawerContent.querySelector('#' + id) || drawerContent.querySelector('#drawer-' + id);
            if (el) { el.style.display = ''; try { initialiseChart(); } catch (_) { } return true; }
            return false;
        }

        links.addEventListener('click', function (e) {
            const a = e.target.closest && e.target.closest('a');
            if (!a) return;
            const href = a.getAttribute('href');
            const section = a.dataset.section || (href && href.startsWith('#') ? href.replace('#', '') : null);
            if (section) {
                e.preventDefault();
                showDrawerSection(section);
                try {
                    const mainSectionId = document.getElementById(section) ? section : ('section-' + section.replace(/^drawer-/, ''));
                    const mainEl = document.getElementById(mainSectionId);
                    if (mainEl) { mainEl.style.display = ''; mainEl.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
                } catch (err) { }
                return;
            }

            if (!href) return;
            if (href.startsWith('/') || href.indexOf(location.origin) === 0) {
                e.preventDefault();
                fetch(href, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, credentials: 'same-origin' })
                    .then(r => { if (!r.ok) throw new Error('Failed to load'); return r.text(); })
                    .then(html => {
                        try {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const remoteMain = doc.getElementById('managerMainContent');
                            const localMain = document.getElementById('managerMainContent');
                            if (remoteMain && localMain) {
                                localMain.innerHTML = remoteMain.innerHTML;
                                // re-run initialisers after injection
                                try { managerDashboardInit(); } catch (_) { }
                                try { bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('managerDrawer')).hide(); } catch (_) { }
                                localMain.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                return;
                            }

                            drawerContent.innerHTML = html;
                        } catch (err) {
                            console.error('Inject error', err);
                            location.href = href;
                        }
                    }).catch(err => { console.error(err); location.href = href; });
            }
        });
    }

    // Public initialiser to allow re-initialisation after HTML injection
    window.managerDashboardInit = function managerDashboardInit() {
        try { initialiseReceiptModal(); } catch (e) { console.error('receipt init', e); }
        try { initialiseChart(); } catch (e) { /* ignore */ }
        try { initialiseDrawerHandlers(); } catch (e) { console.error('drawer init', e); }
    };

    // Auto-run once on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', managerDashboardInit, { once: true });
    } else {
        managerDashboardInit();
    }
})();
