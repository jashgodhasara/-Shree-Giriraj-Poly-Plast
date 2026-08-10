<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\ProductionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionController extends Controller
{
    public function index()
    {
        $logs = ProductionLog::with(['rawMaterial', 'additive', 'finalProduct'])
            ->latest()
            ->get();

        $rawMaterials  = Material::where('type', 'Raw Material')->orderBy('name')->get();
        $additives     = Material::where('type', 'Additive')->orderBy('name')->get();
        $finalProducts = Material::where('type', 'Final Product')->orderBy('name')->get();

        return view('production.index', compact('logs', 'rawMaterials', 'additives', 'finalProducts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date'                   => 'required|date',
            'raw_material_id'        => 'required|exists:materials,id',
            'raw_material_used_kg'   => 'required|numeric|min:0.01',
            'additive_id'            => 'nullable|exists:materials,id',
            'additive_used_kg'       => 'nullable|numeric|min:0',
            'final_product_id'       => 'required|exists:materials,id',
            'final_product_qty_pcs'  => 'required|integer|min:1',
            'salvage_qty_kg'         => 'nullable|numeric|min:0',
            'notes'                  => 'nullable|string',
        ]);

        $rm = Material::findOrFail($validated['raw_material_id']);
        if ($rm->stock_quantity < $validated['raw_material_used_kg']) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient stock for raw material '{$rm->name}'. Available: {$rm->stock_quantity} {$rm->unit}, Requested: {$validated['raw_material_used_kg']} {$rm->unit}"
            ], 422);
        }

        if (!empty($validated['additive_id']) && !empty($validated['additive_used_kg'])) {
            $ad = Material::findOrFail($validated['additive_id']);
            if ($ad->stock_quantity < $validated['additive_used_kg']) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for additive '{$ad->name}'. Available: {$ad->stock_quantity} {$ad->unit}, Requested: {$validated['additive_used_kg']} {$ad->unit}"
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $log = ProductionLog::create($validated);

            // Deduct raw material stock
            $rm->decrement('stock_quantity', $validated['raw_material_used_kg']);

            // Deduct additive stock if used
            if (!empty($validated['additive_id']) && !empty($validated['additive_used_kg'])) {
                Material::where('id', $validated['additive_id'])
                    ->decrement('stock_quantity', $validated['additive_used_kg']);
            }

            // Increase final product stock
            Material::where('id', $validated['final_product_id'])
                ->increment('stock_quantity', $validated['final_product_qty_pcs']);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Production log saved successfully.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(ProductionLog $productionLog)
    {
        DB::beginTransaction();
        try {
            // 1. Restore consumed raw material stock
            Material::where('id', $productionLog->raw_material_id)
                ->increment('stock_quantity', $productionLog->raw_material_used_kg);

            // 2. Restore consumed additive stock (if used)
            if ($productionLog->additive_id && $productionLog->additive_used_kg > 0) {
                Material::where('id', $productionLog->additive_id)
                    ->increment('stock_quantity', $productionLog->additive_used_kg);
            }

            // 3. Decrement produced final product stock
            Material::where('id', $productionLog->final_product_id)
                ->decrement('stock_quantity', $productionLog->final_product_qty_pcs);

            $productionLog->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Production log deleted and stock adjustments reversed.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
