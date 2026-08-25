<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvestorController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JobWorkController;
use App\Http\Controllers\JobWorkOrderController;
use App\Http\Controllers\JobWorkClientController;
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

// GSTIN Live Verification (Fast utility endpoint)
Route::post('/verify-gstin', [\App\Http\Controllers\GstVerificationController::class, 'verify'])->name('gstin.verify');


// ── Authenticated routes ─────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Change Password
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('change-password');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('change-password.update');
    Route::post('/users/switch', [AuthController::class, 'switchUser'])->name('users.switch');

    // Dashboard & Working Date Switcher
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/working-date/set', [DashboardController::class, 'setWorkingDate'])->name('working-date.set');
    Route::post('/working-date/reset', [DashboardController::class, 'resetWorkingDate'])->name('working-date.reset');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');


    // Live Plastic Pricing Feed
    Route::get('/plastic-prices', [App\Http\Controllers\Api\PlasticPricingApiController::class, 'index'])->name('plastic-prices.index');

    // Onboarding Configurator
    Route::get('/onboard', [OnboardingController::class, 'index'])->name('onboard.index');
    Route::post('/onboard', [OnboardingController::class, 'saveConfig'])->name('onboard.save');

    // Multi-Location Branches
    Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
    Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
    Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
    Route::post('/branches/switch', [BranchController::class, 'switchBranch'])->name('branches.switch');
    Route::delete('/branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');

    // Customers
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // Products & Product Inventory
    Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
    Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
    Route::get('/products/check-duplicate', [ProductController::class, 'checkDuplicate'])->name('products.check-duplicate');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Product Categories Master
    Route::get('/categories', [\App\Http\Controllers\ProductCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [\App\Http\Controllers\ProductCategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [\App\Http\Controllers\ProductCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [\App\Http\Controllers\ProductCategoryController::class, 'destroy'])->name('categories.destroy');

    // Unit Master & Conversions
    Route::get('/units', [\App\Http\Controllers\UnitController::class, 'index'])->name('units.index');
    Route::post('/units', [\App\Http\Controllers\UnitController::class, 'store'])->name('units.store');
    Route::put('/units/{unit}', [\App\Http\Controllers\UnitController::class, 'update'])->name('units.update');
    Route::post('/unit-conversions', [\App\Http\Controllers\UnitController::class, 'storeConversion'])->name('units.conversions.store');
    Route::delete('/unit-conversions/{conversion}', [\App\Http\Controllers\UnitController::class, 'destroyConversion'])->name('units.conversions.destroy');

    // Complete ERP Inventory System
    Route::get('/inventory/dashboard', [\App\Http\Controllers\InventoryController::class, 'dashboard'])->name('inventory.dashboard');
    Route::get('/inventory/ledger', [\App\Http\Controllers\InventoryController::class, 'ledger'])->name('inventory.ledger');
    Route::get('/inventory/low-stock', [\App\Http\Controllers\InventoryController::class, 'lowStock'])->name('inventory.low-stock');
    Route::get('/inventory/valuation', [\App\Http\Controllers\InventoryController::class, 'valuation'])->name('inventory.valuation');
    
    // Stock Adjustments
    Route::get('/inventory/adjustments', [\App\Http\Controllers\InventoryController::class, 'adjustmentsIndex'])->name('inventory.adjustments.index');
    Route::post('/inventory/adjustments', [\App\Http\Controllers\InventoryController::class, 'adjustmentStore'])->name('inventory.adjustments.store');

    // Stock Transfers
    Route::get('/inventory/transfers', [\App\Http\Controllers\InventoryController::class, 'transfersIndex'])->name('inventory.transfers.index');
    Route::post('/inventory/transfers', [\App\Http\Controllers\InventoryController::class, 'transferStore'])->name('inventory.transfers.store');

    // Warehouse Master
    Route::get('/warehouses', [\App\Http\Controllers\InventoryController::class, 'warehousesIndex'])->name('warehouses.index');
    Route::post('/warehouses', [\App\Http\Controllers\InventoryController::class, 'warehouseStore'])->name('warehouses.store');
    Route::put('/warehouses/{warehouse}', [\App\Http\Controllers\InventoryController::class, 'warehouseUpdate'])->name('warehouses.update');

    // Dyes & Moulds Tool Room Inventory
    Route::get('/dyes', [\App\Http\Controllers\DyeController::class, 'index'])->name('dyes.index');
    Route::post('/dyes', [\App\Http\Controllers\DyeController::class, 'store'])->name('dyes.store');
    Route::get('/dyes/{dye}', [\App\Http\Controllers\DyeController::class, 'show'])->name('dyes.show');
    Route::put('/dyes/{dye}', [\App\Http\Controllers\DyeController::class, 'update'])->name('dyes.update');
    Route::delete('/dyes/{dye}', [\App\Http\Controllers\DyeController::class, 'destroy'])->name('dyes.destroy');
    Route::post('/dyes/{dye}/maintenance', [\App\Http\Controllers\DyeController::class, 'logMaintenance'])->name('dyes.log-maintenance');
    Route::post('/dyes/{dye}/shots', [\App\Http\Controllers\DyeController::class, 'updateShots'])->name('dyes.update-shots');

    // Factory Machinery & Plant Assets
    Route::get('/factory-assets', [\App\Http\Controllers\FactoryAssetController::class, 'index'])->name('factory-assets.index');
    Route::post('/factory-assets', [\App\Http\Controllers\FactoryAssetController::class, 'store'])->name('factory-assets.store');
    Route::get('/factory-assets/{factoryAsset}', [\App\Http\Controllers\FactoryAssetController::class, 'show'])->name('factory-assets.show');
    Route::put('/factory-assets/{factoryAsset}', [\App\Http\Controllers\FactoryAssetController::class, 'update'])->name('factory-assets.update');
    Route::delete('/factory-assets/{factoryAsset}', [\App\Http\Controllers\FactoryAssetController::class, 'destroy'])->name('factory-assets.destroy');
    Route::post('/factory-assets/{factoryAsset}/maintenance', [\App\Http\Controllers\FactoryAssetController::class, 'logMaintenance'])->name('factory-assets.log-maintenance');

    // Staff & Employees Management
    Route::get('/employees', [\App\Http\Controllers\EmployeeController::class, 'index'])->name('employees.index');
    Route::post('/employees', [\App\Http\Controllers\EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}', [\App\Http\Controllers\EmployeeController::class, 'show'])->name('employees.show');
    Route::put('/employees/{employee}', [\App\Http\Controllers\EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [\App\Http\Controllers\EmployeeController::class, 'destroy'])->name('employees.destroy');

    // Daily Attendance & Monthly Matrix
    Route::get('/attendance', [\App\Http\Controllers\AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/daily', [\App\Http\Controllers\AttendanceController::class, 'storeDaily'])->name('attendance.store-daily');
    Route::post('/attendance/mark-all-present', [\App\Http\Controllers\AttendanceController::class, 'markAllPresent'])->name('attendance.mark-all-present');
    Route::get('/attendance/monthly', [\App\Http\Controllers\AttendanceController::class, 'monthly'])->name('attendance.monthly');

    // Staff Advances & Upad
    Route::get('/employee-advances', [\App\Http\Controllers\EmployeeAdvanceController::class, 'index'])->name('employee-advances.index');
    Route::post('/employee-advances', [\App\Http\Controllers\EmployeeAdvanceController::class, 'store'])->name('employee-advances.store');
    Route::delete('/employee-advances/{employeeAdvance}', [\App\Http\Controllers\EmployeeAdvanceController::class, 'destroy'])->name('employee-advances.destroy');

    // Monthly Salary & Payroll
    Route::get('/payroll', [\App\Http\Controllers\PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/payroll/generate-all', [\App\Http\Controllers\PayrollController::class, 'generateAll'])->name('payroll.generate-all');
    Route::post('/payroll/generate/{employee}', [\App\Http\Controllers\PayrollController::class, 'generateSingle'])->name('payroll.generate-single');
    Route::post('/payroll/{payroll}/pay', [\App\Http\Controllers\PayrollController::class, 'markAsPaid'])->name('payroll.mark-paid');
    Route::get('/payroll/{payroll}/payslip', [\App\Http\Controllers\PayrollController::class, 'payslip'])->name('payroll.payslip');

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
    Route::post('/materials/sync-api', [MaterialController::class, 'syncFromApi'])->name('materials.sync-api');
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

    // Job Works (Automatic Weight & Quantity Module)
    Route::get('/job-works/dashboard', [JobWorkOrderController::class, 'dashboard'])->name('jobworks.dashboard');
    Route::get('/job-works', [JobWorkOrderController::class, 'index'])->name('jobworks.index');
    Route::get('/job-works/create', [JobWorkOrderController::class, 'create'])->name('jobworks.create');
    Route::post('/job-works', [JobWorkOrderController::class, 'store'])->name('jobworks.store');
    Route::post('/job-works/calculate-ajax', [JobWorkOrderController::class, 'calculateAjax'])->name('jobworks.calculate-ajax');
    Route::get('/job-works/reports', [JobWorkOrderController::class, 'reports'])->name('jobworks.reports');
    Route::get('/job-works/{jobWorkOrder}', [JobWorkOrderController::class, 'show'])->name('jobworks.show');
    Route::get('/job-works/{jobWorkOrder}/edit', [JobWorkOrderController::class, 'edit'])->name('jobworks.edit');
    Route::put('/job-works/{jobWorkOrder}', [JobWorkOrderController::class, 'update'])->name('jobworks.update');
    Route::delete('/job-works/{jobWorkOrder}', [JobWorkOrderController::class, 'destroy'])->name('jobworks.destroy');
    Route::get('/job-works/{jobWorkOrder}/duplicate', [JobWorkOrderController::class, 'duplicate'])->name('jobworks.duplicate');
    Route::post('/job-works/{jobWorkOrder}/status', [JobWorkOrderController::class, 'updateStatus'])->name('jobworks.status.update');
    Route::post('/job-works/{jobWorkOrder}/delivery', [JobWorkOrderController::class, 'recordDelivery'])->name('jobworks.delivery.record');
    Route::get('/job-works/{jobWorkOrder}/print', [JobWorkOrderController::class, 'print'])->name('jobworks.print');

    // Job Work Clients / Parties
    Route::get('/job-work-clients', [JobWorkClientController::class, 'index'])->name('jobworks.clients.index');
    Route::post('/job-work-clients', [JobWorkClientController::class, 'store'])->name('jobworks.clients.store');
    Route::put('/job-work-clients/{client}', [JobWorkClientController::class, 'update'])->name('jobworks.clients.update');
    Route::delete('/job-work-clients/{client}', [JobWorkClientController::class, 'destroy'])->name('jobworks.clients.destroy');

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
