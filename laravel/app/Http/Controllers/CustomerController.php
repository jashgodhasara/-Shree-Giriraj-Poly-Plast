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

        try {
            if ($request->hasFile('image')) {
                $dest = public_path('uploads/customers');
                if (!file_exists($dest)) {
                    @mkdir($dest, 0777, true);
                }
                $file = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
                $file->move($dest, $filename);
                $validated['image'] = 'uploads/customers/' . $filename;
            }

            Customer::create($validated);
            return response()->json(['success' => true, 'message' => 'Customer added successfully.']);
        } catch (\Throwable $e) {
            \Log::error('Customer Store Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error adding customer: ' . $e->getMessage()], 500);
        }
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

        try {
            if ($request->hasFile('image')) {
                $dest = public_path('uploads/customers');
                if (!file_exists($dest)) {
                    @mkdir($dest, 0777, true);
                }
                if ($customer->image && file_exists(public_path($customer->image))) {
                    @unlink(public_path($customer->image));
                }
                $file = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
                $file->move($dest, $filename);
                $validated['image'] = 'uploads/customers/' . $filename;
            } elseif ($request->boolean('remove_image')) {
                if ($customer->image && file_exists(public_path($customer->image))) {
                    @unlink(public_path($customer->image));
                }
                $validated['image'] = null;
            }

            $customer->update($validated);
            return response()->json(['success' => true, 'message' => 'Customer updated successfully.']);
        } catch (\Throwable $e) {
            \Log::error('Customer Update Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error updating customer: ' . $e->getMessage()], 500);
        }
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
