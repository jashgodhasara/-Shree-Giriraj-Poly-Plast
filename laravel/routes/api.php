<?php

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
use App\Http\Controllers\Api\SupplierApiController;
use App\Http\Controllers\Api\TransporterApiController;
use Illuminate\Support\Facades\Route;

// API Documentation index
Route::get('/', function () {
    return response()->json([
        'name'    => 'Shree Giriraj Poly Plast — ERP API',
        'version' => '1.0',
        'base_url'=> url('/api'),
        'web_erp' => url('/'),
        'endpoints' => [
            'GET  /api/dashboard'              => 'Stats + recent invoices',
            'GET  /api/customers'              => 'List customers',
            'POST /api/customers'              => 'Create customer',
            'GET  /api/products'               => 'List products',
            'POST /api/products'               => 'Create product',
            'GET  /api/suppliers'              => 'List suppliers',
            'POST /api/suppliers'              => 'Create supplier',
            'GET  /api/transporters'           => 'List transporters',
            'GET  /api/materials'              => 'List materials + stock',
            'GET  /api/invoices'               => 'List invoices',
            'POST /api/invoices'               => 'Create invoice',
            'GET  /api/invoices/{id}'          => 'Invoice detail + items + payments',
            'POST /api/payments'               => 'Record payment',
            'GET  /api/production'             => 'Production logs',
            'POST /api/production'             => 'Log production run',
            'GET  /api/material-transactions'  => 'Stock IN/OUT log',
            'POST /api/material-transactions'  => 'Add stock transaction',
            'GET  /api/ledger'                 => 'Ledger entries',
            'POST /api/ledger'                 => 'Add ledger entry',
            'GET  /api/investors'              => 'List investors',
            'GET  /api/job-works'              => 'List job work parties',
        ],
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
});

// Dashboard
Route::get('/dashboard', [DashboardApiController::class, 'index']);

// Masters
Route::apiResource('customers',    CustomerApiController::class)->names('api.customers');
Route::apiResource('products',     ProductApiController::class)->names('api.products');
Route::apiResource('suppliers',    SupplierApiController::class)->names('api.suppliers');
Route::apiResource('transporters', TransporterApiController::class)->names('api.transporters');
Route::apiResource('materials',    MaterialApiController::class)->names('api.materials');
Route::apiResource('investors',    InvestorApiController::class)->names('api.investors');
Route::apiResource('job-works',    JobWorkApiController::class)->names('api.job-works');

// Invoices
Route::get('/invoices',              [InvoiceApiController::class, 'index']);
Route::post('/invoices',             [InvoiceApiController::class, 'store']);
Route::get('/invoices/{invoice}',    [InvoiceApiController::class, 'show']);
Route::put('/invoices/{invoice}',    [InvoiceApiController::class, 'update']);
Route::delete('/invoices/{invoice}', [InvoiceApiController::class, 'destroy']);

// Payments
Route::get('/payments',          [PaymentApiController::class, 'index']);
Route::post('/payments',         [PaymentApiController::class, 'store']);
Route::delete('/payments/{payment}', [PaymentApiController::class, 'destroy']);

// Production
Route::get('/production',                       [ProductionApiController::class, 'index']);
Route::post('/production',                      [ProductionApiController::class, 'store']);
Route::delete('/production/{productionLog}',    [ProductionApiController::class, 'destroy']);

// Material Transactions (stock IN/OUT)
Route::get('/material-transactions',                             [MaterialTransactionApiController::class, 'index']);
Route::post('/material-transactions',                            [MaterialTransactionApiController::class, 'store']);
Route::delete('/material-transactions/{materialTransaction}',    [MaterialTransactionApiController::class, 'destroy']);

// Ledger
Route::get('/ledger',             [LedgerApiController::class, 'index']);
Route::post('/ledger',            [LedgerApiController::class, 'store']);
Route::delete('/ledger/{ledger}', [LedgerApiController::class, 'destroy']);
