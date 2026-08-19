<?php

namespace App\Http\Controllers;

use App\Models\JobWorkClient;
use Illuminate\Http\Request;

class JobWorkClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = JobWorkClient::withCount('orders')->latest()->get();

        if ($request->wantsJson()) {
            return response()->json($clients);
        }

        return view('jobworks.clients', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'company_name'    => 'nullable|string|max:255',
            'phone'           => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:255',
            'address'         => 'nullable|string',
            'gstin'           => 'nullable|string|max:20',
            'opening_balance' => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $client = JobWorkClient::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Job Work Client created successfully.',
                'client'  => $client,
            ]);
        }

        return redirect()->route('jobworks.clients.index')->with('success', 'Job Work Client added successfully.');
    }

    public function update(Request $request, JobWorkClient $client)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'company_name'    => 'nullable|string|max:255',
            'phone'           => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:255',
            'address'         => 'nullable|string',
            'gstin'           => 'nullable|string|max:20',
            'opening_balance' => 'nullable|numeric',
            'notes'           => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $client->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Job Work Client updated successfully.',
                'client'  => $client,
            ]);
        }

        return redirect()->route('jobworks.clients.index')->with('success', 'Job Work Client updated successfully.');
    }

    public function destroy(JobWorkClient $client)
    {
        if ($client->orders()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete client with existing Job Work orders.',
            ], 422);
        }

        $client->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job Work Client deleted successfully.',
        ]);
    }
}
