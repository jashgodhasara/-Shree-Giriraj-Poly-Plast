<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\UnitConversion;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $units = Unit::with(['conversionsFrom.toUnit'])->orderBy('name')->get();
        $conversions = UnitConversion::with(['fromUnit', 'toUnit'])->get();

        if ($request->wantsJson()) {
            return response()->json(['units' => $units, 'conversions' => $conversions]);
        }

        return view('units.index', compact('units', 'conversions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'code'      => 'required|string|max:20|unique:units,code',
            'symbol'    => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active', true);

        $unit = Unit::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Unit created successfully.', 'unit' => $unit]);
        }

        return redirect()->route('units.index')->with('success', 'Unit created successfully.');
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'code'      => 'required|string|max:20|unique:units,code,' . $unit->id,
            'symbol'    => 'nullable|string|max:20',
            'is_active' => 'required|boolean',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $unit->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Unit updated successfully.', 'unit' => $unit]);
        }

        return redirect()->route('units.index')->with('success', 'Unit updated successfully.');
    }

    public function storeConversion(Request $request)
    {
        $validated = $request->validate([
            'from_unit_id'      => 'required|exists:units,id|different:to_unit_id',
            'to_unit_id'        => 'required|exists:units,id',
            'conversion_factor' => 'required|numeric|gt:0',
            'operator'          => 'nullable|in:*,/',
        ]);

        $validated['operator'] = $validated['operator'] ?: '*';

        $conversion = UnitConversion::updateOrCreate(
            ['from_unit_id' => $validated['from_unit_id'], 'to_unit_id' => $validated['to_unit_id']],
            $validated
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Unit conversion saved.', 'conversion' => $conversion]);
        }

        return redirect()->route('units.index')->with('success', 'Unit conversion saved successfully.');
    }

    public function destroyConversion(UnitConversion $conversion)
    {
        $conversion->delete();
        return response()->json(['success' => true, 'message' => 'Unit conversion deleted.']);
    }
}
