<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FactoryAsset;
use Illuminate\Http\Request;

class FactoryAssetApiController extends Controller
{
    public function index(Request $request)
    {
        $query = FactoryAsset::with('supplier:id,name')->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $assets = $query->get();

        return response()->json([
            'status' => 'success',
            'count'  => $assets->count(),
            'assets' => $assets,
        ]);
    }

    public function show(FactoryAsset $factoryAsset)
    {
        $factoryAsset->load(['supplier', 'maintenanceLogs']);

        return response()->json([
            'status' => 'success',
            'asset'  => $factoryAsset,
        ]);
    }
}
