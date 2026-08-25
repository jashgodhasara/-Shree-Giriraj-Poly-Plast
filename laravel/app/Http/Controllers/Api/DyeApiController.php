<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DyeAndMould;
use Illuminate\Http\Request;

class DyeApiController extends Controller
{
    public function index(Request $request)
    {
        $query = DyeAndMould::with(['customer:id,name', 'product:id,name,sku'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('mould_type', $request->type);
        }

        $dyes = $query->get();

        return response()->json([
            'status' => 'success',
            'count'  => $dyes->count(),
            'dyes'   => $dyes,
        ]);
    }

    public function show(DyeAndMould $dye)
    {
        $dye->load(['customer', 'product', 'maintenanceLogs']);

        return response()->json([
            'status' => 'success',
            'dye'    => $dye,
        ]);
    }
}
