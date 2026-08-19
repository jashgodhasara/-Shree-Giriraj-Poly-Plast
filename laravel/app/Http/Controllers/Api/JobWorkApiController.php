<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobWorkClient;
use App\Models\JobWorkOrder;
use App\Models\Product;
use App\Services\JobWorkCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JobWorkApiController extends Controller
{
    protected JobWorkCalculationService $calcService;

    public function __construct(JobWorkCalculationService $calcService)
    {
        $this->calcService = $calcService;
    }

    public function index(Request $request)
    {
        $orders = JobWorkOrder::with('client', 'items.product')
            ->latest('order_date')
            ->paginate($request->get('per_page', 25));

        return response()->json($orders);
    }

    public function show(JobWorkOrder $jobWorkOrder)
    {
        $jobWorkOrder->load(['client', 'items.product', 'deliveries', 'auditLogs.user']);
        return response()->json($jobWorkOrder);
    }

    public function getProductWeight(Product $product)
    {
        return response()->json([
            'id'                  => $product->id,
            'name'                => $product->name,
            'sku'                 => $product->sku,
            'unit_type'           => $product->unit_type,
            'weight_per_piece'    => (float) ($product->weight_per_piece ?: 10),
            'weight_unit'         => $product->weight_unit ?: 'Gram',
            'weight_in_grams'     => (float) $product->calculated_weight_grams,
            'wastage_percentage'  => (float) ($product->wastage_percentage ?: 0),
            'fixed_wastage'       => (float) ($product->fixed_wastage ?: 0),
            'job_work_applicable' => (bool) $product->job_work_applicable,
        ]);
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'received_weight'      => 'required|numeric|min:0',
            'received_weight_unit' => 'required|in:KG,Gram,Milligram,Ton',
            'product_weight'       => 'required|numeric|gt:0',
            'product_weight_unit'  => 'required|in:Gram,KG,Milligram,Ton',
            'wastage_type'         => 'required|in:percentage,fixed,none',
            'wastage_percentage'   => 'nullable|numeric|min:0|max:100',
            'fixed_wastage'        => 'nullable|numeric|min:0',
            'rate_type'            => 'required|in:per_piece,per_kg,fixed',
            'rate'                 => 'nullable|numeric|min:0',
            'rounding_method'      => 'nullable|in:floor,round,ceil,decimal',
        ]);

        $roundingMethod = $request->rounding_method ?? 'floor';
        $result = $this->calcService->calculateItem($validated, $roundingMethod);

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id'          => 'required|exists:job_work_clients,id',
            'order_date'         => 'required|date',
            'due_date'           => 'nullable|date',
            'reference_number'   => 'nullable|string|max:100',
            'status'             => 'nullable|in:Draft,Material Received,In Production,Partially Completed,Completed,Delivered,Cancelled',
            'rounding_method'    => 'nullable|in:floor,round,ceil,decimal',
            'additional_charges' => 'nullable|numeric|min:0',
            'discount'           => 'nullable|numeric|min:0',
            'tax'                => 'nullable|numeric|min:0',
            'paid_amount'        => 'nullable|numeric|min:0',
            'remarks'            => 'nullable|string',

            'items'                       => 'required|array|min:1',
            'items.*.product_id'          => 'required|exists:products,id',
            'items.*.received_weight'     => 'required|numeric|gt:0',
            'items.*.received_weight_unit'=> 'required|in:KG,Gram,Milligram,Ton',
            'items.*.product_weight'      => 'nullable|numeric|gt:0',
            'items.*.product_weight_unit' => 'nullable|in:Gram,KG,Milligram,Ton',
            'items.*.wastage_type'        => 'nullable|in:percentage,fixed,none',
            'items.*.wastage_percentage'  => 'nullable|numeric|min:0|max:100',
            'items.*.fixed_wastage'       => 'nullable|numeric|min:0',
            'items.*.rate_type'           => 'nullable|in:per_piece,per_kg,fixed',
            'items.*.rate'                => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $prefix = 'JW-' . date('Ym') . '-';
            $lastOrder = JobWorkOrder::where('job_work_number', 'like', $prefix . '%')->orderBy('job_work_number', 'desc')->first();
            $nextNum = $lastOrder ? ((int) substr($lastOrder->job_work_number, -4) + 1) : 1;
            $jwNumber = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            $roundingMethod = $request->rounding_method ?? 'floor';
            $calculatedItems = [];

            foreach ($request->items as $rawItem) {
                $product = Product::findOrFail($rawItem['product_id']);
                $pieceWeight     = !empty($rawItem['product_weight']) ? (float) $rawItem['product_weight'] : (float) ($product->weight_per_piece ?: 10);
                $pieceWeightUnit = !empty($rawItem['product_weight_unit']) ? $rawItem['product_weight_unit'] : ($product->weight_unit ?: 'Gram');
                $wastageType     = $rawItem['wastage_type'] ?? 'percentage';
                $wastagePct      = isset($rawItem['wastage_percentage']) ? (float) $rawItem['wastage_percentage'] : (float) ($product->wastage_percentage ?? 0);
                $fixedWastage    = isset($rawItem['fixed_wastage']) ? (float) $rawItem['fixed_wastage'] : 0;
                $rateType        = $rawItem['rate_type'] ?? 'per_piece';
                $rate            = (float) ($rawItem['rate'] ?? 0);

                $calc = $this->calcService->calculateItem([
                    'received_weight'      => (float) $rawItem['received_weight'],
                    'received_weight_unit' => $rawItem['received_weight_unit'],
                    'product_weight'       => $pieceWeight,
                    'product_weight_unit'  => $pieceWeightUnit,
                    'wastage_type'         => $wastageType,
                    'wastage_percentage'   => $wastagePct,
                    'fixed_wastage'        => $fixedWastage,
                    'rate_type'            => $rateType,
                    'rate'                 => $rate,
                ], $roundingMethod);

                $calc['product_id'] = $product->id;
                $calc['remarks']    = $rawItem['remarks'] ?? null;
                $calculatedItems[]  = $calc;
            }

            $totals = $this->calcService->calculateOrderTotals(
                $calculatedItems,
                (float) ($request->additional_charges ?? 0),
                (float) ($request->discount ?? 0),
                (float) ($request->tax ?? 0),
                (float) ($request->paid_amount ?? 0)
            );

            $order = JobWorkOrder::create(array_merge([
                'job_work_number'  => $jwNumber,
                'client_id'        => $request->client_id,
                'order_date'       => $request->order_date,
                'due_date'         => $request->due_date,
                'reference_number' => $request->reference_number,
                'status'           => $request->status ?? 'Material Received',
                'rounding_method'  => $roundingMethod,
                'remarks'          => $request->remarks,
                'created_by'       => Auth::id(),
            ], $totals));

            foreach ($calculatedItems as $itemData) {
                $order->items()->create([
                    'product_id'            => $itemData['product_id'],
                    'received_weight'       => $itemData['received_weight'],
                    'received_weight_unit'  => $itemData['received_weight_unit'],
                    'received_weight_grams' => $itemData['received_weight_grams'],
                    'product_weight'        => $itemData['product_weight'],
                    'product_weight_unit'   => $itemData['product_weight_unit'],
                    'product_weight_grams'  => $itemData['product_weight_grams'],
                    'gross_quantity'        => $itemData['gross_quantity'],
                    'wastage_type'          => $itemData['wastage_type'],
                    'wastage_percentage'    => $itemData['wastage_percentage'],
                    'wastage_quantity'      => $itemData['wastage_quantity'],
                    'net_quantity'          => $itemData['net_quantity'],
                    'delivered_quantity'    => 0,
                    'balance_quantity'      => $itemData['net_quantity'],
                    'rate_type'             => $itemData['rate_type'],
                    'rate'                  => $itemData['rate'],
                    'amount'                => $itemData['amount'],
                    'remarks'               => $itemData['remarks'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Job Work order created successfully.',
                'data'    => $order->load('client', 'items.product'),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
