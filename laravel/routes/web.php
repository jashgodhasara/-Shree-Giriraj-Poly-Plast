<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvestorController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JobWorkController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MaterialTransactionController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransporterController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

// ── Guest routes (unauthenticated) ──────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    // Social Authentication (Google & Facebook)
    Route::get('/auth/{provider}/redirect', [AuthController::class, 'redirectToProvider'])->name('auth.social.redirect');
    Route::get('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback'])->name('auth.social.callback');
});

// ── Authenticated routes ─────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Change Password
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('change-password');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('change-password.update');

    // Dashboard & Working Date Switcher
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/working-date/set', [DashboardController::class, 'setWorkingDate'])->name('working-date.set');
    Route::post('/working-date/reset', [DashboardController::class, 'resetWorkingDate'])->name('working-date.reset');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // GSTIN Live Verification
    Route::post('/verify-gstin', [\App\Http\Controllers\GstVerificationController::class, 'verify'])->name('gstin.verify');

    // Onboarding Configurator
    Route::get('/onboard', [OnboardingController::class, 'index'])->name('onboard.index');
    Route::post('/onboard', [OnboardingController::class, 'saveConfig'])->name('onboard.save');

    // Multi-Location Branches
    Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
    Route::post('/branches/switch', [BranchController::class, 'switchBranch'])->name('branches.switch');

    // Customers
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // Products
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/billing', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('/invoices/{invoice}/challan', [InvoiceController::class, 'challan'])->name('invoices.challan');
    Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    Route::get('/invoices/{invoice}/payments', [InvoiceController::class, 'payments'])->name('invoices.payments');

    // Payments
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    // Suppliers
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

    // Purchase Orders & Bills
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
    Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
    Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    Route::get('/purchase-orders/{purchaseOrder}/print', [PurchaseOrderController::class, 'print'])->name('purchase-orders.print');
    Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'markReceived'])->name('purchase-orders.receive');
    Route::delete('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');

    // Materials
    Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::post('/materials', [MaterialController::class, 'store'])->name('materials.store');
    Route::put('/materials/{material}', [MaterialController::class, 'update'])->name('materials.update');
    Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');

    // Material Transactions (Raw Material IN/OUT)
    Route::get('/material-transactions', [MaterialTransactionController::class, 'index'])->name('material-transactions.index');
    Route::post('/material-transactions', [MaterialTransactionController::class, 'store'])->name('material-transactions.store');
    Route::delete('/material-transactions/{materialTransaction}', [MaterialTransactionController::class, 'destroy'])->name('material-transactions.destroy');

    // Transporters
    Route::get('/transporters', [TransporterController::class, 'index'])->name('transporters.index');
    Route::post('/transporters', [TransporterController::class, 'store'])->name('transporters.store');
    Route::put('/transporters/{transporter}', [TransporterController::class, 'update'])->name('transporters.update');
    Route::delete('/transporters/{transporter}', [TransporterController::class, 'destroy'])->name('transporters.destroy');

    // Investors
    Route::get('/investors', [InvestorController::class, 'index'])->name('investors.index');
    Route::post('/investors', [InvestorController::class, 'store'])->name('investors.store');
    Route::put('/investors/{investor}', [InvestorController::class, 'update'])->name('investors.update');
    Route::delete('/investors/{investor}', [InvestorController::class, 'destroy'])->name('investors.destroy');

    // Job Works
    Route::get('/job-works', [JobWorkController::class, 'index'])->name('jobworks.index');
    Route::post('/job-works', [JobWorkController::class, 'store'])->name('jobworks.store');
    Route::put('/job-works/{jobWork}', [JobWorkController::class, 'update'])->name('jobworks.update');
    Route::delete('/job-works/{jobWork}', [JobWorkController::class, 'destroy'])->name('jobworks.destroy');

    // Production
    Route::get('/production', [ProductionController::class, 'index'])->name('production.index');
    Route::post('/production', [ProductionController::class, 'store'])->name('production.store');
    Route::delete('/production/{productionLog}', [ProductionController::class, 'destroy'])->name('production.destroy');

    // Ledger
    Route::get('/ledger', [LedgerController::class, 'index'])->name('ledger.index');
    Route::post('/ledger', [LedgerController::class, 'store'])->name('ledger.store');
    Route::delete('/ledger/{ledger}', [LedgerController::class, 'destroy'])->name('ledger.destroy');

    // ── Admin only ───────────────────────────────────────────────────
    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])->name('users.toggle-active');
    });
});
