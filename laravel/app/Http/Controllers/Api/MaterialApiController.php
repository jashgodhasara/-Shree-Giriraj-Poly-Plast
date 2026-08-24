<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaterialResource;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialApiController extends Controller
{
    public function index()
    {
        return MaterialResource::collection(Material::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'            => 'required|in:Raw Material,Additive,Final Product',
            'name'            => 'required|string|max:255',
            'unit'            => 'nullable|string|max:10',
            'grade_variation' => 'nullable|string|max:100',
            'temp'            => 'nullable|string|max:50',
            'size'            => 'nullable|string|max:50',
            'stock_quantity'  => 'nullable|numeric|min:0',
        ]);
        return new MaterialResource(Material::create($validated));
    }

    public function show(Material $material)
    {
        return new MaterialResource($material);
    }

    public function update(Request $request, Material $material)
    {
        $validated = $request->validate([
            'type'            => 'required|in:Raw Material,Additive,Final Product',
            'name'            => 'required|string|max:255',
            'unit'            => 'nullable|string|max:10',
            'grade_variation' => 'nullable|string|max:100',
            'temp'            => 'nullable|string|max:50',
            'size'            => 'nullable|string|max:50',
            'stock_quantity'  => 'nullable|numeric|min:0',
        ]);
        $material->update($validated);
        return new MaterialResource($material);
    }

    public function destroy(Material $material)
    {
        if ($material->transactions()->exists() || $material->productionLogs()->exists() || $material->purchaseOrders()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete raw material because existing stock transactions, purchase orders, or production logs are linked to it.',
            ], 422);
        }

        $material->delete();
        return response()->json(['success' => true, 'message' => 'Material deleted.']);
    }
}
