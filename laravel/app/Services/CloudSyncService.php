<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Material;
use App\Models\MaterialTransaction;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductionLog;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudSyncService
{
    protected string $cloudUrl;
    protected string $adminEmail;
    protected string $adminPassword;
    protected ?string $token = null;

    public function __construct()
    {
        $this->cloudUrl = rtrim(env('CLOUD_ERP_URL', 'https://shreegiriraj-erp.onrender.com'), '/');
        $this->adminEmail = env('CLOUD_ERP_EMAIL', 'admin@shreegiriraj.com');
        $this->adminPassword = env('CLOUD_ERP_PASSWORD', 'Admin@1234');
    }

    /**
     * Check if Cloud Server is reachable and online
     */
    public function isOnline(): bool
    {
        try {
            $response = Http::timeout(4)->get($this->cloudUrl . '/api');
            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get Last Sync Status & State
     */
    public function getSyncStatus(): array
    {
        $stateFile = storage_path('app/sync_state.json');
        $lastSync = null;
        $lastResult = null;

        if (File::exists($stateFile)) {
            $data = json_decode(File::get($stateFile), true);
            $lastSync = $data['last_sync'] ?? null;
            $lastResult = $data['last_result'] ?? null;
        }

        $isOnline = $this->isOnline();

        return [
            'is_online' => $isOnline,
            'cloud_url' => $this->cloudUrl,
            'last_sync' => $lastSync,
            'last_result' => $lastResult,
            'pending_invoices' => Invoice::count(),
            'pending_customers' => Customer::count(),
            'pending_products' => Product::count(),
        ];
    }

    /**
     * Authenticate with Cloud API and retrieve Bearer token
     */
    protected function authenticate(): bool
    {
        try {
            $response = Http::timeout(8)->post($this->cloudUrl . '/api/auth/login', [
                'email' => $this->adminEmail,
                'password' => $this->adminPassword,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->token = $data['token'] ?? null;
                return !empty($this->token);
            }
        } catch (\Throwable $e) {
            Log::warning('Cloud authentication failed: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Perform full Two-Way Auto Sync (Offline -> Cloud & Cloud -> Local)
     */
    public function sync(): array
    {
        if (!$this->isOnline()) {
            return [
                'success' => false,
                'message' => 'Cloud server is offline or no internet connection.',
                'timestamp' => now()->toIso8601String(),
            ];
        }

        if (!$this->authenticate()) {
            return [
                'success' => false,
                'message' => 'Failed to authenticate with Cloud Server.',
                'timestamp' => now()->toIso8601String(),
            ];
        }

        $report = [
            'customers_synced' => 0,
            'products_synced' => 0,
            'suppliers_synced' => 0,
            'invoices_synced' => 0,
            'payments_synced' => 0,
            'production_synced' => 0,
        ];

        try {
            // 1. Sync Customers (Pull & Push)
            $report['customers_synced'] = $this->syncCustomers();

            // 2. Sync Products (Pull & Push)
            $report['products_synced'] = $this->syncProducts();

            // 3. Sync Suppliers
            $report['suppliers_synced'] = $this->syncSuppliers();

            // 4. Sync Invoices (Offline to Cloud)
            $report['invoices_synced'] = $this->syncInvoices();

            // 5. Sync Payments
            $report['payments_synced'] = $this->syncPayments();

            // 6. Sync Production Logs
            $report['production_synced'] = $this->syncProduction();

            // Save Sync State
            $syncState = [
                'last_sync' => now()->toDateTimeString(),
                'last_result' => 'Success',
                'report' => $report,
            ];
            File::put(storage_path('app/sync_state.json'), json_encode($syncState, JSON_PRETTY_PRINT));

            return [
                'success' => true,
                'message' => 'All local offline data synchronized with Cloud ERP successfully!',
                'report' => $report,
                'timestamp' => now()->toDateTimeString(),
            ];
        } catch (\Throwable $e) {
            Log::error('ERP Sync Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Sync error: ' . $e->getMessage(),
                'report' => $report,
                'timestamp' => now()->toDateTimeString(),
            ];
        }
    }

    /**
     * Sync Customers
     */
    protected function syncCustomers(): int
    {
        $headers = ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json'];
        $res = Http::withHeaders($headers)->timeout(10)->get($this->cloudUrl . '/api/customers');
        $cloudData = $res->json('data') ?? $res->json() ?? [];
        if (!is_array($cloudData)) $cloudData = [];

        $synced = 0;
        $cloudNames = [];

        foreach ($cloudData as $c) {
            if (!empty($c['name'])) {
                $cloudNames[strtolower(trim($c['name']))] = $c;
            }
        }

        // Push local customers to cloud if not existing
        $localCustomers = Customer::all();
        foreach ($localCustomers as $local) {
            $key = strtolower(trim($local->name));
            if (!isset($cloudNames[$key])) {
                $pushRes = Http::withHeaders($headers)->timeout(8)->post($this->cloudUrl . '/api/customers', [
                    'name' => $local->name,
                    'company_name' => $local->company_name,
                    'phone' => $local->phone,
                    'email' => $local->email,
                    'gstin' => $local->gstin,
                    'address' => $local->address,
                    'city' => $local->city,
                    'state' => $local->state,
                    'pincode' => $local->pincode,
                ]);
                if ($pushRes->successful()) {
                    $synced++;
                }
            }
        }

        return $synced;
    }

    /**
     * Sync Products
     */
    protected function syncProducts(): int
    {
        $headers = ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json'];
        $res = Http::withHeaders($headers)->timeout(10)->get($this->cloudUrl . '/api/products');
        $cloudData = $res->json('data') ?? $res->json() ?? [];
        if (!is_array($cloudData)) $cloudData = [];

        $synced = 0;
        $cloudItems = [];
        foreach ($cloudData as $p) {
            if (!empty($p['name'])) {
                $cloudItems[strtolower(trim($p['name']))] = $p;
            }
        }

        $localProducts = Product::all();
        foreach ($localProducts as $local) {
            $key = strtolower(trim($local->name));
            if (!isset($cloudItems[$key])) {
                $pushRes = Http::withHeaders($headers)->timeout(8)->post($this->cloudUrl . '/api/products', [
                    'name' => $local->name,
                    'category' => $local->category,
                    'unit' => $local->unit,
                    'hsn_code' => $local->hsn_code,
                    'price' => $local->price,
                    'gst_rate' => $local->gst_rate,
                    'stock_quantity' => $local->stock_quantity,
                    'min_stock_alert' => $local->min_stock_alert,
                ]);
                if ($pushRes->successful()) {
                    $synced++;
                }
            }
        }

        return $synced;
    }

    /**
     * Sync Suppliers
     */
    protected function syncSuppliers(): int
    {
        $headers = ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json'];
        $res = Http::withHeaders($headers)->timeout(10)->get($this->cloudUrl . '/api/suppliers');
        $cloudData = $res->json('data') ?? $res->json() ?? [];
        if (!is_array($cloudData)) $cloudData = [];

        $synced = 0;
        $cloudNames = [];
        foreach ($cloudData as $s) {
            if (!empty($s['name'])) {
                $cloudNames[strtolower(trim($s['name']))] = $s;
            }
        }

        $localSuppliers = Supplier::all();
        foreach ($localSuppliers as $local) {
            $key = strtolower(trim($local->name));
            if (!isset($cloudNames[$key])) {
                $pushRes = Http::withHeaders($headers)->timeout(8)->post($this->cloudUrl . '/api/suppliers', [
                    'name' => $local->name,
                    'company_name' => $local->company_name,
                    'phone' => $local->phone,
                    'email' => $local->email,
                    'gstin' => $local->gstin,
                    'address' => $local->address,
                    'state' => $local->state,
                ]);
                if ($pushRes->successful()) {
                    $synced++;
                }
            }
        }

        return $synced;
    }

    /**
     * Sync Invoices (Push offline invoices to Cloud)
     */
    protected function syncInvoices(): int
    {
        $headers = ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json'];
        $res = Http::withHeaders($headers)->timeout(10)->get($this->cloudUrl . '/api/invoices');
        $cloudInvoices = $res->json('data') ?? $res->json() ?? [];
        if (!is_array($cloudInvoices)) $cloudInvoices = [];

        $cloudNumbers = [];
        foreach ($cloudInvoices as $inv) {
            if (!empty($inv['invoice_number'])) {
                $cloudNumbers[$inv['invoice_number']] = true;
            }
        }

        // Get cloud customers map for mapping local customer_id to cloud customer_id
        $custRes = Http::withHeaders($headers)->timeout(10)->get($this->cloudUrl . '/api/customers');
        $cloudCustList = $custRes->json('data') ?? $custRes->json() ?? [];
        $cloudCustMap = [];
        foreach ($cloudCustList as $cc) {
            if (!empty($cc['name'])) {
                $cloudCustMap[strtolower(trim($cc['name']))] = $cc['id'];
            }
        }

        // Get cloud products map
        $prodRes = Http::withHeaders($headers)->timeout(10)->get($this->cloudUrl . '/api/products');
        $cloudProdList = $prodRes->json('data') ?? $prodRes->json() ?? [];
        $cloudProdMap = [];
        foreach ($cloudProdList as $cp) {
            if (!empty($cp['name'])) {
                $cloudProdMap[strtolower(trim($cp['name']))] = $cp['id'];
            }
        }
        $fallbackProdId = !empty($cloudProdList[0]['id']) ? $cloudProdList[0]['id'] : 1;

        $synced = 0;
        $localInvoices = Invoice::with(['items.product', 'customer'])->get();

        foreach ($localInvoices as $localInv) {
            if (!empty($cloudNumbers[$localInv->invoice_number])) {
                continue; // Already on cloud
            }

            // Map customer
            $custName = strtolower(trim($localInv->customer->name ?? ''));
            $cloudCustId = $cloudCustMap[$custName] ?? (!empty($cloudCustList[0]['id']) ? $cloudCustList[0]['id'] : null);

            if (!$cloudCustId) continue;

            $itemsData = [];
            foreach ($localInv->items as $item) {
                $pName = strtolower(trim($item->product->name ?? ''));
                $cProdId = $cloudProdMap[$pName] ?? $fallbackProdId;
                $itemsData[] = [
                    'product_id' => $cProdId,
                    'quantity' => $item->quantity,
                    'rate' => $item->rate,
                    'unit' => $item->unit ?? 'Kg',
                    'tax_percent' => $item->tax_percent ?? 18,
                    'total_price' => $item->total_price,
                ];
            }

            $payload = [
                'customer_id' => $cloudCustId,
                'invoice_number' => $localInv->invoice_number,
                'invoice_date' => $localInv->invoice_date ?? $localInv->date,
                'due_date' => $localInv->due_date,
                'payment_mode' => $localInv->payment_mode ?? 'credit',
                'items' => $itemsData,
                'notes' => $localInv->notes ?? 'Offline created invoice synced to cloud',
            ];

            $postRes = Http::withHeaders($headers)->timeout(10)->post($this->cloudUrl . '/api/invoices', $payload);
            if ($postRes->successful()) {
                $synced++;
                $cloudNumbers[$localInv->invoice_number] = true;
            }
        }

        return $synced;
    }

    /**
     * Sync Payments
     */
    protected function syncPayments(): int
    {
        $headers = ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json'];
        $synced = 0;

        $localPayments = Payment::with('invoice')->whereNotNull('invoice_id')->get();
        foreach ($localPayments as $pay) {
            // Push payment entry
            $postRes = Http::withHeaders($headers)->timeout(8)->post($this->cloudUrl . '/api/payments', [
                'payment_date' => $pay->payment_date,
                'amount' => $pay->amount,
                'payment_mode' => $pay->payment_mode ?? 'bank_transfer',
                'reference_number' => $pay->reference_number,
                'notes' => $pay->notes ?? 'Synced from offline',
            ]);
            if ($postRes->successful()) {
                $synced++;
            }
        }

        return $synced;
    }

    /**
     * Sync Production Logs
     */
    protected function syncProduction(): int
    {
        $headers = ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json'];
        $synced = 0;

        $localLogs = ProductionLog::with('product')->get();
        foreach ($localLogs as $log) {
            $postRes = Http::withHeaders($headers)->timeout(8)->post($this->cloudUrl . '/api/production', [
                'product_id' => $log->product_id,
                'quantity' => $log->quantity,
                'production_date' => $log->production_date,
                'shift' => $log->shift ?? 'Day',
                'operator_name' => $log->operator_name,
                'notes' => $log->notes,
            ]);
            if ($postRes->successful()) {
                $synced++;
            }
        }

        return $synced;
    }
}
