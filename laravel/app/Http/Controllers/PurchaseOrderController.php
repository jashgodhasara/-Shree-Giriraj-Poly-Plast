<?php

namespace App\Http\Controllers;

use App\Models\Ledger;
use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Product;
use App\Services\InventoryService;

class PurchaseOrderController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

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
        $status   = $request->get('status', '');
        [$dateFrom, $dateTo] = $this->resolveDates($preset, $dateFrom, $dateTo);

        $query = PurchaseOrder::with('supplier', 'items.material')->latest('po_date')->latest('id');

        if ($dateFrom) $query->whereDate('po_date', '>=', $dateFrom);
        if ($dateTo)   $query->whereDate('po_date', '<=', $dateTo);
        if ($status)   $query->where('status', $status);

        $purchaseOrders = $query->paginate(50)->appends($request->query());

        return view('purchase-orders.index', compact('purchaseOrders', 'preset', 'dateFrom', 'dateTo', 'status'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $materials = Material::orderBy('name')->get();

        return view('purchase-orders.create', compact('suppliers', 'materials'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'            => 'required|exists:suppliers,id',
            'po_date'                => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'payment_terms'          => 'nullable|string|max:100',
            'delivery_address'       => 'nullable|string',
            'notes'                  => 'nullable|string',
            'items'                  => 'required|array|min:1',
            'items.*.material_id'    => 'required|exists:materials,id',
            'items.*.quantity'       => 'required|numeric|min:0.01',
            'items.*.unit_price'     => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $supplier  = Supplier::findOrFail($request->supplier_id);
            $taxRegime = \App\Services\GstTaxCalculationService::determineTaxRegime(
                $supplier->country,
                $supplier->state,
                $supplier->gstin,
                $supplier->tax_type
            );

            $subtotal  = 0;
            $cgst      = 0;
            $sgst      = 0;
            $igst      = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $material  = Material::findOrFail($item['material_id']);
                $qty       = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $gstRate   = (float) ($item['gst_rate'] ?? 18.00);
                $hsnCode   = $item['hsn_code'] ?? '';

                $lineTotal = $qty * $unitPrice;
                $gstAmt    = $lineTotal * ($gstRate / 100);

                $subtotal += $lineTotal;

                $cgst += $gstAmt * $taxRegime['cgst_split'];
                $sgst += $gstAmt * $taxRegime['sgst_split'];
                $igst += $gstAmt * $taxRegime['igst_split'];

                $itemsData[] = [
                    'material_id' => $material->id,
                    'quantity'    => $qty,
                    'unit_price'  => $unitPrice,
                    'total_price' => $lineTotal,
                    'hsn_code'    => $hsnCode,
                    'gst_rate'    => $gstRate,
                ];
            }

            $grandTotal = round($subtotal + $cgst + $sgst + $igst, 2);

            $po = PurchaseOrder::create([
                'po_number'              => PurchaseOrder::generatePoNumber(),
                'supplier_id'            => $supplier->id,
                'po_date'                => $request->po_date,
                'expected_delivery_date' => $request->expected_delivery_date ?: null,
                'payment_terms'          => $request->payment_terms ?: null,
                'delivery_address'       => $request->delivery_address ?: null,
                'subtotal'               => $subtotal,
                'cgst'                   => $cgst,
                'sgst'                   => $sgst,
                'igst'                   => $igst,
                'grand_total'            => $grandTotal,
                'status'                 => 'Pending',
                'notes'                  => $request->notes ?: null,
            ]);

            foreach ($itemsData as $iData) {
                $po->items()->create($iData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'po_id'   => $po->id,
                'message' => 'Purchase Order ' . $po->po_number . ' created successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('supplier', 'items.material');
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function print(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('supplier', 'items.material');
        return view('purchase-orders.print', compact('purchaseOrder'));
    }

    public function markReceived(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'Received') {
            return response()->json(['success' => false, 'message' => 'Purchase Order is already marked as received.']);
        }

        DB::beginTransaction();
        try {
            $purchaseOrder->load('items.material');

            // 1. Update PO Status
            $purchaseOrder->update(['status' => 'Received']);

            // 2. Increase Material & Product stock for each line item
            foreach ($purchaseOrder->items as $item) {
                if ($item->material) {
                    $item->material->increment('stock_quantity', $item->quantity);

                    // Sync/Record to matching Product if exists
                    $matchedProduct = Product::where('material_id', $item->material->id)
                        ->orWhere('name', $item->material->name)
                        ->first();

                    if ($matchedProduct) {
                        $this->inventoryService->recordPurchase(
                            $matchedProduct,
                            (float) $item->quantity,
                            (float) $item->unit_price,
                            $purchaseOrder->po_number,
                            $purchaseOrder->id,
                            now()->toDateString(),
                            "Purchase receipt from {$purchaseOrder->supplier->name}"
                        );
                    }
                }
            }

            // 3. Post Credit Entry in Ledgers for Supplier
            Ledger::create([
                'entity_type'      => 'Supplier',
                'entity_id'        => $purchaseOrder->supplier_id,
                'transaction_date' => now()->toDateString(),
                'type'             => 'Credit',
                'amount'           => $purchaseOrder->grand_total,
                'description'      => 'Purchase Bill Received #' . $purchaseOrder->po_number,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock updated & Supplier Credit posted successfully for PO #' . $purchaseOrder->po_number,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        DB::beginTransaction();
        try {
            $purchaseOrder->load('items.material');

            // If received, reverse stock addition and remove supplier credit ledger entry
            if ($purchaseOrder->status === 'Received') {
                foreach ($purchaseOrder->items as $item) {
                    if ($item->material) {
                        $item->material->decrement('stock_quantity', $item->quantity);
                    }
                }

                // Reverse Product Stock Ledgers
                $this->inventoryService->reverseByReference('PurchaseOrders', $purchaseOrder->id, 'Purchase Order Cancellation');

                Ledger::where('entity_type', 'Supplier')
                    ->where('entity_id', $purchaseOrder->supplier_id)
                    ->where('description', 'Purchase Bill Received #' . $purchaseOrder->po_number)
                    ->delete();
            }

            $purchaseOrder->items()->delete();
            $purchaseOrder->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Purchase Order deleted successfully.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
