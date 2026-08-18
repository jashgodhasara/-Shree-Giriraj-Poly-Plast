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
    private function resolveDates(?string $preset, ?string $dateFrom, ?string $dateTo): array
    {
        $today = \Carbon\Carbon::today();

        if ($preset && $preset !== 'custom') {
            return match ($preset) {
                'today'        => [$today->toDateString(), $today->toDateString()],
                'yesterday'    => [\Carbon\Carbon::yesterday()->toDateString(), [\Carbon\Carbon::yesterday()->toDateString()]],
                'this_month'   => [$today->copy()->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()],
                'last_month'   => [$today->copy()->subMonth()->startOfMonth()->toDateString(), $today->copy()->subMonth()->endOfMonth()->toDateString()],
                'last_3months' => [$today->copy()->subMonths(3)->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()],
                'this_year'    => [$today->copy()->startOfYear()->toDateString(), $today->copy()->endOfYear()->toDateString()],
                'last_year'    => [$today->copy()->subYear()->startOfYear()->toDateString(), $today->copy()->subYear()->endOfYear()->toDateString()],
                default        => [$dateFrom ? \Carbon\Carbon::parse($dateFrom)->toDateString() : '', $dateTo ? \Carbon\Carbon::parse($dateTo)->toDateString() : ''],
            };
        }

        $from = $dateFrom ? \Carbon\Carbon::parse($dateFrom)->toDateString() : '';
        $to   = $dateTo ? \Carbon\Carbon::parse($dateTo)->toDateString() : '';

        return [$from, $to];
    }

    public function index(Request $request)
    {
        $preset   = $request->get('preset', '');
        $dateFrom = $request->get('date_from', '');
        $dateTo   = $request->get('date_to', '');
        [$dateFrom, $dateTo] = $this->resolveDates($preset, $dateFrom, $dateTo);

        $invQuery = Invoice::query();
        $poQuery  = PurchaseOrder::where('status', 'Received');
        $ledQuery = Ledger::query();

        if ($dateFrom) {
            $invQuery->whereDate('invoice_date', '>=', $dateFrom);
            $poQuery->whereDate('po_date', '>=', $dateFrom);
            $ledQuery->whereDate('transaction_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $invQuery->whereDate('invoice_date', '<=', $dateTo);
            $poQuery->whereDate('po_date', '<=', $dateTo);
            $ledQuery->whereDate('transaction_date', '<=', $dateTo);
        }

        $totalSales        = (clone $invQuery)->sum('grand_total');
        $invoiceCount      = (clone $invQuery)->count();
        $totalPurchases    = (clone $poQuery)->sum('grand_total');
        $poCount           = (clone $poQuery)->count();

        $customerDebits    = (clone $ledQuery)->where('entity_type', 'Customer')->where('type', 'Debit')->sum('amount');
        $customerCredits   = (clone $ledQuery)->where('entity_type', 'Customer')->where('type', 'Credit')->sum('amount');
        $totalAr           = max(0, $customerDebits - $customerCredits);

        $supplierCredits   = (clone $ledQuery)->where('entity_type', 'Supplier')->where('type', 'Credit')->sum('amount');
        $supplierDebits    = (clone $ledQuery)->where('entity_type', 'Supplier')->where('type', 'Debit')->sum('amount');
        $totalAp           = max(0, $supplierCredits - $supplierDebits);

        $lowStockMaterials = Material::where('stock_quantity', '<=', 10)->get();
        $recentInvoices    = Invoice::with('customer')->latest('invoice_date')->latest('id')->take(5)->get();
        $recentPos         = PurchaseOrder::with('supplier')->latest('po_date')->latest('id')->take(5)->get();

        return view('reports.index', compact(
            'totalSales',
            'invoiceCount',
            'totalPurchases',
            'poCount',
            'totalAr',
            'totalAp',
            'lowStockMaterials',
            'recentInvoices',
            'recentPos',
            'preset',
            'dateFrom',
            'dateTo'
        ));
    }
}
