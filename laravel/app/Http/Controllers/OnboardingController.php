<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function index()
    {
        return view('onboard.index');
    }

    public function saveConfig(Request $request)
    {
        $validated = $request->validate([
            'business_type' => 'required|string',
            'has_inventory' => 'required|string',
            'has_staff'     => 'required|string',
            'has_credit'    => 'required|string',
            'payment_modes' => 'array',
            'branch_count'  => 'required|string',
        ]);

        $profile = [
            'business_type' => $validated['business_type'],
            'has_inventory' => $validated['has_inventory'] === 'yes',
            'has_staff'     => $validated['has_staff'] === 'yes',
            'has_credit'    => $validated['has_credit'] === 'yes',
            'payment_modes' => $validated['payment_modes'] ?? ['Cash', 'UPI'],
            'branch_count'  => $validated['branch_count'],
            'configured_at' => now()->toDateTimeString(),
            'is_configured' => true,
        ];

        session(['erp_profile' => $profile]);

        return response()->json([
            'success'      => true,
            'message'      => 'AI Configurator completed successfully! Your custom ERP is ready.',
            'redirect_url' => route('dashboard') . '?tour=1',
        ]);
    }
}
