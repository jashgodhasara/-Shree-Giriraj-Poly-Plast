<?php

namespace App\Http\Controllers;

use App\Models\JobWork;
use Illuminate\Http\Request;

class JobWorkController extends Controller
{
    public function index()
    {
        $jobWorks = JobWork::latest()->get();
        return view('jobworks.index', compact('jobWorks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'party_name' => 'required|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'work_type'  => 'nullable|string|max:100',
            'rate'       => 'nullable|numeric|min:0',
            'unit'       => 'nullable|string|max:20',
            'address'    => 'nullable|string',
            'notes'      => 'nullable|string',
        ]);

        JobWork::create($validated);

        return response()->json(['success' => true, 'message' => 'Job Work party added successfully.']);
    }

    public function update(Request $request, JobWork $jobWork)
    {
        $validated = $request->validate([
            'party_name' => 'required|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'work_type'  => 'nullable|string|max:100',
            'rate'       => 'nullable|numeric|min:0',
            'unit'       => 'nullable|string|max:20',
            'address'    => 'nullable|string',
            'notes'      => 'nullable|string',
        ]);

        $jobWork->update($validated);

        return response()->json(['success' => true, 'message' => 'Job Work updated successfully.']);
    }

    public function destroy(JobWork $jobWork)
    {
        $jobWork->delete();
        return response()->json(['success' => true, 'message' => 'Job Work deleted.']);
    }
}
