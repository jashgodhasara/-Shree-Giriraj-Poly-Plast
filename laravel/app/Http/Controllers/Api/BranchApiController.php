<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchApiController extends Controller
{
    public function index()
    {
        if (Branch::count() === 0) {
            Branch::insert([
                [
                    'name'          => 'Main Plant & HQ',
                    'city'          => 'Ahmedabad, Gujarat',
                    'type'          => 'Factory & Warehouse',
                    'manager_name'  => 'Rajeshbhai Patel (Plant Head)',
                    'manager_phone' => '+91 98250 12345',
                    'manager_email' => 'plant.head@shreegiriraj.com',
                    'is_main'       => true,
                    'created_at'    => now(),
                    'updated_at'    => now()
                ],
                [
                    'name'          => 'Branch Store #1',
                    'city'          => 'Surat, Gujarat',
                    'type'          => 'Retail Depot',
                    'manager_name'  => 'Sureshbhai Shah (Depot Manager)',
                    'manager_phone' => '+91 98790 67890',
                    'manager_email' => 'surat.depot@shreegiriraj.com',
                    'is_main'       => false,
                    'created_at'    => now(),
                    'updated_at'    => now()
                ],
                [
                    'name'          => 'Branch Store #2',
                    'city'          => 'Vadodara, Gujarat',
                    'type'          => 'Distribution Hub',
                    'manager_name'  => 'Mukeshbhai Prajapati (Hub Manager)',
                    'manager_phone' => '+91 99090 54321',
                    'manager_email' => 'vadodara.hub@shreegiriraj.com',
                    'is_main'       => false,
                    'created_at'    => now(),
                    'updated_at'    => now()
                ],
            ]);
        }

        return response()->json(Branch::orderBy('is_main', 'desc')->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'city'          => 'required|string|max:255',
            'type'          => 'required|string|max:255',
            'manager_name'  => 'required|string|max:255',
            'manager_phone' => 'nullable|string|max:30',
            'manager_email' => 'nullable|email|max:255',
        ]);

        $validated['is_main'] = false;
        $branch = Branch::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Branch created successfully.',
            'data'    => $branch,
        ], 201);
    }

    public function show(Branch $branch)
    {
        return response()->json($branch);
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'city'          => 'required|string|max:255',
            'type'          => 'required|string|max:255',
            'manager_name'  => 'required|string|max:255',
            'manager_phone' => 'nullable|string|max:30',
            'manager_email' => 'nullable|email|max:255',
        ]);

        $branch->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Branch updated successfully.',
            'data'    => $branch,
        ]);
    }

    public function destroy(Branch $branch)
    {
        if ($branch->is_main) {
            return response()->json([
                'success' => false,
                'message' => 'Primary / Main Plant branch cannot be deleted.',
            ], 422);
        }

        $branch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Branch deleted successfully.',
        ]);
    }
}
