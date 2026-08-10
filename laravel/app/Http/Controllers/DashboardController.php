<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductionLog;
use App\Models\PurchaseOrder;
use App\Models\Supplier;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'customers'               => Customer::count(),
            'suppliers'               => Supplier::count(),
            'products'                => Product::count(),
            'product_stock_total'     => Product::sum('stock_quantity'),
            'invoices'                => Invoice::count(),
            'revenue'                 => Invoice::sum('grand_total'),
            'paid'                    => Invoice::where('status', 'Paid')->sum('grand_total'),
            'unpaid'                  => Invoice::whereIn('status', ['Unpaid', 'Partial'])
                                            ->selectRaw('SUM(grand_total - paid_amount) as total')
                                            ->value('total') ?? 0,
            'productions'             => ProductionLog::count(),
            'purchase_orders_pending' => PurchaseOrder::where('status', 'Pending')->count(),
            'raw_materials'           => Material::where('type', 'Raw Material')->sum('stock_quantity'),
            'final_products'          => Material::where('type', 'Final Product')->sum('stock_quantity'),
        ];

        $recentInvoices = Invoice::with('customer')
            ->latest()
            ->take(8)
            ->get();

        $lowStock = Material::where('stock_quantity', '<=', 50)
            ->orderBy('stock_quantity')
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'recentInvoices', 'lowStock'));
    }
}
