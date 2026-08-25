<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockAdjustment;
use App\Models\StockLedger;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\InventoryValuationService;
use App\Services\StockLedgerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    protected InventoryService $inventoryService;
    protected StockLedgerService $stockLedgerService;
    protected InventoryValuationService $valuationService;

    public function __construct(
        InventoryService $inventoryService,
        StockLedgerService $stockLedgerService,
        InventoryValuationService $valuationService
    ) {
        $this->inventoryService = $inventoryService;
        $this->stockLedgerService = $stockLedgerService;
        $this->valuationService = $valuationService;
    }

    /**
     * Complete Real-time Inventory Dashboard.
     */
    public function dashboard(Request $request)
    {
        $today = Carbon::today()->toDateString();

        $totalProducts = Product::count();
        $totalStockQty = (float) Product::sum('stock_quantity');
        
        // Total Inventory Value based on average_cost or purchase_rate
        $totalValuation = (float) Product::select(DB::raw('SUM(stock_quantity * CASE WHEN average_cost > 0 THEN average_cost WHEN purchase_rate > 0 THEN purchase_rate ELSE price END) as total_val'))
            ->value('total_val');

        $lowStockCount = Product::where('stock_quantity', '>', 0)
            ->where(function ($q) {
                $q->whereColumn('stock_quantity', '<=', 'reorder_level')
                  ->orWhereColumn('stock_quantity', '<=', 'minimum_stock');
            })->count();

        $outOfStockCount = Product::where('stock_quantity', '<=', 0)->count();
        $negativeStockCount = Product::where('stock_quantity', '<', 0)->count();

        // Today's Movements
        $todayIn = (float) StockLedger::whereDate('transaction_date', $today)->sum('quantity_in');
        $todayOut = (float) StockLedger::whereDate('transaction_date', $today)->sum('quantity_out');
        $todayVal = (float) StockLedger::whereDate('transaction_date', $today)->sum('amount');

        // Top 5 Products by Stock Qty
        $topStockProducts = Product::orderBy('stock_quantity', 'desc')->take(5)->get();

        // Low stock products alert table
        $lowStockList = $this->valuationService->getLowStockAlerts()->take(10);

        // Recent 10 Stock Ledger Movements
        $recentTransactions = StockLedger::with('product', 'warehouse', 'user')->latest('id')->take(10)->get();

        // Stock In vs Out by Month (Past 6 months)
        $monthlyMovements = StockLedger::select(
            DB::raw("strftime('%Y-%m', transaction_date) as month"),
            DB::raw('SUM(quantity_in) as total_in'),
            DB::raw('SUM(quantity_out) as total_out')
        )
        ->groupBy('month')
        ->orderBy('month', 'desc')
        ->take(6)
        ->get()
        ->reverse()
        ->values();

        // Stock value by Category
        $categoryValuations = ProductCategory::with('products')->get()->map(function ($cat) {
            $catVal = $cat->products->sum(function ($p) {
                $cost = (float) $p->average_cost > 0 ? (float) $p->average_cost : (float) $p->purchase_rate;
                return (float) $p->stock_quantity * $cost;
            });
            return [
                'name' => $cat->name,
                'value' => round($catVal, 2),
                'count' => $cat->products->count(),
            ];
        })->filter(fn($c) => $c['value'] > 0 || $c['count'] > 0)->values();

        if ($request->wantsJson()) {
            return response()->json([
                'total_products'     => $totalProducts,
                'total_stock_qty'    => $totalStockQty,
                'total_valuation'    => round($totalValuation, 2),
                'low_stock_count'    => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'today_in'           => $todayIn,
                'today_out'          => $todayOut,
                'today_value'        => round($todayVal, 2),
                'top_products'       => $topStockProducts,
                'recent_ledgers'     => $recentTransactions,
            ]);
        }

        return view('inventory.dashboard', compact(
            'totalProducts', 'totalStockQty', 'totalValuation',
            'lowStockCount', 'outOfStockCount', 'negativeStockCount',
            'todayIn', 'todayOut', 'todayVal',
            'topStockProducts', 'lowStockList', 'recentTransactions',
            'monthlyMovements', 'categoryValuations'
        ));
    }

    /**
     * Stock Ledger View.
     */
    public function ledger(Request $request)
    {
        $filters = [
            'product_id'       => $request->get('product_id'),
            'warehouse_id'     => $request->get('warehouse_id'),
            'transaction_type' => $request->get('transaction_type'),
            'reference_number' => $request->get('reference_number'),
            'date_from'        => $request->get('date_from'),
            'date_to'          => $request->get('date_to'),
        ];

        $ledgers = $this->stockLedgerService->getPaginated($filters, 50);
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        // Calculate Totals for current filtered criteria
        $query = $this->stockLedgerService->getFilteredQuery($filters);
        $totalIn = (clone $query)->sum('quantity_in');
        $totalOut = (clone $query)->sum('quantity_out');
        $totalAmount = (clone $query)->sum('amount');

        if ($request->wantsJson()) {
            return response()->json([
                'ledgers'      => $ledgers,
                'total_in'     => $totalIn,
                'total_out'    => $totalOut,
                'total_amount' => $totalAmount,
            ]);
        }

        return view('inventory.ledger', compact('ledgers', 'products', 'warehouses', 'filters', 'totalIn', 'totalOut', 'totalAmount'));
    }

    /**
     * Low Stock Alert & Purchase Suggestions Page.
     */
    public function lowStock(Request $request)
    {
        $alerts = $this->valuationService->getLowStockAlerts();
        $totalSuggestedValue = $alerts->sum('estimated_cost');

        if ($request->wantsJson()) {
            return response()->json([
                'alerts'                => $alerts,
                'total_low_stock_items' => $alerts->count(),
                'total_suggested_cost'  => $totalSuggestedValue,
            ]);
        }

        return view('inventory.low_stock', compact('alerts', 'totalSuggestedValue'));
    }

    /**
     * Inventory Valuation Report Page.
     */
    public function valuation(Request $request)
    {
        $data = $this->valuationService->getValuationReport();
        $deadStock = $this->valuationService->getDeadStockReport(60);

        if ($request->wantsJson()) {
            return response()->json(array_merge($data, ['dead_stock' => $deadStock]));
        }

        return view('inventory.valuation', compact('data', 'deadStock'));
    }

    /**
     * Stock Adjustments List.
     */
    public function adjustmentsIndex(Request $request)
    {
        $adjustments = StockAdjustment::with(['product', 'warehouse', 'creator'])->latest('id')->paginate(30);
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('inventory.adjustments', compact('adjustments', 'products', 'warehouses'));
    }

    /**
     * Create Physical Stock Adjustment.
     */
    public function adjustmentStore(Request $request)
    {
        $validated = $request->validate([
            'product_id'     => 'required|exists:products,id',
            'warehouse_id'   => 'nullable|exists:warehouses,id',
            'physical_stock' => 'required|numeric|min:0',
            'reason'         => 'required|string',
            'remarks'        => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $systemStock = (float) $product->stock_quantity;
        $physicalStock = (float) $validated['physical_stock'];
        $diff = round($physicalStock - $systemStock, 4);

        if (abs($diff) < 0.0001) {
            return response()->json(['success' => false, 'message' => 'System stock and physical stock are identical. No adjustment needed.'], 422);
        }

        $adj = DB::transaction(function () use ($product, $systemStock, $physicalStock, $diff, $validated) {
            $adjNumber = StockAdjustment::generateAdjustmentNumber();

            $record = StockAdjustment::create([
                'adjustment_number'   => $adjNumber,
                'adjustment_date'     => now()->toDateString(),
                'product_id'          => $product->id,
                'warehouse_id'        => $validated['warehouse_id'] ?? $product->warehouse_id,
                'system_stock'        => $systemStock,
                'physical_stock'      => $physicalStock,
                'difference_quantity' => $diff,
                'adjustment_type'     => $diff > 0 ? 'Increase' : 'Decrease',
                'reason'              => $validated['reason'],
                'remarks'             => $validated['remarks'] ?? null,
                'status'              => 'Applied',
                'created_by'          => auth()->id(),
            ]);

            // Record through Centralized Inventory Engine
            $this->inventoryService->recordAdjustment(
                $product,
                $systemStock,
                $physicalStock,
                $validated['reason'],
                $adjNumber,
                $record->id,
                $validated['remarks'] ?? null
            );

            return $record;
        });

        return response()->json([
            'success' => true,
            'message' => "Stock adjustment #{$adj->adjustment_number} applied successfully. New Stock: {$physicalStock}",
            'adjustment' => $adj
        ]);
    }

    /**
     * Stock Transfers List.
     */
    public function transfersIndex(Request $request)
    {
        $transfers = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'product', 'creator'])->latest('id')->paginate(30);
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('inventory.transfers', compact('transfers', 'products', 'warehouses'));
    }

    /**
     * Create Inter-Warehouse Stock Transfer.
     */
    public function transferStore(Request $request)
    {
        $validated = $request->validate([
            'product_id'        => 'required|exists:products,id',
            'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id'   => 'required|exists:warehouses,id',
            'quantity'          => 'required|numeric|min:0.01',
            'remarks'           => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $qty = (float) $validated['quantity'];

        $transfer = DB::transaction(function () use ($product, $qty, $validated) {
            $transferNumber = StockTransfer::generateTransferNumber();

            $record = StockTransfer::create([
                'transfer_number'    => $transferNumber,
                'transfer_date'      => now()->toDateString(),
                'from_warehouse_id'  => $validated['from_warehouse_id'],
                'to_warehouse_id'    => $validated['to_warehouse_id'],
                'product_id'         => $product->id,
                'quantity'           => $qty,
                'unit'               => $product->unit ?: 'PCS',
                'converted_quantity' => $qty,
                'status'             => 'Completed',
                'remarks'            => $validated['remarks'] ?? null,
                'created_by'         => auth()->id(),
            ]);

            // Execute Transfer Out & In in Stock Ledger
            $this->inventoryService->recordTransfer(
                $product,
                $validated['from_warehouse_id'],
                $validated['to_warehouse_id'],
                $qty,
                $transferNumber,
                $record->id
            );

            return $record;
        });

        return response()->json([
            'success' => true,
            'message' => "Stock Transfer #{$transfer->transfer_number} processed successfully.",
            'transfer' => $transfer
        ]);
    }

    /**
     * Warehouse Master Index.
     */
    public function warehousesIndex()
    {
        $warehouses = Warehouse::withCount(['products', 'stockLedgers'])->orderBy('name')->get();
        return view('inventory.warehouses', compact('warehouses'));
    }

    /**
     * Store Warehouse.
     */
    public function warehouseStore(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:150',
            'code'           => 'required|string|max:50|unique:warehouses,code',
            'location'       => 'nullable|string|max:150',
            'contact_person' => 'nullable|string|max:150',
            'contact_number' => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:150',
            'address'        => 'nullable|string',
            'is_primary'     => 'nullable|boolean',
            'status'         => 'nullable|in:active,inactive',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_primary'] = $request->boolean('is_primary');
        $validated['status'] = $validated['status'] ?? 'active';

        if ($validated['is_primary']) {
            Warehouse::where('is_primary', true)->update(['is_primary' => false]);
        }

        Warehouse::create($validated);

        return response()->json(['success' => true, 'message' => 'Warehouse created successfully.']);
    }

    /**
     * Update Warehouse.
     */
    public function warehouseUpdate(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:150',
            'code'           => 'required|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'location'       => 'nullable|string|max:150',
            'contact_person' => 'nullable|string|max:150',
            'contact_number' => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:150',
            'address'        => 'nullable|string',
            'is_primary'     => 'nullable|boolean',
            'status'         => 'required|in:active,inactive',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_primary'] = $request->boolean('is_primary');

        if ($validated['is_primary']) {
            Warehouse::where('id', '!=', $warehouse->id)->update(['is_primary' => false]);
        }

        $warehouse->update($validated);

        return response()->json(['success' => true, 'message' => 'Warehouse updated successfully.']);
    }
}
