<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobWork;
use Illuminate\Http\Request;

class JobWorkApiController extends Controller
{
    public function index()
    {
        return response()->json(JobWork::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'party_name' => 'required|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'work_type'  => 'nullable|string|max:255',
            'rate'       => 'nullable|numeric|min:0',
            'unit'       => 'nullable|string|max:20',
            'address'    => 'nullable|string',
            'notes'      => 'nullable|string',
        ]);
        return response()->json(JobWork::create($validated), 201);
    }

    public function show(JobWork $jobWork)
    {
        return response()->json($jobWork);
    }

    public function update(Request $request, JobWork $jobWork)
    {
        $validated = $request->validate([
            'party_name' => 'required|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'work_type'  => 'nullable|string|max:255',
            'rate'       => 'nullable|numeric|min:0',
            'unit'       => 'nullable|string|max:20',
            'address'    => 'nullable|string',
            'notes'      => 'nullable|string',
        ]);
        $jobWork->update($validated);
        return response()->json($jobWork);
    }

    public function destroy(JobWork $jobWork)
    {
        $jobWork->delete();
        return response()->json(['message' => 'Job work deleted.']);
    }
}
