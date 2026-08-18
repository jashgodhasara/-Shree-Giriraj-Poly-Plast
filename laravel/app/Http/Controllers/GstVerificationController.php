<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GstVerificationController extends Controller
{
    /**
     * Verify GSTIN and return standardized business information.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'gstin' => 'required|string|min:15|max:15',
        ]);

        $gstin = strtoupper(trim($request->input('gstin')));
        $apiKey = config('services.gstin.api_key', env('GSTIN_API_KEY', '375ae44ed21b759aa6a580d31a4ff3d5'));

        // Basic GSTIN format check
        if (!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $gstin)) {
            return response()->json([
                'success' => false,
                'valid'   => false,
                'message' => 'Invalid GSTIN format. Must be 15 alphanumeric characters (e.g. 24AHUPP7924M1ZG).',
            ], 422);
        }

        try {
            $url = "https://api.gstincheck.co.in/check/{$apiKey}/{$gstin}";
            $response = Http::timeout(8)
                ->withoutVerifying()
                ->get($url);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'valid'   => false,
                    'message' => 'GSTIN lookup service returned an error. Please try again.',
                ], 502);
            }

            $data = $response->json();

            if (!empty($data['flag']) && !empty($data['data'])) {
                $d = $data['data'];
                $tradeName = !empty($d['tradeNam']) ? trim($d['tradeNam']) : '';
                $legalName = !empty($d['lgnm']) ? trim($d['lgnm']) : '';
                $name = $tradeName ?: $legalName;

                $addr = $d['pradr']['addr'] ?? [];
                $state = $addr['stcd'] ?? '';
                $city = $addr['dst'] ?? ($addr['city'] ?? '');
                $pincode = $addr['pncd'] ?? '';
                $fullAddress = $d['pradr']['adr'] ?? '';

                $status = $d['sts'] ?? 'Active';

                return response()->json([
                    'success'       => true,
                    'valid'         => true,
                    'gstin'         => $gstin,
                    'name'          => $name,
                    'trade_name'    => $tradeName,
                    'legal_name'    => $legalName,
                    'status'        => $status,
                    'is_active'     => strtolower($status) === 'active',
                    'state'         => $state,
                    'city'          => $city,
                    'pincode'       => $pincode,
                    'address'       => $fullAddress,
                    'business_type' => $d['ctb'] ?? '',
                    'registered_on' => $d['rgdt'] ?? '',
                    'message'       => "GSTIN Verified: {$name} ({$status})",
                ]);
            }

            return response()->json([
                'success' => false,
                'valid'   => false,
                'message' => $data['message'] ?? 'GSTIN not found or inactive on GST portal.',
            ], 404);

        } catch (\Throwable $e) {
            Log::error('GSTIN Verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'valid'   => false,
                'message' => 'Network error connecting to GST verification API: ' . $e->getMessage(),
            ], 500);
        }
    }
}
