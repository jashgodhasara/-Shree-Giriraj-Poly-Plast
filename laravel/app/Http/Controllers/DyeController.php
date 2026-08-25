<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DyeAndMould;
use App\Models\DyeMaintenanceLog;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DyeController extends Controller
{
    public function index(Request $request)
    {
        $search    = $request->get('search');
        $type      = $request->get('mould_type');
        $status    = $request->get('status');
        $ownership = $request->get('ownership_type');

        $query = DyeAndMould::with(['customer', 'product'])->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('rack_location', 'like', "%{$search}%")
                  ->orWhere('compatible_machines', 'like', "%{$search}%");
            });
        }

        if ($type) {
            $query->where('mould_type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($ownership) {
            $query->where('ownership_type', $ownership);
        }

        $dyes = $query->paginate(25)->withQueryString();

        // Summary KPI stats
        $totalCount       = DyeAndMould::count();
        $readyCount       = DyeAndMould::where('status', 'Ready / In Storage')->count();
        $onMachineCount   = DyeAndMould::where('status', 'Mounted on Machine')->count();
        $maintenanceCount = DyeAndMould::where('status', 'Under Maintenance')->count();
        $companyOwned     = DyeAndMould::where('ownership_type', 'Company Owned')->count();
        $clientOwned      = DyeAndMould::where('ownership_type', 'Client Owned')->count();

        $customers = Customer::orderBy('name')->get();
        $products  = Product::orderBy('name')->get();

        return view('dyes.index', compact(
            'dyes',
            'totalCount',
            'readyCount',
            'onMachineCount',
            'maintenanceCount',
            'companyOwned',
            'clientOwned',
            'customers',
            'products',
            'search',
            'type',
            'status',
            'ownership'
        ));
    }

    public function show(DyeAndMould $dye)
    {
        $dye->load(['customer', 'product', 'maintenanceLogs']);
        $totalMaintenanceCost = $dye->maintenanceLogs->sum('cost');
        $lastLog = $dye->maintenanceLogs->first();

        return view('dyes.show', compact('dye', 'totalMaintenanceCost', 'lastLog'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'                   => 'nullable|string|max:50|unique:dyes_and_moulds,code',
            'name'                   => 'required|string|max:255',
            'mould_type'             => 'required|string|max:100',
            'cavities'               => 'required|integer|min:1',
            'ownership_type'         => 'required|in:Company Owned,Client Owned',
            'customer_id'            => 'nullable|required_if:ownership_type,Client Owned|exists:customers,id',
            'product_id'             => 'nullable|exists:products,id',
            'compatible_machines'    => 'nullable|string|max:255',
            'rack_location'          => 'nullable|string|max:150',
            'status'                 => 'required|string|max:50',
            'total_shots_count'      => 'nullable|integer|min:0',
            'service_interval_shots' => 'nullable|integer|min:100',
            'purchase_cost'          => 'nullable|numeric|min:0',
            'fabrication_date'       => 'nullable|date',
            'last_serviced_date'     => 'nullable|date',
            'next_service_due_date'  => 'nullable|date',
            'image'                  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'notes'                  => 'nullable|string',
        ]);

        if (empty($validated['code'])) {
            $prefix = match ($validated['mould_type']) {
                'Blow Mould'     => 'BLW',
                'Extrusion Die'  => 'EXT',
                default          => 'DIE',
            };
            $validated['code'] = DyeAndMould::generateCode($prefix);
        }

        if ($request->hasFile('image')) {
            $dest = public_path('uploads/dyes');
            if (!file_exists($dest)) {
                @mkdir($dest, 0777, true);
            }
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
            $file->move($dest, $filename);
            $validated['image'] = 'uploads/dyes/' . $filename;
        }

        DyeAndMould::create($validated);

        return redirect()->route('dyes.index')->with('success', 'Dye / Mould added successfully.');
    }

    public function update(Request $request, DyeAndMould $dye)
    {
        $validated = $request->validate([
            'code'                   => 'required|string|max:50|unique:dyes_and_moulds,code,' . $dye->id,
            'name'                   => 'required|string|max:255',
            'mould_type'             => 'required|string|max:100',
            'cavities'               => 'required|integer|min:1',
            'ownership_type'         => 'required|in:Company Owned,Client Owned',
            'customer_id'            => 'nullable|required_if:ownership_type,Client Owned|exists:customers,id',
            'product_id'             => 'nullable|exists:products,id',
            'compatible_machines'    => 'nullable|string|max:255',
            'rack_location'          => 'nullable|string|max:150',
            'status'                 => 'required|string|max:50',
            'total_shots_count'      => 'nullable|integer|min:0',
            'service_interval_shots' => 'nullable|integer|min:100',
            'purchase_cost'          => 'nullable|numeric|min:0',
            'fabrication_date'       => 'nullable|date',
            'last_serviced_date'     => 'nullable|date',
            'next_service_due_date'  => 'nullable|date',
            'image'                  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'notes'                  => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $dest = public_path('uploads/dyes');
            if (!file_exists($dest)) {
                @mkdir($dest, 0777, true);
            }
            if ($dye->image && file_exists(public_path($dye->image))) {
                @unlink(public_path($dye->image));
            }
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
            $file->move($dest, $filename);
            $validated['image'] = 'uploads/dyes/' . $filename;
        }

        $dye->update($validated);

        return redirect()->route('dyes.index')->with('success', 'Dye / Mould details updated successfully.');
    }

    public function destroy(DyeAndMould $dye)
    {
        if ($dye->image && file_exists(public_path($dye->image))) {
            @unlink(public_path($dye->image));
        }
        $dye->delete();

        return redirect()->route('dyes.index')->with('success', 'Dye / Mould deleted.');
    }

    public function logMaintenance(Request $request, DyeAndMould $dye)
    {
        $validated = $request->validate([
            'maintenance_date'  => 'required|date',
            'maintenance_type'  => 'required|string|max:100',
            'shots_at_service'  => 'nullable|integer|min:0',
            'cost'              => 'nullable|numeric|min:0',
            'performed_by'      => 'nullable|string|max:150',
            'vendor_name'       => 'nullable|string|max:150',
            'work_description'  => 'nullable|string',
            'status_after'      => 'required|string|max:50',
            'next_due_date'     => 'nullable|date',
        ]);

        DB::transaction(function () use ($validated, $dye) {
            DyeMaintenanceLog::create([
                'dye_id'            => $dye->id,
                'maintenance_date'  => $validated['maintenance_date'],
                'maintenance_type'  => $validated['maintenance_type'],
                'shots_at_service'  => $validated['shots_at_service'] ?? $dye->total_shots_count,
                'cost'              => $validated['cost'] ?? 0.00,
                'performed_by'      => $validated['performed_by'] ?? null,
                'vendor_name'       => $validated['vendor_name'] ?? null,
                'work_description'  => $validated['work_description'] ?? null,
                'next_due_date'     => $validated['next_due_date'] ?? null,
            ]);

            $dye->last_serviced_date    = $validated['maintenance_date'];
            $dye->next_service_due_date = $validated['next_due_date'] ?? null;
            $dye->status                = $validated['status_after'];
            if (!empty($validated['shots_at_service'])) {
                $dye->total_shots_count = max($dye->total_shots_count, (int)$validated['shots_at_service']);
            }
            $dye->save();
        });

        return redirect()->route('dyes.show', $dye)->with('success', 'Maintenance record logged successfully.');
    }

    public function updateShots(Request $request, DyeAndMould $dye)
    {
        $validated = $request->validate([
            'added_shots' => 'required|integer|min:1',
        ]);

        $dye->increment('total_shots_count', $validated['added_shots']);

        return response()->json([
            'success' => true,
            'message' => 'Shot count updated.',
            'total_shots' => $dye->total_shots_count,
        ]);
    }
}
