<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::latest()->get();
        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'gstin'   => 'nullable|string|max:15',
            'address' => 'nullable|string',
        ]);

        Supplier::create($validated);

        return response()->json(['success' => true, 'message' => 'Supplier added successfully.']);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'gstin'   => 'nullable|string|max:15',
            'address' => 'nullable|string',
        ]);

        $supplier->update($validated);

        return response()->json(['success' => true, 'message' => 'Supplier updated successfully.']);
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return response()->json(['success' => true, 'message' => 'Supplier deleted.']);
    }
}
