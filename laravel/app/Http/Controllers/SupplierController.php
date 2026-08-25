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
            'name'     => 'required|string|max:255',
            'phone'    => 'nullable|string|max:20',
            'email'    => 'nullable|email|max:255',
            'gstin'    => 'nullable|string|max:15',
            'address'  => 'nullable|string',
            'city'     => 'nullable|string|max:100',
            'state'    => 'nullable|string|max:50',
            'country'  => 'nullable|string|max:100',
            'pincode'  => 'nullable|string|max:20',
            'tax_type' => 'nullable|string|max:50',
        ]);

        if (empty($validated['country'])) {
            $validated['country'] = 'India';
        }
        if (empty($validated['state']) && !empty($validated['gstin'])) {
            $validated['state'] = \App\Services\GstTaxCalculationService::getStateFromGstin($validated['gstin']) ?: 'Gujarat';
        }

        Supplier::create($validated);

        return response()->json(['success' => true, 'message' => 'Supplier added successfully.']);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'nullable|string|max:20',
            'email'    => 'nullable|email|max:255',
            'gstin'    => 'nullable|string|max:15',
            'address'  => 'nullable|string',
            'city'     => 'nullable|string|max:100',
            'state'    => 'nullable|string|max:50',
            'country'  => 'nullable|string|max:100',
            'pincode'  => 'nullable|string|max:20',
            'tax_type' => 'nullable|string|max:50',
        ]);

        if (empty($validated['country'])) {
            $validated['country'] = 'India';
        }
        if (empty($validated['state']) && !empty($validated['gstin'])) {
            $validated['state'] = \App\Services\GstTaxCalculationService::getStateFromGstin($validated['gstin']) ?: $supplier->state;
        }

        $supplier->update($validated);

        return response()->json(['success' => true, 'message' => 'Supplier updated successfully.']);
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
