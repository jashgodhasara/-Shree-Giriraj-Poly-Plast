<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerApiController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->expectsJson()) {
            return redirect('/customers');
        }

        return CustomerResource::collection(Customer::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:30',
            'email'   => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'gstin'   => 'nullable|string|max:20',
            'state'   => 'nullable|string|max:50',
        ]);
        return new CustomerResource(Customer::create($validated));
    }

    public function show(Customer $customer)
    {
        $invoices = $customer->invoices()
            ->with(['items.product', 'payments'])
            ->latest('invoice_date')
            ->get();

        $totalSales = (float) $invoices->sum('grand_total');
        $totalPaid = (float) $invoices->sum(function ($inv) {
            return $inv->payments->sum('amount');
        });
        if ($totalPaid == 0 && $invoices->sum('paid_amount') > 0) {
            $totalPaid = (float) $invoices->sum('paid_amount');
        }
        $totalPending = max(0, $totalSales - $totalPaid);

        return response()->json([
            'customer'      => new CustomerResource($customer),
            'total_sales'   => $totalSales,
            'total_paid'    => $totalPaid,
            'total_pending' => $totalPending,
            'invoice_count' => $invoices->count(),
            'invoices'      => $invoices,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:30',
            'email'   => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'gstin'   => 'nullable|string|max:20',
            'state'   => 'nullable|string|max:50',
        ]);
        $customer->update($validated);
        return new CustomerResource($customer);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->json(['message' => 'Customer deleted.']);
    }
}
