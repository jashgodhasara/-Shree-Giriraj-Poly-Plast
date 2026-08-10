<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransporterResource;
use App\Models\Transporter;
use Illuminate\Http\Request;

class TransporterApiController extends Controller
{
    public function index()
    {
        return TransporterResource::collection(Transporter::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'vehicle_no' => 'nullable|string|max:50',
            'phone'      => 'nullable|string|max:20',
        ]);
        return new TransporterResource(Transporter::create($validated));
    }

    public function show(Transporter $transporter)
    {
        return new TransporterResource($transporter);
    }

    public function update(Request $request, Transporter $transporter)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'vehicle_no' => 'nullable|string|max:50',
            'phone'      => 'nullable|string|max:20',
        ]);
        $transporter->update($validated);
        return new TransporterResource($transporter);
    }

    public function destroy(Transporter $transporter)
    {
        $transporter->delete();
        return response()->json(['message' => 'Transporter deleted.']);
    }
}
