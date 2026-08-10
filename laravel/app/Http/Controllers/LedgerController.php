<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Investor;
use App\Models\JobWork;
use App\Models\Ledger;
use App\Models\Supplier;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    public function index()
    {
        $entries   = Ledger::latest()->paginate(100);
        $customers = Customer::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $investors = Investor::orderBy('name')->get();
        $jobWorks  = JobWork::orderBy('party_name')->get();

        return view('ledger.index', compact('entries', 'customers', 'suppliers', 'investors', 'jobWorks'));
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

        Ledger::create($validated);

        return response()->json(['success' => true, 'message' => 'Ledger entry added successfully.']);
    }

    public function destroy(Ledger $ledger)
    {
        $ledger->delete();
        return response()->json(['success' => true, 'message' => 'Ledger entry deleted.']);
    }
}
