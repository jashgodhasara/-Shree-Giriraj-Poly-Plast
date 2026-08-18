<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\ProductionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionApiController extends Controller
{
    public function index()
    {
        $logs = ProductionLog::with(['rawMaterial', 'additive', 'finalProduct'])
            ->latest()
            ->get()
            ->map(fn($log) => [
                'id'                    => $log->id,
                'date'                  => $log->date?->format('Y-m-d') ?? ($log->created_at?->format('Y-m-d') ?? now()->format('Y-m-d')),
                'raw_material'          => $log->rawMaterial?->name,
                'raw_material_used_kg'  => (float) $log->raw_material_used_kg,
                'additive'              => $log->additive?->name,
                'additive_used_kg'      => $log->additive_used_kg ? (float) $log->additive_used_kg : null,
                'final_product'         => $log->finalProduct?->name,
                'final_product_qty_pcs' => $log->final_product_qty_pcs,
                'salvage_qty_kg'        => (float) $log->salvage_qty_kg,
                'notes'                 => $log->notes,
            ]);

        return response()->json($logs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date'                  => 'required|date',
            'raw_material_id'       => 'required|exists:materials,id',
            'raw_material_used_kg'  => 'required|numeric|min:0.01',
            'additive_id'           => 'nullable|exists:materials,id',
            'additive_used_kg'      => 'nullable|numeric|min:0',
            'final_product_id'      => 'required|exists:materials,id',
            'final_product_qty_pcs' => 'required|integer|min:1',
            'salvage_qty_kg'        => 'nullable|numeric|min:0',
            'notes'                 => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $log = ProductionLog::create($validated);

            Material::where('id', $validated['raw_material_id'])
                ->decrement('stock_quantity', $validated['raw_material_used_kg']);

            if (!empty($validated['additive_id']) && !empty($validated['additive_used_kg'])) {
                Material::where('id', $validated['additive_id'])
                    ->decrement('stock_quantity', $validated['additive_used_kg']);
            }

            Material::where('id', $validated['final_product_id'])
                ->increment('stock_quantity', $validated['final_product_qty_pcs']);

            DB::commit();
            return response()->json(['success' => true, 'id' => $log->id], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(ProductionLog $productionLog)
    {
        DB::beginTransaction();
        try {
            // Reverse all stock changes from this log
            Material::where('id', $productionLog->raw_material_id)
                ->increment('stock_quantity', $productionLog->raw_material_used_kg);

            if ($productionLog->additive_id && $productionLog->additive_used_kg) {
                Material::where('id', $productionLog->additive_id)
                    ->increment('stock_quantity', $productionLog->additive_used_kg);
            }

            Material::where('id', $productionLog->final_product_id)
                ->decrement('stock_quantity', $productionLog->final_product_qty_pcs);

            $productionLog->delete();
            DB::commit();
            return response()->json(['message' => 'Log deleted and stock reversed.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
