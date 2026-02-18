<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BarcodeController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\SalesController as AdminSalesController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SupplierController as AdminSupplierController;
use App\Http\Controllers\Admin\UserLogController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Cashier\DashboardController as CashierDashboardController;
use App\Http\Controllers\Cashier\SalesController as CashierSalesController;
use App\Http\Controllers\CustomerCreditController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Manager\PurchasesController;
use App\Http\Controllers\Manager\SalesController as ManagerSalesController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Manager\ManagerDashboardController as ManagerDashboardDataController;

// Default root: show landing welcome page or auto-redirect authenticated users if enabled in settings
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    $settings = null;
    if (Schema::hasTable('settings')) {
        $settings = \App\Models\Setting::first();
    }

    if (auth()->check() && ($settings->auto_redirect ?? false)) {
        $user = auth()->user();
        // Redirect based on simple role field on users table
        switch ($user->role ?? null) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'manager':
                return redirect()->route('manager.dashboard');
            // For cashiers, prefer to show the welcome/landing page by default so
            // the POS link remains available but the user still sees the landing UI.
            // This prevents the site from immediately taking all authenticated
            // users to the cashier dashboard unless an explicit auto-redirect
            // setting is enabled. If you want cashiers to auto-redirect, set
            // the `auto_redirect` flag in the settings table.
            default:
                return view('welcome');
        }
    }

    return view('welcome');
});

// Serve files from storage disk/public when the storage symlink is not present.
use App\Http\Controllers\StorageController;
Route::get('storage/files/{path}', [StorageController::class, 'show'])->where('path', '.*')->name('storage.files.show');
// Also accept the legacy /storage/* path and forward to the controller when the
// public/storage symlink is not available. If the symlink exists the static
// file will be served by the webserver and this route won't be hit.
Route::get('storage/{path}', function ($path) {
    return redirect()->route('storage.files.show', ['path' => $path]);
})->where('path', '.*');

use App\Http\Controllers\RolesController;

// Return taken roles for registration UI
Route::get('/roles/taken', [RolesController::class, 'taken'])->name('roles.taken');

// Registration routes (server-side single-role enforcement)
use App\Http\Controllers\Auth\RegisterController;

Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->middleware('single.role.enforcer');

// Login routes
use App\Http\Controllers\Auth\LoginController;
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
// Note: A GET logout route was previously provided for convenience but POST
// logout is the recommended secure method. The GET logout route has been
// removed to avoid duplicate route surfaces. Use the POST route named
// 'logout' instead.

// Password reset routes (lightweight closures implementing the common flow)
// Provides the named routes: password.request, password.email, password.reset, password.update
Route::get('password/reset', function () {
    return view('auth.passwords.email');
})->name('password.request');

Route::post('password/email', function (Request $request) {
    $request->validate(['email' => 'required|email']);
    $status = Password::sendResetLink($request->only('email'));

    return $status == Password::RESET_LINK_SENT
        ? back()->with('status', __($status))
        : back()->withErrors(['email' => __($status)]);
})->name('password.email');

Route::get('password/reset/{token}', function ($token) {
    return view('auth.passwords.reset', ['token' => $token]);
})->name('password.reset');

Route::post('password/reset', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function (\App\Models\User $user, $password) {
            $user->password = Hash::make($password);
            $user->setRememberToken(Str::random(60));
            $user->save();
        }
    );

    return $status == Password::PASSWORD_RESET
        ? redirect()->route('login')->with('status', __($status))
        : back()->withErrors(['email' => [__($status)]]);
})->name('password.update');

// Role-protected dashboard routes

Route::middleware(['auth'])->group(function () {
    // Admin
    Route::middleware(['ensure.role:admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Allow admins to open the manager-style dashboard for inspection without
    // changing manager middleware. This mounts the manager dashboard controller
    // under an admin-only URL so admins can preview the manager UI.
    Route::get('manager', [ManagerDashboardController::class, 'index'])->name('admin.manager.dashboard');

    // Users management (list, soft-delete)
    Route::get('users', [UserManagementController::class, 'index'])->name('admin.users.index');
    Route::get('users/create', [UserManagementController::class, 'create'])->name('admin.users.create');
    Route::post('users', [UserManagementController::class, 'store'])->name('admin.users.store');
    Route::get('users/{id}/edit', [UserManagementController::class, 'edit'])->name('admin.users.edit');
    Route::put('users/{id}', [UserManagementController::class, 'update'])->name('admin.users.update');
    Route::delete('users/{id}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('users/{id}/restore', [UserManagementController::class, 'restore'])->name('admin.users.restore');

    // System settings
    Route::get('settings', [SettingsController::class, 'edit'])->name('admin.settings.edit');
    Route::post('settings', [SettingsController::class, 'update'])->name('admin.settings.update');
    Route::post('settings/force-logout', [SettingsController::class, 'forceLogout'])->name('admin.settings.force_logout');
        // Audit logs
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('admin.audit.index');
    // User-uploaded logs (simple upload/listing) — routing changed to allow owners access
    // (actual route definitions moved below to be accessible by authenticated owners as well)
        
        // Bulk restore users
        Route::post('users/restore-bulk', [UserManagementController::class, 'restoreBulk'])->name('admin.users.restore_bulk');

        // Reports
        Route::get('reports', [ReportsController::class, 'index'])->name('admin.reports.index');
    Route::get('reports/series', [ReportsController::class, 'series'])->name('admin.reports.series');
        Route::get('reports/export', [ReportsController::class, 'export'])->name('admin.reports.export');
            // Barcode image
                Route::get('products/{product}/barcode.png', [BarcodeController::class, 'image'])->name('admin.products.barcode');
                Route::get('products/labels/print', [BarcodeController::class, 'batch'])->name('admin.products.labels.print');
        
        // Admin product management (reuse ProductController but mounted under /admin)
        Route::resource('products', ProductController::class)->names('admin.products');
    // Admin categories
    Route::resource('categories', AdminCategoryController::class)->names('admin.categories');
    // Admin sales reporting and management
    Route::get('sales/export', [AdminSalesController::class, 'export'])->name('admin.sales.export');
    Route::resource('sales', AdminSalesController::class)->names('admin.sales');
        // Admin customers and suppliers
        Route::resource('customers', CustomerController::class)->names('admin.customers');
    Route::get('customers/trashed', [CustomerController::class, 'trashed'])->name('admin.customers.trashed');
    Route::post('customers/{id}/restore', [CustomerController::class, 'restore'])->name('admin.customers.restore');
    Route::post('customers/restore-bulk', [CustomerController::class, 'restoreBulk'])->name('admin.customers.restore_bulk');
        Route::resource('suppliers', AdminSupplierController::class)->names('admin.suppliers');
    Route::get('suppliers/trashed', [AdminSupplierController::class, 'trashed'])->name('admin.suppliers.trashed');
    Route::post('suppliers/{id}/restore', [AdminSupplierController::class, 'restore'])->name('admin.suppliers.restore');
    Route::post('suppliers/restore-bulk', [AdminSupplierController::class, 'restoreBulk'])->name('admin.suppliers.restore_bulk');
        // NOTE: purchase reports are served via a single canonical route (see below)
    });

    // Reports: admins have full access; managers have limited read-only access to purchases
    // Note: manager-specific purchase reports route is registered under the manager prefix
    // to avoid duplicate dashboards. Admin views should use 'admin.reports.index' or
    // the manager-prefixed 'manager.reports.purchases' where appropriate.

    // Manager
    Route::middleware(['ensure.role:manager'])->prefix('manager')->group(function () {
        Route::get('/', [ManagerDashboardController::class, 'index'])->name('manager.dashboard');
    // Manager sales (read-only), purchases and reports (limited)
    // Managers should NOT create sales; only view them. Cashiers handle live POS checkout.
    Route::resource('sales', ManagerSalesController::class)->only(['index','show'])->names('manager.sales');
        Route::resource('purchases', PurchasesController::class)->names('manager.purchases');
    // Manager report endpoints (limited): allow managers to manage categories, suppliers and view purchase reports and monitor cashiers
    // Managers can manage categories and suppliers (limited admin-equivalent pages under manager prefix)
    Route::resource('categories', AdminCategoryController::class)->names('manager.categories');
    Route::resource('suppliers', AdminSupplierController::class)->names('manager.suppliers');

    // Allow managers to view purchase reports (read-only)
    // (manager links updated to use the canonical route name)

    // Cashier monitoring / audit view for managers (read-only)
    Route::get('cashiers/monitor', [AuditLogController::class, 'index'])->name('manager.cashiers.monitor');
    });

    // POS endpoints (cashiers and admins)
    Route::middleware(['ensure.role:cashier|admin'])->prefix('cashier')->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('cashier.pos');
        // Cashier dashboard (retail-friendly) - shows today's sales, quick links and low-stock
        Route::get('dashboard', [CashierDashboardController::class, 'index'])->name('cashier.dashboard');
    // Sales history and receipt reprint for cashiers
    Route::get('sales', [CashierSalesController::class, 'index'])->name('cashier.sales.index');
    Route::get('sales/{id}', [CashierSalesController::class, 'show'])->name('cashier.sales.show');
    Route::delete('sales/{id}', [CashierSalesController::class, 'destroy'])->name('cashier.sales.destroy');
        Route::post('/scan', [POSController::class, 'scan'])->name('cashier.scan');
    // product search by name/sku for UI autocomplete
    Route::post('/search', [POSController::class, 'search'])->name('cashier.search');
    // inline customer creation for cashier UI
    Route::post('/customers', [POSController::class, 'createCustomer'])->name('cashier.customers.store');
        Route::post('/checkout', [POSController::class, 'checkout'])->name('cashier.checkout');
        Route::get('/credits/{customer}', [CustomerCreditController::class, 'show'])->name('cashier.customer.credits');
    });

    // Credit management endpoints (managers, cashiers and admins can create/settle credits)
    Route::middleware(['ensure.role:manager|cashier|admin'])->prefix('cashier')->group(function () {
        // Create a credit entry for a customer
        Route::post('/credits/{customer}', [CustomerCreditController::class, 'store'])->name('cashier.customer.credits.store');
        // Settle a credit by id
        Route::post('/credits/{id}/settle', [CustomerCreditController::class, 'settle'])->name('cashier.customer.credits.settle');
    });
});

// Product CRUD
Route::middleware(['auth','ensure.role:manager'])->prefix('manager')->group(function(){
    // Name manager product routes under the "manager.products.*" namespace so
    // views that call route('manager.products.index') resolve correctly.
    Route::resource('products', ProductController::class)->names('manager.products');
});

// Backwards-compatibility: register top-level `products.*` names so legacy views
// that call route('products.*') continue to work. These are protected so only
// authenticated admins and managers can access them.
Route::middleware(['auth','ensure.role:admin|manager'])->group(function () {
    Route::resource('products', ProductController::class)->names('products');
});

// Backwards-compatibility: register top-level `categories`, `suppliers`, and `purchases`
// resources so legacy views that expect unprefixed route names keep working.
Route::middleware(['auth','ensure.role:admin|manager'])->group(function () {
    Route::resource('categories', AdminCategoryController::class)->names('categories');
    Route::resource('suppliers', AdminSupplierController::class)->names('suppliers');
    // Purchase endpoints (create/list) used by manager flows — provide top-level names
    Route::resource('purchases', PurchasesController::class)->names('purchases');
    // Canonical purchase reports route (single URL for both admins and managers).
    // Controller will gate view logic based on role (admins see full reports, managers limited view).
    Route::get('reports/purchases', [ReportsController::class, 'purchases'])
        ->name('reports.purchases');
});

// User-uploaded logs: allow authenticated users to upload and view their own logs;
// admins will see all logs. Routes are prefixed with `admin/` path to keep existing
// route names and links (e.g. route('admin.user_logs.index')).
Route::middleware(['auth'])->group(function () {
    Route::get('admin/user-logs', [UserLogController::class, 'index'])->name('admin.user_logs.index');
    Route::post('admin/user-logs', [UserLogController::class, 'store'])->name('admin.user_logs.store');
    Route::get('admin/user-logs/{id}/download', [UserLogController::class, 'download'])->name('admin.user_logs.download');
});

// Lightweight diagnostic route to confirm middleware aliases resolve correctly.
Route::middleware(['auth','ensure.role:admin'])->get('diagnostics/middleware-aliases', function () {
    $aliases = app('router')->getMiddleware();

    return response()->json([
        'timestamp' => now()->toDateTimeString(),
        'aliases' => [
            'ensure.role' => $aliases['ensure.role'] ?? null,
            'single.role.enforcer' => $aliases['single.role.enforcer'] ?? null,
            'audit.log' => $aliases['audit.log'] ?? null,
        ],
        'registered' => [
            'ensure.role' => array_key_exists('ensure.role', $aliases),
            'single.role.enforcer' => array_key_exists('single.role.enforcer', $aliases),
            'audit.log' => array_key_exists('audit.log', $aliases),
        ],
    ]);
})->name('diagnostics.middleware.aliases');

// Lightweight health check for admins: returns DB connectivity and presence of key tables
Route::middleware(['auth','ensure.role:admin'])->get('diagnostics/health', function () {
    $dbOk = false;
    $tables = [];
    try {
        DB::getPdo();
        $dbOk = true;
    } catch (\Exception $e) {
        $dbOk = false;
    }

    $checkTables = ['users', 'sales', 'products', 'purchases', 'suppliers'];
    foreach ($checkTables as $t) {
        $tables[$t] = Schema::hasTable($t);
    }

    return response()->json([
        'timestamp' => now()->toDateTimeString(),
        'db_connected' => $dbOk,
        'tables' => $tables,
    ]);
})->name('diagnostics.health');

// AJAX endpoints for small UI helpers (authenticated)
Route::middleware(['auth'])->get('ajax/products/{id}', [ProductController::class, 'ajaxGet'])->name('ajax.products.get');
Route::middleware(['auth','ensure.role:manager|admin'])->get('/manager/dashboard/data',
    [ManagerDashboardDataController::class, 'data']
)->name('manager.dashboard.data');
