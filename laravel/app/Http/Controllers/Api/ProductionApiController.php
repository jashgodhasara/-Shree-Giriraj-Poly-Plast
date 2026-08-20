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
        return response()->json(
            ProductionLog::with(['rawMaterial', 'additive', 'finalProduct'])
                ->latest('date')->latest('id')
                ->get()
                ->map(fn($log) => [
                    'id'                    => $log->id,
                    'date'                  => $log->date?->format('Y-m-d'),
                    'raw_material'          => $log->rawMaterial?->name,
                    'raw_material_id'       => $log->raw_material_id,
                    'raw_material_used_kg'  => (float) $log->raw_material_used_kg,
                    'additive'              => $log->additive?->name,
                    'additive_id'           => $log->additive_id,
                    'additive_used_kg'      => $log->additive_used_kg ? (float) $log->additive_used_kg : null,
                    'final_product'         => $log->finalProduct?->name,
                    'final_product_id'      => $log->final_product_id,
                    'final_product_qty_pcs' => (int) $log->final_product_qty_pcs,
                    'salvage_pct'           => (float) ($log->salvage_pct ?? 2),
                    'salvage_qty_kg'        => (float) $log->salvage_qty_kg,
                    'effective_yield_kg'    => $log->effective_yield_kg ? (float) $log->effective_yield_kg : null,
                    'notes'                 => $log->notes,
                ])
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'                  => 'required|date',
            'raw_material_id'       => 'required|exists:materials,id',
            'raw_material_used_kg'  => 'required|numeric|min:0.01',
            'additive_id'           => 'nullable|exists:materials,id',
            'additive_used_kg'      => 'nullable|numeric|min:0',
            'final_product_id'      => 'required|exists:materials,id',
            'final_product_qty_pcs' => 'required|integer|min:1',
            'salvage_pct'           => 'nullable|numeric|min:0|max:100',
            'notes'                 => 'nullable|string',
        ]);

        $rawKg      = (float) $request->raw_material_used_kg;
        $salvagePct = max(0, (float) ($request->salvage_pct ?? 2));
        $salvageKg  = ProductionLog::calcSalvageKg($rawKg, $salvagePct);
        $yieldKg    = ProductionLog::calcYieldKg($rawKg, $salvageKg);

        DB::beginTransaction();
        try {
            $log = ProductionLog::create([
                'date'                  => $request->date,
                'raw_material_id'       => $request->raw_material_id,
                'raw_material_used_kg'  => $rawKg,
                'additive_id'           => $request->additive_id,
                'additive_used_kg'      => $request->additive_used_kg,
                'final_product_id'      => $request->final_product_id,
                'final_product_qty_pcs' => (int) $request->final_product_qty_pcs,
                'salvage_pct'           => $salvagePct,
                'salvage_qty_kg'        => $salvageKg,
                'effective_yield_kg'    => $yieldKg,
                'notes'                 => $request->notes,
            ]);

            Material::where('id', $request->raw_material_id)
                ->decrement('stock_quantity', $rawKg);

            if ($request->additive_id && $request->additive_used_kg) {
                Material::where('id', $request->additive_id)
                    ->decrement('stock_quantity', $request->additive_used_kg);
            }

            Material::where('id', $request->final_product_id)
                ->increment('stock_quantity', (int) $request->final_product_qty_pcs);

            DB::commit();
            return response()->json([
                'success'     => true,
                'id'          => $log->id,
                'salvage_pct' => $salvagePct,
                'salvage_kg'  => $salvageKg,
                'yield_kg'    => $yieldKg,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(ProductionLog $productionLog)
    {
        DB::beginTransaction();
        try {
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
            return response()->json(['message' => 'Production log deleted and stock reversed.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
