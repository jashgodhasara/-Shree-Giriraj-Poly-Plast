<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Material;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductionLog;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ReportsApiController extends Controller
{
    /**
     * GET /api/reports
     * Aggregated business reports for mobile dashboard.
     */
    public function index(Request $request)
    {
        $period = $request->query('period', 'month'); // today, week, month, year, all

        $dateFrom = match($period) {
            'today'  => now()->startOfDay(),
            'week'   => now()->startOfWeek(),
            'month'  => now()->startOfMonth(),
            'year'   => now()->startOfYear(),
            default  => null,
        };

        $invoiceQuery       = Invoice::query();
        $purchaseQuery      = PurchaseOrder::query();
        $productionQuery    = ProductionLog::query();

        if ($dateFrom) {
            $invoiceQuery->where('invoice_date', '>=', $dateFrom);
            $purchaseQuery->where('po_date', '>=', $dateFrom);
            $productionQuery->where('date', '>=', $dateFrom);
        }

        // Sales summary
        $salesStats = $invoiceQuery->selectRaw('
            COUNT(*) as total_invoices,
            SUM(grand_total) as total_revenue,
            SUM(paid_amount) as total_collected,
            SUM(grand_total - paid_amount) as total_pending
        ')->first();

        // Top customers by revenue (all time for context)
        $topCustomers = Invoice::with('customer:id,name')
            ->selectRaw('customer_id, SUM(grand_total) as total')
            ->groupBy('customer_id')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(fn($inv) => [
                'customer_name' => $inv->customer?->name,
                'total'         => (float) $inv->total,
            ]);

        // Top products by quantity sold
        $topProducts = \App\Models\InvoiceItem::with('product:id,name')
            ->selectRaw('product_id, SUM(quantity) as qty_sold, SUM(total_price) as revenue')
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->take(5)
            ->get()
            ->map(fn($item) => [
                'product_name' => $item->product?->name,
                'qty_sold'     => (int) $item->qty_sold,
                'revenue'      => (float) $item->revenue,
            ]);

        // Stock alerts (low stock)
        $lowStock = Material::where('stock_quantity', '<', 10)
            ->orderBy('stock_quantity')
            ->take(10)
            ->get(['id', 'name', 'type', 'stock_quantity', 'unit']);

        // Purchase summary
        $purchaseStats = (clone $purchaseQuery)->selectRaw('
            COUNT(*) as total_orders,
            SUM(grand_total) as total_value
        ')->first();

        // Payment breakdown by mode
        $paymentModes = Payment::selectRaw('payment_mode, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_mode')
            ->get()
            ->map(fn($p) => [
                'mode'  => $p->payment_mode,
                'count' => (int) $p->count,
                'total' => (float) $p->total,
            ]);

        // Monthly revenue (last 6 months)
        $monthlyRevenue = Invoice::selectRaw('
            DATE_FORMAT(invoice_date, "%Y-%m") as month,
            SUM(grand_total) as revenue,
            COUNT(*) as invoice_count
        ')
            ->where('invoice_date', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($r) => [
                'month'         => $r->month,
                'revenue'       => (float) $r->revenue,
                'invoice_count' => (int) $r->invoice_count,
            ]);

        return response()->json([
            'period'          => $period,
            'sales'           => [
                'total_invoices'  => (int)   $salesStats->total_invoices,
                'total_revenue'   => (float) $salesStats->total_revenue,
                'total_collected' => (float) $salesStats->total_collected,
                'total_pending'   => (float) $salesStats->total_pending,
            ],
            'purchases' => [
                'total_orders' => (int)   $purchaseStats->total_orders,
                'total_value'  => (float) $purchaseStats->total_value,
            ],
            'top_customers'  => $topCustomers,
            'top_products'   => $topProducts,
            'low_stock'      => $lowStock,
            'payment_modes'  => $paymentModes,
            'monthly_revenue'=> $monthlyRevenue,
            'overview' => [
                'total_customers' => Customer::count(),
                'total_products'  => Product::count(),
                'total_suppliers' => Supplier::count(),
            ],
        ]);
    }
}
