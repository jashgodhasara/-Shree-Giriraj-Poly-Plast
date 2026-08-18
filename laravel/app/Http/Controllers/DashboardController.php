<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductionLog;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public static function resolveDateFilter(): array
    {
        $today = Carbon::today();
        $preset = session('date_filter_preset');
        $mode = session('date_filter_mode', 'all');
        $dateFrom = session('date_from');
        $dateTo = session('date_to');
        $workingDate = session('working_date', $today->toDateString());

        if ($preset === 'today') {
            $dateFrom = $today->toDateString();
            $dateTo = $today->toDateString();
            $label = 'Today (' . $today->format('d M') . ')';
        } elseif ($preset === 'yesterday') {
            $yesterday = Carbon::yesterday();
            $dateFrom = $yesterday->toDateString();
            $dateTo = $yesterday->toDateString();
            $label = 'Yesterday (' . $yesterday->format('d M') . ')';
        } elseif ($preset === 'this_month') {
            $dateFrom = $today->copy()->startOfMonth()->toDateString();
            $dateTo = $today->copy()->endOfMonth()->toDateString();
            $label = 'This Month (' . $today->format('M Y') . ')';
        } elseif ($preset === 'last_month') {
            $lastMonth = $today->copy()->subMonth();
            $dateFrom = $lastMonth->copy()->startOfMonth()->toDateString();
            $dateTo = $lastMonth->copy()->endOfMonth()->toDateString();
            $label = 'Last Month (' . $lastMonth->format('M Y') . ')';
        } elseif ($preset === 'this_year') {
            $dateFrom = $today->copy()->startOfYear()->toDateString();
            $dateTo = $today->copy()->endOfYear()->toDateString();
            $label = 'This Year (' . $today->format('Y') . ')';
        } elseif ($preset === 'last_year') {
            $lastYear = $today->copy()->subYear();
            $dateFrom = $lastYear->copy()->startOfYear()->toDateString();
            $dateTo = $lastYear->copy()->endOfYear()->toDateString();
            $label = 'Last Year (' . $lastYear->format('Y') . ')';
        } elseif ($mode === 'single' && !empty($dateFrom)) {
            $dateFrom = Carbon::parse($dateFrom)->toDateString();
            $dateTo = $dateFrom;
            $label = Carbon::parse($dateFrom)->format('D, d M, Y');
        } elseif ($mode === 'range' && (!empty($dateFrom) || !empty($dateTo))) {
            $fromFormatted = $dateFrom ? Carbon::parse($dateFrom)->format('d M, Y') : 'Start';
            $toFormatted = $dateTo ? Carbon::parse($dateTo)->format('d M, Y') : 'End';
            $label = "{$fromFormatted} – {$toFormatted}";
        } else {
            // Default: Today
            $label = $today->format('D, d M, Y');
            $dateFrom = null;
            $dateTo = null;
        }

        $isFiltered = ($dateFrom !== null || $dateTo !== null || $preset !== null || session()->has('date_from'));

        return [
            'date_from'    => $dateFrom,
            'date_to'      => $dateTo,
            'label'        => $label,
            'mode'         => $mode,
            'preset'       => $preset,
            'working_date' => $workingDate,
            'is_filtered'  => $isFiltered,
        ];
    }

    public function index()
    {
        $filter = self::resolveDateFilter();
        $dateFrom = $filter['date_from'];
        $dateTo = $filter['date_to'];
        $workingDate = $filter['working_date'];
        $isCustomDate = $filter['is_filtered'];
        $dateLabel = $filter['label'];

        // Filtered Queries
        $invoiceQuery = Invoice::query();
        $productionQuery = ProductionLog::query();

        if ($dateFrom) {
            $invoiceQuery->whereDate('invoice_date', '>=', $dateFrom);
            $productionQuery->whereDate('production_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $invoiceQuery->whereDate('invoice_date', '<=', $dateTo);
            $productionQuery->whereDate('production_date', '<=', $dateTo);
        }

        $stats = [
            'customers'               => Customer::count(),
            'suppliers'               => Supplier::count(),
            'products'                => Product::count(),
            'product_stock_total'     => Product::sum('stock_quantity'),
            'raw_materials'           => Material::where('type', 'Raw Material')->sum('stock_quantity'),
            'final_products'          => Material::where('type', 'Final Product')->sum('stock_quantity'),
            'purchase_orders_pending' => PurchaseOrder::where('status', 'Pending')->count(),

            // Filter-aware metrics
            'invoices'                => (clone $invoiceQuery)->count(),
            'revenue'                 => (clone $invoiceQuery)->sum('grand_total'),
            'paid'                    => (clone $invoiceQuery)->where('status', 'Paid')->sum('grand_total'),
            'unpaid'                  => (clone $invoiceQuery)->whereIn('status', ['Unpaid', 'Partial'])
                                            ->selectRaw('SUM(grand_total - paid_amount) as total')
                                            ->value('total') ?? 0,
            'productions'             => $productionQuery->count(),
        ];

        // Recent Invoices: scoped to the date filter
        $recentInvoicesQuery = (clone $invoiceQuery)->with('customer')->latest('invoice_date')->latest('id');
        $recentInvoices = $recentInvoicesQuery->take(10)->get();

        if ($recentInvoices->isEmpty() && !$isCustomDate) {
            $recentInvoices = Invoice::with('customer')->latest('invoice_date')->latest('id')->take(8)->get();
        }

        $lowStock = Material::where('stock_quantity', '<=', 50)
            ->orderBy('stock_quantity')
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'recentInvoices', 'lowStock', 'workingDate', 'isCustomDate', 'filter', 'dateLabel'));
    }

    public function setWorkingDate(Request $request)
    {
        $type = $request->input('type', 'single'); // 'preset', 'single', 'range'

        if ($type === 'preset') {
            $preset = $request->input('preset');
            session([
                'date_filter_mode'   => 'preset',
                'date_filter_preset' => $preset,
                'date_from'          => null,
                'date_to'            => null,
            ]);

            // Set working date according to preset
            $today = Carbon::today();
            if ($preset === 'yesterday') {
                session(['working_date' => Carbon::yesterday()->toDateString()]);
            } else {
                session(['working_date' => $today->toDateString()]);
            }
        } elseif ($type === 'single') {
            $request->validate([
                'single_date' => 'required|date',
            ]);
            $date = Carbon::parse($request->single_date)->toDateString();
            session([
                'date_filter_mode'   => 'single',
                'date_filter_preset' => null,
                'date_from'          => $date,
                'date_to'            => $date,
                'working_date'       => $date,
            ]);
        } elseif ($type === 'range') {
            $request->validate([
                'date_from' => 'nullable|date',
                'date_to'   => 'nullable|date',
            ]);
            $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from)->toDateString() : null;
            $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to)->toDateString() : null;

            session([
                'date_filter_mode'   => 'range',
                'date_filter_preset' => null,
                'date_from'          => $dateFrom,
                'date_to'            => $dateTo,
                'working_date'       => $dateTo ?: ($dateFrom ?: now()->toDateString()),
            ]);
        }

        $filter = self::resolveDateFilter();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'label'        => $filter['label'],
                'working_date' => $filter['working_date'],
            ]);
        }

        return back()->with('success', 'Filter applied: ' . $filter['label']);
    }

    public function resetWorkingDate(Request $request)
    {
        session()->forget(['date_filter_mode', 'date_filter_preset', 'date_from', 'date_to', 'working_date']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'working_date' => now()->toDateString(),
                'label'        => now()->format('D, d M, Y'),
                'is_today'     => true,
            ]);
        }

        return back()->with('success', 'Date filter reset to Today.');
    }
}
