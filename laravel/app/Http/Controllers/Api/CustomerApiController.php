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
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email',
            'address' => 'nullable|string',
            'gstin'   => 'nullable|string|max:15',
            'state'   => 'nullable|string|max:50',
        ]);
        return new CustomerResource(Customer::create($validated));
    }

    public function show(Customer $customer)
    {
        return new CustomerResource($customer);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email',
            'address' => 'nullable|string',
            'gstin'   => 'nullable|string|max:15',
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
