<?php

namespace App\Http\Controllers;

use App\Models\Transporter;
use Illuminate\Http\Request;

class TransporterController extends Controller
{
    public function index()
    {
        $transporters = Transporter::latest()->get();
        return view('transporters.index', compact('transporters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'vehicle_no' => 'nullable|string|max:50',
            'phone'      => 'nullable|string|max:20',
        ]);

        Transporter::create($validated);

        return response()->json(['success' => true, 'message' => 'Transporter added successfully.']);
    }

    public function update(Request $request, Transporter $transporter)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'vehicle_no' => 'nullable|string|max:50',
            'phone'      => 'nullable|string|max:20',
        ]);

        $transporter->update($validated);

        return response()->json(['success' => true, 'message' => 'Transporter updated successfully.']);
    }

    public function destroy(Transporter $transporter)
    {
        $transporter->delete();
        return response()->json(['success' => true, 'message' => 'Transporter deleted.']);
    }
}
