<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Ledger;
use App\Models\Material;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $totalSales        = Invoice::sum('grand_total');
        $invoiceCount      = Invoice::count();
        $totalPurchases    = PurchaseOrder::where('status', 'Received')->sum('grand_total');
        $poCount           = PurchaseOrder::count();

        $customerDebits    = Ledger::where('entity_type', 'Customer')->where('type', 'Debit')->sum('amount');
        $customerCredits   = Ledger::where('entity_type', 'Customer')->where('type', 'Credit')->sum('amount');
        $totalAr           = max(0, $customerDebits - $customerCredits);

        $supplierCredits   = Ledger::where('entity_type', 'Supplier')->where('type', 'Credit')->sum('amount');
        $supplierDebits    = Ledger::where('entity_type', 'Supplier')->where('type', 'Debit')->sum('amount');
        $totalAp           = max(0, $supplierCredits - $supplierDebits);

        $lowStockMaterials = Material::where('stock_quantity', '<=', 10)->get();
        $recentInvoices    = Invoice::with('customer')->latest()->take(5)->get();
        $recentPos         = PurchaseOrder::with('supplier')->latest()->take(5)->get();

        return view('reports.index', compact(
            'totalSales',
            'invoiceCount',
            'totalPurchases',
            'poCount',
            'totalAr',
            'totalAp',
            'lowStockMaterials',
            'recentInvoices',
            'recentPos'
        ));
    }
}
