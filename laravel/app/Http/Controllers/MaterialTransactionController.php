<?php

namespace App\Http\Controllers;

use App\Models\Ledger;
use App\Models\Material;
use App\Models\MaterialTransaction;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MaterialTransactionController extends Controller
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
                default        => [$dateFrom ?? '', $dateTo ?? ''],
            };
        }
        return [
            $dateFrom ? Carbon::parse($dateFrom)->toDateString() : '',
            $dateTo   ? Carbon::parse($dateTo)->toDateString()   : '',
        ];
    }

    public function index(Request $request)
    {
        $preset   = $request->get('preset', '');
        $dateFrom = $request->get('date_from', '');
        $dateTo   = $request->get('date_to', '');
        $type     = $request->get('type', '');
        [$dateFrom, $dateTo] = $this->resolveDates($preset, $dateFrom, $dateTo);

        $query = MaterialTransaction::with(['material', 'supplier'])
            ->latest('transaction_date')
            ->latest('id');

        if ($dateFrom) $query->whereDate('transaction_date', '>=', $dateFrom);
        if ($dateTo)   $query->whereDate('transaction_date', '<=', $dateTo);
        if ($type)     $query->where('type', $type);

        $transactions = $query->paginate(50)->appends($request->query());
        $materials    = Material::orderBy('name')->get();
        $suppliers    = Supplier::orderBy('name')->get();

        return view('material-transactions.index', compact(
            'transactions', 'materials', 'suppliers',
            'preset', 'dateFrom', 'dateTo', 'type'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id'      => 'required|exists:materials,id',
            'type'             => 'required|in:IN,OUT',
            'unit_type'        => 'nullable|in:Kg,Pcs',
            'quantity'         => 'required|numeric|min:0.001',
            'rate'             => 'nullable|numeric|min:0',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'transaction_date' => 'required|date',
            'reference_no'     => 'nullable|string|max:100',
            'vehicle_no'       => 'nullable|string|max:50',
            'remarks'          => 'nullable|string',
        ]);

        $material   = Material::findOrFail($validated['material_id']);
        $unitType   = $validated['unit_type'] ?? ($material->unit === 'Pcs' ? 'Pcs' : 'Kg');
        $validated['unit_type'] = $unitType;
        $qty        = (float) $validated['quantity'];
        $kgPerPcs   = (float) ($material->kg_per_pcs ?? 0);
        $hasDual    = $kgPerPcs > 0;

        // --- Calculate equivalent quantities ---
        $qtyKg  = null;
        $qtyPcs = null;

        if ($unitType === 'Kg') {
            $qtyKg  = $qty;
            $qtyPcs = $hasDual ? round($qty / $kgPerPcs, 2) : null;
        } else {
            // Pcs
            $qtyPcs = $qty;
            $qtyKg  = $hasDual ? round($qty * $kgPerPcs, 3) : null;
        }

        $validated['quantity_kg']  = $qtyKg;
        $validated['quantity_pcs'] = $qtyPcs;
        $validated['total_amount'] = !empty($validated['rate'])
            ? round($qty * (float) $validated['rate'], 2)
            : null;

        // --- Stock check for OUT ---
        if ($validated['type'] === 'OUT') {
            if ($unitType === 'Kg') {
                $available = (float) $material->stock_kg;
                if ($qtyKg > $available + 0.001) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient Kg stock for '{$material->name}'. Available: {$available} Kg, Requested: {$qtyKg} Kg",
                    ], 422);
                }
            } else {
                $available = (float) $material->stock_pcs;
                if ($qtyPcs > $available + 0.001) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient Pcs stock for '{$material->name}'. Available: {$available} Pcs, Requested: {$qtyPcs} Pcs",
                    ], 422);
                }
            }
        }

        DB::beginTransaction();
        try {
            MaterialTransaction::create($validated);

            // --- Update stock ---
            if ($validated['type'] === 'IN') {
                if ($unitType === 'Kg') {
                    $material->increment('stock_kg', $qtyKg);
                    $material->increment('stock_quantity', $qtyKg); // keep legacy in sync
                } else {
                    $material->increment('stock_pcs', $qtyPcs);
                    // Also deduct Kg equivalent from stock_kg if dual-unit
                    if ($hasDual && $qtyKg) {
                        $material->increment('stock_kg', $qtyKg);
                        $material->increment('stock_quantity', $qtyKg);
                    }
                }
            } else {
                // OUT
                if ($unitType === 'Kg') {
                    $material->decrement('stock_kg', $qtyKg);
                    $material->decrement('stock_quantity', $qtyKg);
                    // If dual-unit, also deduct pcs equivalent
                    if ($hasDual && $qtyPcs) {
                        $material->decrement('stock_pcs', $qtyPcs);
                    }
                } else {
                    $material->decrement('stock_pcs', $qtyPcs);
                    // Deduct Kg equivalent
                    if ($hasDual && $qtyKg) {
                        $material->decrement('stock_kg', $qtyKg);
                        $material->decrement('stock_quantity', $qtyKg);
                    }
                }
            }

            // --- Auto-post ledger for supplier purchases ---
            if ($validated['type'] === 'IN' && !empty($validated['supplier_id']) && !empty($validated['total_amount'])) {
                $unitLabel = $unitType === 'Kg' ? "{$qty} Kg" : "{$qty} Pcs";
                Ledger::create([
                    'entity_type'      => 'Supplier',
                    'entity_id'        => $validated['supplier_id'],
                    'transaction_date' => $validated['transaction_date'],
                    'type'             => 'Credit',
                    'amount'           => $validated['total_amount'],
                    'description'      => "Material Inward: {$material->name} ({$unitLabel})",
                ]);
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
            $mat      = Material::findOrFail($materialTransaction->material_id);
            $unitType = $materialTransaction->unit_type ?? 'Kg';
            $qtyKg    = (float) $materialTransaction->quantity_kg;
            $qtyPcs   = (float) $materialTransaction->quantity_pcs;
            $qty      = (float) $materialTransaction->quantity;
            $hasDual  = (float)($mat->kg_per_pcs ?? 0) > 0;

            if ($materialTransaction->type === 'IN') {
                if ($unitType === 'Kg') {
                    $mat->decrement('stock_kg', $qtyKg ?: $qty);
                    $mat->decrement('stock_quantity', $qtyKg ?: $qty);
                } else {
                    $mat->decrement('stock_pcs', $qtyPcs ?: $qty);
                    if ($hasDual && $qtyKg) {
                        $mat->decrement('stock_kg', $qtyKg);
                        $mat->decrement('stock_quantity', $qtyKg);
                    }
                }
            } else {
                if ($unitType === 'Kg') {
                    $mat->increment('stock_kg', $qtyKg ?: $qty);
                    $mat->increment('stock_quantity', $qtyKg ?: $qty);
                    if ($hasDual && $qtyPcs) $mat->increment('stock_pcs', $qtyPcs);
                } else {
                    $mat->increment('stock_pcs', $qtyPcs ?: $qty);
                    if ($hasDual && $qtyKg) {
                        $mat->increment('stock_kg', $qtyKg);
                        $mat->increment('stock_quantity', $qtyKg);
                    }
                }
            }

            $materialTransaction->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaction deleted and stock reversed.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
