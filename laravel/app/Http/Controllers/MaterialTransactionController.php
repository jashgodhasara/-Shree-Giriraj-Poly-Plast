<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialTransaction;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialTransactionController extends Controller
{
    public function index()
    {
        $transactions = MaterialTransaction::with(['material', 'supplier'])
            ->latest()
            ->paginate(50);

        $materials = Material::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('material-transactions.index', compact('transactions', 'materials', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id'      => 'required|exists:materials,id',
            'type'             => 'required|in:IN,OUT',
            'quantity'         => 'required|numeric|min:0.01',
            'rate'             => 'nullable|numeric|min:0',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'transaction_date' => 'required|date',
            'reference_no'     => 'nullable|string|max:100',
            'vehicle_no'       => 'nullable|string|max:50',
            'remarks'          => 'nullable|string',
        ]);

        $validated['total_amount'] = isset($validated['rate'])
            ? $validated['quantity'] * $validated['rate']
            : null;

        $material = Material::findOrFail($validated['material_id']);

        if ($validated['type'] === 'OUT' && $material->stock_quantity < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient stock for '{$material->name}'. Available: {$material->stock_quantity} {$material->unit}, Requested: {$validated['quantity']} {$material->unit}"
            ], 422);
        }

        DB::beginTransaction();
        try {
            MaterialTransaction::create($validated);

            // Update stock
            if ($validated['type'] === 'IN') {
                $material->increment('stock_quantity', $validated['quantity']);

                // If supplier is linked and rate exists, post Supplier Credit in Ledger
                if (!empty($validated['supplier_id']) && !empty($validated['total_amount'])) {
                    \App\Models\Ledger::create([
                        'entity_type'      => 'Supplier',
                        'entity_id'        => $validated['supplier_id'],
                        'transaction_date' => $validated['transaction_date'],
                        'type'             => 'Credit',
                        'amount'           => $validated['total_amount'],
                        'description'      => 'Material Inward: ' . $material->name . ' (' . $validated['quantity'] . ' ' . $material->unit . ')',
                    ]);
                }
            } else {
                $material->decrement('stock_quantity', $validated['quantity']);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaction recorded successfully.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(MaterialTransaction $materialTransaction)
    {
        DB::beginTransaction();
        try {
            // Reverse the stock change
            if ($materialTransaction->type === 'IN') {
                Material::where('id', $materialTransaction->material_id)
                    ->decrement('stock_quantity', $materialTransaction->quantity);
            } else {
                Material::where('id', $materialTransaction->material_id)
                    ->increment('stock_quantity', $materialTransaction->quantity);
            }
            $materialTransaction->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaction deleted.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
