<?php

namespace App\Http\Controllers;

use App\Models\AssetMaintenanceLog;
use App\Models\FactoryAsset;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FactoryAssetController extends Controller
{
    public function index(Request $request)
    {
        $search   = $request->get('search');
        $category = $request->get('category');
        $status   = $request->get('status');

        $query = FactoryAsset::with('supplier')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('asset_code', 'like', "%{$search}%")
                  ->orWhere('make_brand', 'like', "%{$search}%")
                  ->orWhere('plant_location', 'like', "%{$search}%")
                  ->orWhere('model_number', 'like', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $assets = $query->paginate(25)->withQueryString();

        // KPI calculations
        $totalAssets      = FactoryAsset::count();
        $runningCount     = FactoryAsset::where('status', 'Operational')->count();
        $standbyCount     = FactoryAsset::where('status', 'Standby')->count();
        $breakdownCount   = FactoryAsset::where('status', 'Breakdown')->count();
        $maintenanceCount = FactoryAsset::where('status', 'Maintenance / Overhaul')->count();
        $totalAssetValue  = FactoryAsset::sum('purchase_cost');

        $suppliers = Supplier::orderBy('name')->get();

        return view('factory-assets.index', compact(
            'assets',
            'totalAssets',
            'runningCount',
            'standbyCount',
            'breakdownCount',
            'maintenanceCount',
            'totalAssetValue',
            'suppliers',
            'search',
            'category',
            'status'
        ));
    }

    public function show(FactoryAsset $factoryAsset)
    {
        $factoryAsset->load(['supplier', 'maintenanceLogs']);
        $totalMaintenanceCost = $factoryAsset->maintenanceLogs->sum('cost');
        $lastLog = $factoryAsset->maintenanceLogs->first();

        return view('factory-assets.show', compact('factoryAsset', 'totalMaintenanceCost', 'lastLog'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_code'            => 'nullable|string|max:50|unique:factory_assets,asset_code',
            'name'                  => 'required|string|max:255',
            'category'              => 'required|string|max:100',
            'make_brand'            => 'nullable|string|max:150',
            'model_number'          => 'nullable|string|max:100',
            'serial_number'         => 'nullable|string|max:100',
            'tonnage_or_capacity'   => 'nullable|string|max:100',
            'power_rating_kw'       => 'nullable|numeric|min:0',
            'plant_location'        => 'nullable|string|max:150',
            'purchase_date'         => 'nullable|date',
            'purchase_cost'         => 'nullable|numeric|min:0',
            'warranty_expiry'       => 'nullable|date',
            'supplier_id'           => 'nullable|exists:suppliers,id',
            'status'                => 'required|string|max:50',
            'assigned_operator'     => 'nullable|string|max:150',
            'last_service_date'     => 'nullable|date',
            'next_service_date'     => 'nullable|date',
            'service_interval_days' => 'nullable|integer|min:1',
            'image'                 => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'notes'                 => 'nullable|string',
        ]);

        if (empty($validated['asset_code'])) {
            $prefix = match ($validated['category']) {
                'Moulding Machine'      => 'MCH',
                'Compressor & Chiller'  => 'CHL',
                'Auxiliary Equipment'   => 'AUX',
                'Electrical & Power'    => 'PWR',
                default                 => 'AST',
            };
            $validated['asset_code'] = FactoryAsset::generateCode($prefix);
        }

        if ($request->hasFile('image')) {
            $dest = public_path('uploads/assets');
            if (!file_exists($dest)) {
                @mkdir($dest, 0777, true);
            }
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
            $file->move($dest, $filename);
            $validated['image'] = 'uploads/assets/' . $filename;
        }

        FactoryAsset::create($validated);

        return redirect()->route('factory-assets.index')->with('success', 'Factory machine / asset registered successfully.');
    }

    public function update(Request $request, FactoryAsset $factoryAsset)
    {
        $validated = $request->validate([
            'asset_code'            => 'required|string|max:50|unique:factory_assets,asset_code,' . $factoryAsset->id,
            'name'                  => 'required|string|max:255',
            'category'              => 'required|string|max:100',
            'make_brand'            => 'nullable|string|max:150',
            'model_number'          => 'nullable|string|max:100',
            'serial_number'         => 'nullable|string|max:100',
            'tonnage_or_capacity'   => 'nullable|string|max:100',
            'power_rating_kw'       => 'nullable|numeric|min:0',
            'plant_location'        => 'nullable|string|max:150',
            'purchase_date'         => 'nullable|date',
            'purchase_cost'         => 'nullable|numeric|min:0',
            'warranty_expiry'       => 'nullable|date',
            'supplier_id'           => 'nullable|exists:suppliers,id',
            'status'                => 'required|string|max:50',
            'assigned_operator'     => 'nullable|string|max:150',
            'last_service_date'     => 'nullable|date',
            'next_service_date'     => 'nullable|date',
            'service_interval_days' => 'nullable|integer|min:1',
            'image'                 => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'notes'                 => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $dest = public_path('uploads/assets');
            if (!file_exists($dest)) {
                @mkdir($dest, 0777, true);
            }
            if ($factoryAsset->image && file_exists(public_path($factoryAsset->image))) {
                @unlink(public_path($factoryAsset->image));
            }
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
            $file->move($dest, $filename);
            $validated['image'] = 'uploads/assets/' . $filename;
        }

        $factoryAsset->update($validated);

        return redirect()->route('factory-assets.index')->with('success', 'Asset details updated successfully.');
    }

    public function destroy(FactoryAsset $factoryAsset)
    {
        if ($factoryAsset->image && file_exists(public_path($factoryAsset->image))) {
            @unlink(public_path($factoryAsset->image));
        }
        $factoryAsset->delete();

        return redirect()->route('factory-assets.index')->with('success', 'Asset deleted.');
    }

    public function logMaintenance(Request $request, FactoryAsset $factoryAsset)
    {
        $validated = $request->validate([
            'service_date'         => 'required|date',
            'service_type'         => 'required|string|max:100',
            'cost'                 => 'nullable|numeric|min:0',
            'technician_name'      => 'nullable|string|max:150',
            'vendor_name'          => 'nullable|string|max:150',
            'parts_replaced'       => 'nullable|string',
            'problem_reported'     => 'nullable|string',
            'action_taken'         => 'nullable|string',
            'status_after_service' => 'required|string|max:50',
            'next_service_due'     => 'nullable|date',
        ]);

        DB::transaction(function () use ($validated, $factoryAsset) {
            AssetMaintenanceLog::create([
                'asset_id'             => $factoryAsset->id,
                'service_date'         => $validated['service_date'],
                'service_type'         => $validated['service_type'],
                'cost'                 => $validated['cost'] ?? 0.00,
                'technician_name'      => $validated['technician_name'] ?? null,
                'vendor_name'          => $validated['vendor_name'] ?? null,
                'parts_replaced'       => $validated['parts_replaced'] ?? null,
                'problem_reported'     => $validated['problem_reported'] ?? null,
                'action_taken'         => $validated['action_taken'] ?? null,
                'status_after_service' => $validated['status_after_service'],
                'next_service_due'     => $validated['next_service_due'] ?? null,
            ]);

            $factoryAsset->last_service_date = $validated['service_date'];
            $factoryAsset->next_service_date = $validated['next_service_due'] ?? null;
            $factoryAsset->status            = $validated['status_after_service'];
            $factoryAsset->save();
        });

        return redirect()->route('factory-assets.show', $factoryAsset)->with('success', 'Service / Maintenance recorded successfully.');
    }
}
