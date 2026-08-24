<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\InvestorApiController;
use App\Http\Controllers\Api\InvoiceApiController;
use App\Http\Controllers\Api\JobWorkApiController;
use App\Http\Controllers\Api\LedgerApiController;
use App\Http\Controllers\Api\MaterialApiController;
use App\Http\Controllers\Api\MaterialTransactionApiController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\ProductionApiController;
use App\Http\Controllers\Api\PurchaseOrderApiController;
use App\Http\Controllers\Api\ReportsApiController;
use App\Http\Controllers\Api\BranchApiController;
use App\Http\Controllers\Api\SupplierApiController;
use App\Http\Controllers\Api\TransporterApiController;
use App\Http\Controllers\Api\PlasticPricingApiController;
use Illuminate\Support\Facades\Route;

// ─── API Documentation (public) ─────────────────────────────────────────────
Route::get('/', function () {
    return response()->json([
        'name'     => 'Shree Giriraj Poly Plast — ERP API',
        'version'  => '2.0',
        'base_url' => url('/api'),
        'auth'     => 'Bearer token (POST /api/auth/login)',
        'endpoints' => [
            '── Auth ──'                         => '',
            'POST /api/auth/login'               => 'Get API token',
            'POST /api/auth/logout'              => 'Revoke token (auth required)',
            'GET  /api/auth/me'                  => 'Current user info (auth required)',
            '── Data (all require Bearer token) ──' => '',
            'GET  /api/dashboard'                => 'Stats + recent invoices',
            'GET  /api/reports'                  => 'Business analytics (period=today|week|month|year)',
            'GET  /api/customers'                => 'List customers',
            'POST /api/customers'                => 'Create customer',
            'GET  /api/products'                 => 'List products',
            'POST /api/products'                 => 'Create product',
            'GET  /api/suppliers'                => 'List suppliers',
            'GET  /api/materials'                => 'List materials + stock',
            'GET  /api/invoices'                 => 'List invoices',
            'POST /api/invoices'                 => 'Create invoice',
            'GET  /api/invoices/{id}'            => 'Invoice detail + items + payments',
            'POST /api/payments'                 => 'Record payment',
            'GET  /api/purchase-orders'          => 'List purchase orders',
            'POST /api/purchase-orders'          => 'Create purchase order',
            'GET  /api/purchase-orders/{id}'     => 'Purchase order detail',
            'POST /api/purchase-orders/{id}/receive' => 'Mark as received',
            'GET  /api/production'               => 'Production logs',
            'GET  /api/material-transactions'    => 'Stock IN/OUT log',
            'GET  /api/ledger'                   => 'Ledger entries',
            'GET  /api/investors'                => 'List investors',
            'GET  /api/job-works'                => 'List job work parties',
        ],
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
});

// ─── Public Auth & Live Data Routes (no token required) ──────────────────────────────────
Route::post('/login', [ApiAuthController::class, 'login'])->name('api.login');
Route::prefix('auth')->group(function () {
    Route::post('/login', [ApiAuthController::class, 'login'])->name('api.auth.login');
});
Route::get('/plastic-prices', [PlasticPricingApiController::class, 'index'])->name('api.plastic-prices.public');
Route::post('/verify-gstin', [\App\Http\Controllers\GstVerificationController::class, 'verify'])->name('api.gstin.verify.public');

// ─── Protected Routes (Sanctum token required) ───────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [ApiAuthController::class, 'logout'])->name('api.auth.logout');
    Route::get('/auth/me',      [ApiAuthController::class, 'me'])->name('api.auth.me');

    // Dashboard & Reports
    Route::get('/dashboard', [DashboardApiController::class, 'index'])->name('api.dashboard');
    Route::get('/reports',   [ReportsApiController::class, 'index'])->name('api.reports');
    Route::post('/verify-gstin', [\App\Http\Controllers\GstVerificationController::class, 'verify'])->name('api.gstin.verify');

    // Masters — all authenticated users (read + write)
    Route::apiResource('customers',    CustomerApiController::class)->names('api.customers');
    Route::apiResource('products',     ProductApiController::class)->names('api.products');
    Route::apiResource('suppliers',    SupplierApiController::class)->names('api.suppliers');
    Route::apiResource('transporters', TransporterApiController::class)->names('api.transporters');
    Route::apiResource('materials',    MaterialApiController::class)->names('api.materials');
    Route::apiResource('investors',    InvestorApiController::class)->names('api.investors');
    Route::post('/job-works/calculate', [JobWorkApiController::class, 'calculate'])->name('api.job-works.calculate');
    Route::get('/products/{product}/weight', [JobWorkApiController::class, 'getProductWeight'])->name('api.products.weight');
    Route::apiResource('job-works',    JobWorkApiController::class)->names('api.job-works');
    Route::apiResource('branches',     BranchApiController::class)->names('api.branches');

    // Invoices (Sales)
    Route::get('/invoices',              [InvoiceApiController::class, 'index'])->name('api.invoices.index');
    Route::post('/invoices',             [InvoiceApiController::class, 'store'])->name('api.invoices.store');
    Route::get('/invoices/{invoice}',    [InvoiceApiController::class, 'show'])->name('api.invoices.show');
    Route::put('/invoices/{invoice}',    [InvoiceApiController::class, 'update'])->name('api.invoices.update');

    // Payments
    Route::get('/payments',              [PaymentApiController::class, 'index'])->name('api.payments.index');
    Route::post('/payments',             [PaymentApiController::class, 'store'])->name('api.payments.store');

    // Purchase Orders (Purchases)
    Route::get('/purchase-orders',                           [PurchaseOrderApiController::class, 'index'])->name('api.purchase-orders.index');
    Route::post('/purchase-orders',                          [PurchaseOrderApiController::class, 'store'])->name('api.purchase-orders.store');
    Route::get('/purchase-orders/{purchaseOrder}',           [PurchaseOrderApiController::class, 'show'])->name('api.purchase-orders.show');
    Route::post('/purchase-orders/{purchaseOrder}/receive',  [PurchaseOrderApiController::class, 'markReceived'])->name('api.purchase-orders.receive');

    // Production
    Route::get('/production',  [ProductionApiController::class, 'index'])->name('api.production.index');
    Route::post('/production', [ProductionApiController::class, 'store'])->name('api.production.store');

    // Material Transactions (Stock IN/OUT)
    Route::get('/material-transactions',  [MaterialTransactionApiController::class, 'index'])->name('api.material-transactions.index');
    Route::post('/material-transactions', [MaterialTransactionApiController::class, 'store'])->name('api.material-transactions.store');

    // Ledger
    Route::get('/ledger',  [LedgerApiController::class, 'index'])->name('api.ledger.index');
    Route::post('/ledger', [LedgerApiController::class, 'store'])->name('api.ledger.store');

    // ── Admin-only destructive actions ───────────────────────────────────────
    Route::middleware('api.admin')->group(function () {
        Route::delete('/invoices/{invoice}',             [InvoiceApiController::class, 'destroy'])->name('api.invoices.destroy');
        Route::delete('/payments/{payment}',             [PaymentApiController::class, 'destroy'])->name('api.payments.destroy');
        Route::delete('/production/{productionLog}',     [ProductionApiController::class, 'destroy'])->name('api.production.destroy');
        Route::delete('/material-transactions/{materialTransaction}', [MaterialTransactionApiController::class, 'destroy'])->name('api.material-transactions.destroy');
        Route::delete('/ledger/{ledger}',                [LedgerApiController::class, 'destroy'])->name('api.ledger.destroy');
        Route::delete('/purchase-orders/{purchaseOrder}',[PurchaseOrderApiController::class, 'destroy'])->name('api.purchase-orders.destroy')->missing(fn() => response()->json(['error' => 'Not found'], 404));
    });
});
