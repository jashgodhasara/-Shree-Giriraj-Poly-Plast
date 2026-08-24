<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = Material::latest()->get();
        $polymerRates = app(\App\Services\PlasticPricingService::class)->getPrices();
        $rateItems = $polymerRates['items'] ?? [];

        $totalStockKg = 0;
        $totalStockValue = 0;

        foreach ($materials as $m) {
            $rate = $this->matchLiveRate($m->name, $rateItems);
            $m->live_rate = $rate;
            $qty = (float)$m->stock_quantity;
            $val = $qty * $rate;
            $m->stock_value = $val;

            $totalStockKg += $qty;
            $totalStockValue += $val;
        }

        return view('materials.index', compact('materials', 'polymerRates', 'totalStockKg', 'totalStockValue'));
    }

    private function matchLiveRate(string $name, array $items): float
    {
        $norm = strtolower(trim($name));
        foreach ($items as $item) {
            $iName = strtolower(trim($item['material_name'] ?? ''));
            if ($norm === $iName || str_contains($norm, $iName) || str_contains($iName, $norm)) {
                return (float)($item['current_price'] ?? 0);
            }
        }
        if (str_contains($norm, 'homopolymer') || str_contains($norm, 'raffia')) return 96.50;
        if (str_contains($norm, 'copolymer') || str_contains($norm, 'injection')) return 104.25;
        if (str_contains($norm, 'hdpe') || str_contains($norm, 'blow')) return 99.80;
        if (str_contains($norm, 'ldpe')) return 112.40;
        if (str_contains($norm, 'lldpe')) return 98.60;
        if (str_contains($norm, 'pvc') || str_contains($norm, 'k67') || str_contains($norm, 'k-67')) return 78.50;
        if (str_contains($norm, 'pet') || str_contains($norm, 'bottle')) return 88.20;
        if (str_contains($norm, 'masterbatch') || str_contains($norm, 'white')) return 145.00;

        return 0.0;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'            => 'required|in:Raw Material,Additive,Final Product',
            'name'            => 'required|string|max:255',
            'unit'            => 'nullable|string|max:20',
            'secondary_unit'  => 'nullable|string|max:20',
            'kg_per_pcs'      => 'nullable|numeric|min:0.0001',
            'grade_variation' => 'nullable|string|max:100',
            'temp'            => 'nullable|string|max:50',
            'size'            => 'nullable|string|max:50',
            'stock_quantity'  => 'nullable|numeric|min:0',
            'stock_kg'        => 'nullable|numeric|min:0',
            'stock_pcs'       => 'nullable|numeric|min:0',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:4096',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/materials'), $filename);
            $validated['image'] = 'uploads/materials/' . $filename;
        }

        // Keep stock_quantity in sync with stock_kg
        if (!isset($validated['stock_quantity']) || $validated['stock_quantity'] == 0) {
            $validated['stock_quantity'] = $validated['stock_kg'] ?? 0;
        }

        $material = Material::create($validated);

        // Auto-sync Final Product to Products table for Billing / POS
        if ($validated['type'] === 'Final Product') {
            \App\Models\Product::updateOrCreate(
                ['name' => $validated['name']],
                [
                    'price' => 0,
                    'gst_rate' => 18,
                    'stock_quantity' => $validated['stock_quantity'] ?? 0,
                    'image' => $validated['image'] ?? null,
                    'description' => 'Final Product (' . ($validated['unit'] ?? '') . ')'
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Material added successfully.']);
    }

    public function update(Request $request, Material $material)
    {
        $validated = $request->validate([
            'type'            => 'required|in:Raw Material,Additive,Final Product',
            'name'            => 'required|string|max:255',
            'unit'            => 'nullable|string|max:20',
            'secondary_unit'  => 'nullable|string|max:20',
            'kg_per_pcs'      => 'nullable|numeric|min:0.0001',
            'grade_variation' => 'nullable|string|max:100',
            'temp'            => 'nullable|string|max:50',
            'size'            => 'nullable|string|max:50',
            'stock_quantity'  => 'nullable|numeric|min:0',
            'stock_kg'        => 'nullable|numeric|min:0',
            'stock_pcs'       => 'nullable|numeric|min:0',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:4096',
        ]);

        if ($request->hasFile('image')) {
            if ($material->image && file_exists(public_path($material->image))) {
                @unlink(public_path($material->image));
            }
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/materials'), $filename);
            $validated['image'] = 'uploads/materials/' . $filename;
        } elseif ($request->boolean('remove_image')) {
            if ($material->image && file_exists(public_path($material->image))) {
                @unlink(public_path($material->image));
            }
            $validated['image'] = null;
        }

        // Keep legacy stock_quantity in sync with stock_kg
        if (isset($validated['stock_kg'])) {
            $validated['stock_quantity'] = $validated['stock_kg'];
        }

        $material->update($validated);

        // Auto-sync Final Product to Products table for Billing / POS
        if ($validated['type'] === 'Final Product') {
            \App\Models\Product::updateOrCreate(
                ['name' => $validated['name']],
                [
                    'stock_quantity' => $validated['stock_quantity'] ?? 0,
                    'image' => $validated['image'] ?? $material->image,
                    'description' => 'Final Product (' . ($validated['unit'] ?? '') . ')'
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Material updated successfully.']);
    }

    public function destroy(Material $material)
    {
        if ($material->transactions()->exists() || $material->productionLogs()->exists() || $material->purchaseOrders()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete raw material because existing stock transactions, purchase orders, or production logs are linked to it.',
            ], 422);
        }

        if ($material->image && file_exists(public_path($material->image))) {
            @unlink(public_path($material->image));
        }
        $material->delete();
        return response()->json(['success' => true, 'message' => 'Material deleted.']);
    }

    public function syncFromApi(\App\Services\PlasticPricingService $pricingService)
    {
        $data = $pricingService->getPrices(true);
        if (empty($data['items'])) {
            return response()->json(['success' => false, 'message' => 'No materials returned from API.'], 422);
        }

        $created = 0;
        foreach ($data['items'] as $item) {
            $name = trim($item['material_name']);
            $category = $item['category'] ?? 'Raw Material';
            $unit = $item['unit'] ?? 'Kg';

            $type = 'Raw Material';
            if (stripos($category, 'Additive') !== false || stripos($name, 'Masterbatch') !== false) {
                $type = 'Additive';
            }

            $exists = Material::where('name', $name)->exists();
            if (!$exists) {
                Material::create([
                    'type'            => $type,
                    'name'            => $name,
                    'unit'            => $unit,
                    'grade_variation' => $category,
                    'stock_quantity'  => 0,
                    'stock_kg'        => 0,
                    'stock_pcs'       => 0,
                ]);
                $created++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully synchronized materials from live API. {$created} new materials added.",
            'created' => $created,
        ]);
    }
}
