<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Investor;
use App\Models\JobWork;
use App\Models\Ledger;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    public function index(Request $request)
    {
        $preset    = $request->get('preset', '');
        $dateFrom  = $request->get('date_from', '');
        $dateTo    = $request->get('date_to', '');

        [$dateFrom, $dateTo] = $this->resolveDates($preset, $dateFrom, $dateTo);

        $query = Ledger::latest('transaction_date')->latest('id');

        if ($dateFrom) {
            $query->whereDate('transaction_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('transaction_date', '<=', $dateTo);
        }

        $entries   = $query->paginate(100)->appends($request->query());
        $customers = Customer::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $investors = Investor::orderBy('name')->get();
        $jobWorks  = JobWork::orderBy('party_name')->get();

        // Summary totals — always calculated (no date filter = all-time)
        $allQuery   = Ledger::query();
        if ($dateFrom) $allQuery->whereDate('transaction_date', '>=', $dateFrom);
        if ($dateTo)   $allQuery->whereDate('transaction_date', '<=', $dateTo);
        $totalCredit = (clone $allQuery)->where('type', 'Credit')->sum('amount');
        $totalDebit  = (clone $allQuery)->where('type', 'Debit')->sum('amount');
        $totalCount  = (clone $allQuery)->count();

        return view('ledger.index', compact(
            'entries', 'customers', 'suppliers', 'investors', 'jobWorks',
            'preset', 'dateFrom', 'dateTo', 'totalCredit', 'totalDebit', 'totalCount'
        ));
    }

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
                'last_year'    => [$today->copy()->subYear()->startOfYear()->toDateString(), $today->copy()->subYear()->endOfYear()->toDateString()],
                default        => [$dateFrom ? Carbon::parse($dateFrom)->toDateString() : '', $dateTo ? Carbon::parse($dateTo)->toDateString() : ''],
            };
        }

        $from = $dateFrom ? Carbon::parse($dateFrom)->toDateString() : '';
        $to   = $dateTo ? Carbon::parse($dateTo)->toDateString() : '';

        return [$from, $to];
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
