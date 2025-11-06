# Testing & QA Plan

This is a lightweight QA plan to validate critical flows and ensure mobile usability.

Automated tests (PHPUnit)
- Unit tests: models (balance calculations), policies (role checks).
- Feature tests: POS checkout reduces stock and creates sale, customer credit creation and settlement, admin user deletion frees role.

Manual QA checklist
- Authentication
  - Register as each role (verify single-user-per-role behavior) and confirm role selection disabled when taken.
  - Login/logout flows and session timeout checks.

- POS
  - Scan barcode via input; verify product lookup returns correct product.
  - Add items to cart, remove items, apply discounts and taxes.
  - Checkout with cash and with credit — verify stock decrement and credit entries.
  - Test on mobile (Android Chrome, iOS Safari) — touch targets and sticky cart.

- Inventory
  - Add/edit product with image; verify image upload and thumbnail generation.
  - Restock using restock logs; verify supplier credit creation.

- Credits & Debts
  - Create customer credit with due date; verify balance updates and overdue listing.
  - Settle a credit and verify balance decreases.

- Reporting & Exports
  - Generate daily sales report and export PDF/Excel.

Performance & Edge Cases
- Large product catalog: pagination and search responsiveness.
- Concurrent checkouts: ensure transactions prevent negative stock.

Linting & CI
- Add PHP CS Fixer / PHPCS rules and run in CI.
- Run `php artisan test` in CI and fail on test failures.
