<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockLedger;
use App\Models\StockTransfer;
use App\Services\InventoryService;
use App\Services\InventoryValuationService;
use App\Services\StockLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryApiController extends Controller
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

    public function dashboard()
    {
        $totalProducts = Product::count();
        $totalStockQty = (float) Product::sum('stock_quantity');
        $totalValuation = (float) Product::select(DB::raw('SUM(stock_quantity * CASE WHEN average_cost > 0 THEN average_cost WHEN purchase_rate > 0 THEN purchase_rate ELSE price END) as total_val'))->value('total_val');
        $lowStockCount = Product::lowStock()->count();
        $outOfStockCount = Product::outOfStock()->count();

        $today = now()->toDateString();
        $todayIn = (float) StockLedger::whereDate('transaction_date', $today)->sum('quantity_in');
        $todayOut = (float) StockLedger::whereDate('transaction_date', $today)->sum('quantity_out');

        return response()->json([
            'total_products'     => $totalProducts,
            'total_stock_qty'    => $totalStockQty,
            'total_valuation'    => round($totalValuation, 2),
            'low_stock_count'    => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'today_stock_in'     => $todayIn,
            'today_stock_out'    => $todayOut,
        ]);
    }

    public function stockLedger(Request $request, ?Product $product = null)
    {
        $filters = [
            'product_id'       => $product ? $product->id : $request->get('product_id'),
            'warehouse_id'     => $request->get('warehouse_id'),
            'transaction_type' => $request->get('transaction_type'),
            'reference_number' => $request->get('reference_number'),
            'date_from'        => $request->get('date_from'),
            'date_to'          => $request->get('date_to'),
        ];

        return response()->json($this->stockLedgerService->getPaginated($filters, $request->get('per_page', 50)));
    }

    public function lowStock()
    {
        return response()->json($this->valuationService->getLowStockAlerts());
    }

    public function valuation()
    {
        return response()->json($this->valuationService->getValuationReport());
    }

    public function adjustments(Request $request)
    {
        return response()->json(StockAdjustment::with('product', 'warehouse')->latest('id')->paginate(50));
    }

    public function storeAdjustment(Request $request)
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
            return response()->json(['error' => 'System stock and physical stock are already equal.'], 422);
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

        return response()->json(['success' => true, 'adjustment' => $adj], 201);
    }
}
