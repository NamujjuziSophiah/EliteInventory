// Small formatting utilities for client-side displays
window.formatCurrencyJS = function (amount, symbol = 'UGX', decimals = 0) {
    if (amount === null || amount === undefined || amount === '') return '';
    const n = Number(amount) || 0;
    // Use locale-aware formatting; UG uses en-UG where available
    try {
        return symbol + ' ' + n.toLocaleString('en-UG', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
    } catch (e) {
        return symbol + ' ' + n.toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
};
