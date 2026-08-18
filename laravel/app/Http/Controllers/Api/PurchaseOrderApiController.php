<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderApiController extends Controller
{
    public function index()
    {
        $orders = PurchaseOrder::with('supplier:id,name')
            ->latest()
            ->get()
            ->map(fn($po) => [
                'id'                     => $po->id,
                'po_number'              => $po->po_number,
                'po_date'                => $po->po_date?->format('Y-m-d'),
                'expected_delivery_date' => $po->expected_delivery_date?->format('Y-m-d'),
                'supplier_id'            => $po->supplier_id,
                'supplier_name'          => $po->supplier?->name,
                'grand_total'            => (float) $po->grand_total,
                'status'                 => $po->status,
                'payment_terms'          => $po->payment_terms,
            ]);

        return response()->json($orders);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('supplier', 'items.material');

        return response()->json([
            'id'                     => $purchaseOrder->id,
            'po_number'              => $purchaseOrder->po_number,
            'po_date'                => $purchaseOrder->po_date?->format('Y-m-d'),
            'expected_delivery_date' => $purchaseOrder->expected_delivery_date?->format('Y-m-d'),
            'payment_terms'          => $purchaseOrder->payment_terms,
            'delivery_address'       => $purchaseOrder->delivery_address,
            'subtotal'               => (float) $purchaseOrder->subtotal,
            'cgst'                   => (float) $purchaseOrder->cgst,
            'sgst'                   => (float) $purchaseOrder->sgst,
            'igst'                   => (float) $purchaseOrder->igst,
            'grand_total'            => (float) $purchaseOrder->grand_total,
            'status'                 => $purchaseOrder->status,
            'notes'                  => $purchaseOrder->notes,
            'supplier'               => $purchaseOrder->supplier,
            'items'                  => $purchaseOrder->items->map(fn($item) => [
                'id'           => $item->id,
                'material_id'  => $item->material_id,
                'material_name'=> $item->material?->name,
                'unit'         => $item->material?->unit,
                'quantity'     => (float) $item->quantity,
                'unit_price'   => (float) $item->unit_price,
                'total_price'  => (float) $item->total_price,
                'received_qty' => (float) ($item->received_qty ?? 0),
            ]),
        ]);
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
            $subtotal = 0;
            $itemsData = [];
            foreach ($request->items as $item) {
                $lineTotal   = $item['quantity'] * $item['unit_price'];
                $subtotal   += $lineTotal;
                $itemsData[] = [
                    'material_id' => $item['material_id'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'total_price' => $lineTotal,
                ];
            }

            $po = PurchaseOrder::create([
                'po_number'              => PurchaseOrder::generatePoNumber(),
                'supplier_id'            => $request->supplier_id,
                'po_date'                => $request->po_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'payment_terms'          => $request->payment_terms,
                'delivery_address'       => $request->delivery_address,
                'notes'                  => $request->notes,
                'subtotal'               => $subtotal,
                'cgst'                   => 0,
                'sgst'                   => 0,
                'igst'                   => 0,
                'grand_total'            => $subtotal,
                'status'                 => 'Pending',
            ]);

            foreach ($itemsData as $item) {
                $po->items()->create($item);
            }

            DB::commit();
            return response()->json([
                'success'   => true,
                'id'        => $po->id,
                'po_number' => $po->po_number,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function markReceived(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'Received') {
            return response()->json(['success' => false, 'message' => 'Purchase Order is already marked as received.']);
        }

        DB::beginTransaction();
        try {
            $purchaseOrder->load('items.material');

            // 1. Update PO Status
            $purchaseOrder->update(['status' => 'Received']);

            // 2. Increase Material stock for each line item
            foreach ($purchaseOrder->items as $item) {
                if ($item->material) {
                    $item->material->increment('stock_quantity', $item->quantity);
                }
            }

            // 3. Post Credit Entry in Ledgers for Supplier
            \App\Models\Ledger::create([
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
                'status'  => 'Received',
                'message' => 'Stock updated & Supplier Credit posted successfully for PO #' . $purchaseOrder->po_number,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
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

                \App\Models\Ledger::where('entity_type', 'Supplier')
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
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}

