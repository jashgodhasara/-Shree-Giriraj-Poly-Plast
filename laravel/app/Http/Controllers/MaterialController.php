<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = Material::latest()->get();
        return view('materials.index', compact('materials'));
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
        if ($material->image && file_exists(public_path($material->image))) {
            @unlink(public_path($material->image));
        }
        $material->delete();
        return response()->json(['success' => true, 'message' => 'Material deleted.']);
    }
}
