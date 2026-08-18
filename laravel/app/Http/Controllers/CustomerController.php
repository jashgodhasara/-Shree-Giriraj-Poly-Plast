<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::latest()->get();
        if ($request->wantsJson()) {
            return response()->json($customers);
        }
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
            'image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:4096',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/customers'), $filename);
            $validated['image'] = 'uploads/customers/' . $filename;
        }

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
            'image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:4096',
        ]);

        if ($request->hasFile('image')) {
            if ($customer->image && file_exists(public_path($customer->image))) {
                @unlink(public_path($customer->image));
            }
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/customers'), $filename);
            $validated['image'] = 'uploads/customers/' . $filename;
        } elseif ($request->boolean('remove_image')) {
            if ($customer->image && file_exists(public_path($customer->image))) {
                @unlink(public_path($customer->image));
            }
            $validated['image'] = null;
        }

        $customer->update($validated);

        return response()->json(['success' => true, 'message' => 'Customer updated successfully.']);
    }

    public function destroy(Customer $customer)
    {
        if ($customer->image && file_exists(public_path($customer->image))) {
            @unlink(public_path($customer->image));
        }
        $customer->delete();
        return response()->json(['success' => true, 'message' => 'Customer deleted.']);
    }
}
