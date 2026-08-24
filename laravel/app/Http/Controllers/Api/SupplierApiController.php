<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierApiController extends Controller
{
    public function index()
    {
        return SupplierResource::collection(Supplier::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:30',
            'email'   => 'nullable|string|max:255',
            'gstin'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);
        return new SupplierResource(Supplier::create($validated));
    }

    public function show(Supplier $supplier)
    {
        return new SupplierResource($supplier);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:30',
            'email'   => 'nullable|string|max:255',
            'gstin'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);
        $supplier->update($validated);
        return new SupplierResource($supplier);
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchaseOrders()->exists() || $supplier->ledgers()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete supplier because existing purchase orders or ledger records are linked to this supplier.',
            ], 422);
        }

        $supplier->delete();
        return response()->json(['success' => true, 'message' => 'Supplier deleted.']);
    }
}
