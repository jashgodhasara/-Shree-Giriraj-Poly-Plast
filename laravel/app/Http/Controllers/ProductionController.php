<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\ProductionLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionController extends Controller
{
    private function resolveDates(?string $preset, ?string $dateFrom, ?string $dateTo): array
    {
        $today = Carbon::today();

        if ($preset && $preset !== 'custom') {
            return match ($preset) {
                'today'        => [$today->toDateString(), $today->toDateString()],
                'yesterday'    => [Carbon::yesterday()->toDateString(), Carbon::yesterday()->toDateString()],
                'this_month'   => [$today->copy()->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()],
                'last_month'   => [$today->copy()->subMonth()->startOfMonth()->toDateString(), $today->copy()->subMonth()->endOfMonth()->toDateString()],
                'last_3months' => [$today->copy()->subMonths(3)->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()],
                'this_year'    => [$today->copy()->startOfYear()->toDateString(), $today->copy()->endOfYear()->toDateString()],
                'last_year'    => [$today->copy()->subYear()->startOfYear()->toDateString(), $today->copy()->subYear()->endOfYear()->toDateString()],
                default        => [$dateFrom ? Carbon::parse($dateFrom)->toDateString() : '', $dateTo ? Carbon::parse($dateTo)->toDateString() : ''],
            };
        }

        $from = $dateFrom ? Carbon::parse($dateFrom)->toDateString() : '';
        $to   = $dateTo ? Carbon::parse($dateTo)->toDateString() : '';

        return [$from, $to];
    }

    public function index(Request $request)
    {
        $preset   = $request->get('preset', '');
        $dateFrom = $request->get('date_from', '');
        $dateTo   = $request->get('date_to', '');
        [$dateFrom, $dateTo] = $this->resolveDates($preset, $dateFrom, $dateTo);

        $query = ProductionLog::with(['rawMaterial', 'additive', 'finalProduct'])->latest('date')->latest('id');
        if ($dateFrom) $query->whereDate('date', '>=', $dateFrom);
        if ($dateTo)   $query->whereDate('date', '<=', $dateTo);
        $logs = $query->get();

        // Summary
        $totalRawKg   = $logs->sum('raw_material_used_kg');
        $totalPieces  = $logs->sum('final_product_qty_pcs');
        $totalSalvage = $logs->sum('salvage_qty_kg');
        $totalCount   = $logs->count();

        $rawMaterials  = Material::where('type', 'Raw Material')->orderBy('name')->get();
        $additives     = Material::where('type', 'Additive')->orderBy('name')->get();
        $finalProducts = Material::where('type', 'Final Product')->orderBy('name')->get();

        return view('production.index', compact(
            'logs', 'rawMaterials', 'additives', 'finalProducts',
            'preset', 'dateFrom', 'dateTo', 'totalRawKg', 'totalPieces', 'totalSalvage', 'totalCount'
        ));
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
            // Salvage: either enter % (2–5) OR manual Kg — % takes priority
            'salvage_pct'            => 'nullable|numeric|min:0|max:100',
            'salvage_qty_kg'         => 'nullable|numeric|min:0',
            'notes'                  => 'nullable|string',
        ]);

        $rawKg      = (float) $validated['raw_material_used_kg'];
        $salvagePct = isset($validated['salvage_pct']) && $validated['salvage_pct'] > 0
                        ? (float) $validated['salvage_pct']
                        : 2.0;   // default 2 % if not provided

        // Auto-calculate salvage Kg from %
        $salvageKg  = ProductionLog::calcSalvageKg($rawKg, $salvagePct);
        $yieldKg    = ProductionLog::calcYieldKg($rawKg, $salvageKg);

        $validated['salvage_pct']        = $salvagePct;
        $validated['salvage_qty_kg']     = $salvageKg;
        $validated['effective_yield_kg'] = $yieldKg;

        $rm = Material::findOrFail($validated['raw_material_id']);
        if ((float)$rm->stock_quantity < $rawKg) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient stock for '{$rm->name}'. Available: {$rm->stock_quantity} {$rm->unit}, Requested: {$rawKg} {$rm->unit}",
            ], 422);
        }

        if (!empty($validated['additive_id']) && !empty($validated['additive_used_kg'])) {
            $ad = Material::findOrFail($validated['additive_id']);
            if ((float)$ad->stock_quantity < (float)$validated['additive_used_kg']) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for additive '{$ad->name}'. Available: {$ad->stock_quantity} {$ad->unit}",
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $log = ProductionLog::create($validated);

            // Deduct raw material
            $rm->decrement('stock_quantity', $rawKg);

            // Deduct additive
            if (!empty($validated['additive_id']) && !empty($validated['additive_used_kg'])) {
                Material::where('id', $validated['additive_id'])
                    ->decrement('stock_quantity', $validated['additive_used_kg']);
            }

            // Increase final product stock
            Material::where('id', $validated['final_product_id'])
                ->increment('stock_quantity', $validated['final_product_qty_pcs']);

            DB::commit();

            return response()->json([
                'success'     => true,
                'message'     => 'Production log saved successfully.',
                'salvage_kg'  => $salvageKg,
                'salvage_pct' => $salvagePct,
                'yield_kg'    => $yieldKg,
            ]);
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
