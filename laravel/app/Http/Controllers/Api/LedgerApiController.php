<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ledger;
use Illuminate\Http\Request;

class LedgerApiController extends Controller
{
    public function index()
    {
        $entries = Ledger::latest()->get()->map(fn($e) => [
            'id'               => $e->id,
            'entity_type'      => $e->entity_type,
            'entity_id'        => $e->entity_id,
            'entity_name'      => $e->entityName(),
            'transaction_date' => $e->transaction_date?->format('Y-m-d') ?? ($e->created_at?->format('Y-m-d') ?? now()->format('Y-m-d')),
            'type'             => $e->type,
            'amount'           => (float) $e->amount,
            'hsn_code'         => $e->hsn_code,
            'csm_code'         => $e->csm_code,
            'description'      => $e->description,
        ]);

        return response()->json($entries);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entity_type'      => 'required|in:Customer,Supplier,Investor,Job Work',
            'entity_id'        => 'required|integer|min:1',
            'transaction_date' => 'required|date',
            'type'             => 'required|in:Debit,Credit',
            'amount'           => 'required|numeric|min:0.01',
            'hsn_code'         => 'nullable|string|max:50',
            'csm_code'         => 'nullable|string|max:50',
            'description'      => 'nullable|string',
        ]);

        $entry = Ledger::create($validated);
        return response()->json($entry, 201);
    }

    public function destroy(Ledger $ledger)
    {
        $ledger->delete();
        return response()->json(['message' => 'Entry deleted.']);
    }
}
