<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::latest()->get();
        return view('customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'gstin'   => 'nullable|string|max:15',
            'state'   => 'nullable|string|max:50',
        ]);

        Customer::create($validated);

        return response()->json(['success' => true, 'message' => 'Customer added successfully.']);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'gstin'   => 'nullable|string|max:15',
            'state'   => 'nullable|string|max:50',
        ]);

        $customer->update($validated);

        return response()->json(['success' => true, 'message' => 'Customer updated successfully.']);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->json(['success' => true, 'message' => 'Customer deleted.']);
    }
}
