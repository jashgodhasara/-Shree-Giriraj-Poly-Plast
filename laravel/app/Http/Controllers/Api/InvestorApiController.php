<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Investor;
use Illuminate\Http\Request;

class InvestorApiController extends Controller
{
    public function index()
    {
        return response()->json(Investor::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email',
            'address'           => 'nullable|string',
            'investment_amount' => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string',
        ]);
        return response()->json(Investor::create($validated), 201);
    }

    public function show(Investor $investor)
    {
        return response()->json($investor);
    }

    public function update(Request $request, Investor $investor)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email',
            'address'           => 'nullable|string',
            'investment_amount' => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string',
        ]);
        $investor->update($validated);
        return response()->json($investor);
    }

    public function destroy(Investor $investor)
    {
        $investor->delete();
        return response()->json(['message' => 'Investor deleted.']);
    }
}
