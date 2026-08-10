<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductionLog;
use App\Models\Supplier;

class DashboardApiController extends Controller
{
    public function index()
    {
        return response()->json([
            'stats' => [
                'customers'        => Customer::count(),
                'products'         => Product::count(),
                'suppliers'        => Supplier::count(),
                'invoices'         => Invoice::count(),
                'total_revenue'    => (float) Invoice::sum('grand_total'),
                'production_runs'  => ProductionLog::count(),
                'low_stock_items'  => Material::where('stock_quantity', '<', 10)->count(),
            ],
            'recent_invoices' => Invoice::with('customer')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn($inv) => [
                    'id'             => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'customer_name'  => $inv->customer->name,
                    'grand_total'    => (float) $inv->grand_total,
                    'status'         => $inv->status,
                    'invoice_date'   => $inv->invoice_date->format('Y-m-d'),
                ]),
            'low_stock' => Material::where('stock_quantity', '<', 10)
                ->orderBy('stock_quantity')
                ->take(5)
                ->get(['id', 'name', 'type', 'stock_quantity', 'unit']),
        ]);
    }
}
