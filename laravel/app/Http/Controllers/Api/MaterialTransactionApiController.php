<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialTransactionApiController extends Controller
{
    public function index()
    {
        $txns = MaterialTransaction::with('material:id,name,unit', 'supplier:id,name')
            ->latest()
            ->get()
            ->map(fn($t) => [
                'id'               => $t->id,
                'material_id'      => $t->material_id,
                'material_name'    => $t->material?->name,
                'unit'             => $t->material?->unit,
                'type'             => $t->type,
                'quantity'         => (float) $t->quantity,
                'rate'             => $t->rate ? (float) $t->rate : null,
                'total_amount'     => $t->total_amount ? (float) $t->total_amount : null,
                'supplier_id'      => $t->supplier_id,
                'supplier_name'    => $t->supplier?->name,
                'transaction_date' => $t->transaction_date?->format('Y-m-d') ?? ($t->created_at?->format('Y-m-d') ?? now()->format('Y-m-d')),
                'reference_no'     => $t->reference_no,
                'vehicle_no'       => $t->vehicle_no,
                'remarks'          => $t->remarks,
            ]);

        return response()->json($txns);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id'      => 'required|exists:materials,id',
            'type'             => 'required|in:IN,OUT',
            'quantity'         => 'required|numeric|min:0.01',
            'rate'             => 'nullable|numeric|min:0',
            'total_amount'     => 'nullable|numeric|min:0',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'transaction_date' => 'required|date',
            'reference_no'     => 'nullable|string|max:100',
            'vehicle_no'       => 'nullable|string|max:50',
            'remarks'          => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $txn = MaterialTransaction::create($validated);

            // Update material stock
            if ($validated['type'] === 'IN') {
                Material::where('id', $validated['material_id'])
                    ->increment('stock_quantity', $validated['quantity']);
            } else {
                Material::where('id', $validated['material_id'])
                    ->decrement('stock_quantity', $validated['quantity']);
            }

            DB::commit();
            return response()->json(['success' => true, 'id' => $txn->id], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
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
            return response()->json(['message' => 'Transaction deleted.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
