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
            'unit'            => 'nullable|string|max:10',
            'grade_variation' => 'nullable|string|max:100',
            'temp'            => 'nullable|string|max:50',
            'size'            => 'nullable|string|max:50',
            'stock_quantity'  => 'nullable|numeric|min:0',
        ]);

        Material::create($validated);

        return response()->json(['success' => true, 'message' => 'Material added successfully.']);
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

        return response()->json(['success' => true, 'message' => 'Material updated successfully.']);
    }

    public function destroy(Material $material)
    {
        $material->delete();
        return response()->json(['success' => true, 'message' => 'Material deleted.']);
    }
}
