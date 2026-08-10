<?php

namespace App\Http\Controllers;

use App\Models\Investor;
use Illuminate\Http\Request;

class InvestorController extends Controller
{
    public function index()
    {
        $investors = Investor::latest()->get();
        return view('investors.index', compact('investors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'address'           => 'nullable|string',
            'investment_amount' => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string',
        ]);

        Investor::create($validated);

        return response()->json(['success' => true, 'message' => 'Investor added successfully.']);
    }

    public function update(Request $request, Investor $investor)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'address'           => 'nullable|string',
            'investment_amount' => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string',
        ]);

        $investor->update($validated);

        return response()->json(['success' => true, 'message' => 'Investor updated successfully.']);
    }

    public function destroy(Investor $investor)
    {
        $investor->delete();
        return response()->json(['success' => true, 'message' => 'Investor deleted.']);
    }
}
