<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = [
            ['id' => 1, 'name' => 'Main Plant & HQ', 'city' => 'Ahmedabad, Gujarat', 'type' => 'Factory & Warehouse', 'is_main' => true],
            ['id' => 2, 'name' => 'Branch Store #1', 'city' => 'Surat, Gujarat', 'type' => 'Retail Depot', 'is_main' => false],
            ['id' => 3, 'name' => 'Branch Store #2', 'city' => 'Vadodara, Gujarat', 'type' => 'Distribution Hub', 'is_main' => false],
        ];

        $stockTransfers = [
            [
                'from_branch' => 'Main Plant & HQ (Ahmedabad)',
                'to_branch'   => 'Branch Store #1 (Surat)',
                'material'    => 'Polypropylene Granules Grade A',
                'quantity'    => '250.00 Kg',
                'reason'      => 'Surat Depot Low Stock Alert (Auto AI Recommendation)',
                'status'      => 'Suggested by AI',
            ],
            [
                'from_branch' => 'Branch Store #2 (Vadodara)',
                'to_branch'   => 'Main Plant & HQ (Ahmedabad)',
                'material'    => 'Plastic Soup Bowl (Finished Goods)',
                'quantity'    => '500 Pcs',
                'reason'      => 'Surat Depot Surplus Redistribution',
                'status'      => 'In Transit',
            ]
        ];

        return view('branches.index', compact('branches', 'stockTransfers'));
    }

    public function switchBranch(Request $request)
    {
        $request->validate(['branch_name' => 'required|string']);
        session(['current_branch' => $request->branch_name]);

        return response()->json([
            'success' => true,
            'message' => 'Switched active location to ' . $request->branch_name,
        ]);
    }
}
