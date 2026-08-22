<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PlasticPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlasticPricingApiController extends Controller
{
    protected PlasticPricingService $pricingService;

    public function __construct(PlasticPricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Return live polymer pricing feed.
     */
    public function index(Request $request): JsonResponse
    {
        $force = $request->boolean('refresh', false);
        $data = $this->pricingService->getPrices($force);

        return response()->json($data);
    }
}
