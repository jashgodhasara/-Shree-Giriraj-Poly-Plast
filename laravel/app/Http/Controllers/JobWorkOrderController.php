<?php

namespace App\Http\Controllers;

use App\Models\JobWorkAuditLog;
use App\Models\JobWorkClient;
use App\Models\JobWorkDelivery;
use App\Models\JobWorkDeliveryItem;
use App\Models\JobWorkOrder;
use App\Models\JobWorkOrderItem;
use App\Models\Product;
use App\Models\Transporter;
use App\Services\JobWorkCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JobWorkOrderController extends Controller
{
    protected JobWorkCalculationService $calcService;

    public function __construct(JobWorkCalculationService $calcService)
    {
        $this->calcService = $calcService;
    }

    private function resolveDates(?string $preset, ?string $dateFrom, ?string $dateTo): array
    {
        $today = Carbon::today();

        if ($preset && $preset !== 'custom') {
            return match ($preset) {
                'today'        => [$today->toDateString(), $today->toDateString()],
                'yesterday'    => [Carbon::yesterday()->toDateString(), Carbon::yesterday()->toDateString()],
                'this_month'   => [$today->copy()->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()],
                'last_month'   => [$today->copy()->subMonth()->startOfMonth()->toDateString(), $today->copy()->subMonth()->endOfMonth()->toDateString()],
                'last_3months' => [$today->copy()->subMonths(3)->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()],
                'this_year'    => [$today->copy()->startOfYear()->toDateString(), $today->copy()->endOfYear()->toDateString()],
                'last_year'    => [$today->copy()->subYear()->startOfYear()->toDateString(), $today->copy()->subYear()->endOfYear()->toDateString()],
                default        => [$dateFrom ? Carbon::parse($dateFrom)->toDateString() : '', $dateTo ? Carbon::parse($dateTo)->toDateString() : ''],
            };
        }

        $from = $dateFrom ? Carbon::parse($dateFrom)->toDateString() : '';
        $to   = $dateTo ? Carbon::parse($dateTo)->toDateString() : '';

        return [$from, $to];
    }

    /**
     * Job Work Dashboard / KPI Overview
     */
    public function dashboard(Request $request)
    {
        $totalOrders          = JobWorkOrder::count();
        $materialReceivedKg   = (float) JobWorkOrder::sum('total_received_weight_kg');
        $grossPieces          = (float) JobWorkOrder::sum('total_gross_pieces');
        $wastagePieces        = (float) JobWorkOrder::sum('total_wastage_pieces');
        $netPieces            = (float) JobWorkOrder::sum('total_net_pieces');
        $deliveredPieces      = (float) JobWorkOrder::sum('total_delivered_pieces');
        $pendingPieces        = (float) JobWorkOrder::sum('total_balance_pieces');
        $totalAmount          = (float) JobWorkOrder::sum('grand_total');
        $paidAmount           = (float) JobWorkOrder::sum('paid_amount');
        $balanceAmount        = (float) JobWorkOrder::sum('balance_amount');

        $statusCounts = JobWorkOrder::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $recentOrders = JobWorkOrder::with('client', 'items.product')
            ->latest('order_date')
            ->latest('id')
            ->limit(10)
            ->get();

        return view('jobworks.dashboard', compact(
            'totalOrders',
            'materialReceivedKg',
            'grossPieces',
            'wastagePieces',
            'netPieces',
            'deliveredPieces',
            'pendingPieces',
            'totalAmount',
            'paidAmount',
            'balanceAmount',
            'statusCounts',
            'recentOrders'
        ));
    }

    /**
     * List all Job Work Orders with search and filters
     */
    public function index(Request $request)
    {
        $preset   = $request->get('preset', '');
        $dateFrom = $request->get('date_from', '');
        $dateTo   = $request->get('date_to', '');
        $status   = $request->get('status', '');
        $clientId = $request->get('client_id', '');
        $search   = $request->get('search', '');

        [$dateFrom, $dateTo] = $this->resolveDates($preset, $dateFrom, $dateTo);

        $query = JobWorkOrder::with('client', 'items.product')->latest('order_date')->latest('id');

        if ($dateFrom) $query->whereDate('order_date', '>=', $dateFrom);
        if ($dateTo)   $query->whereDate('order_date', '<=', $dateTo);
        if ($status)   $query->where('status', $status);
        if ($clientId) $query->where('client_id', $clientId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('job_work_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('company_name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Clone for totals
        $totalsQuery = clone $query;
        $totalReceivedKg = (float) $totalsQuery->sum('total_received_weight_kg');
        $totalGross      = (float) $totalsQuery->sum('total_gross_pieces');
        $totalWastage    = (float) $totalsQuery->sum('total_wastage_pieces');
        $totalNet        = (float) $totalsQuery->sum('total_net_pieces');
        $totalDelivered  = (float) $totalsQuery->sum('total_delivered_pieces');
        $totalBalance    = (float) $totalsQuery->sum('total_balance_pieces');
        $totalGrandTotal = (float) $totalsQuery->sum('grand_total');
        $totalCount      = $totalsQuery->count();

        $orders = $query->paginate(25)->appends($request->query());
        $clients = JobWorkClient::where('is_active', true)->orderBy('name')->get();

        return view('jobworks.index', compact(
            'orders',
            'clients',
            'preset',
            'dateFrom',
            'dateTo',
            'status',
            'clientId',
            'search',
            'totalReceivedKg',
            'totalGross',
            'totalWastage',
            'totalNet',
            'totalDelivered',
            'totalBalance',
            'totalGrandTotal',
            'totalCount'
        ));
    }

    /**
     * Job Work Creation Screen
     */
    public function create()
    {
        $clients = JobWorkClient::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)
            ->where('job_work_applicable', true)
            ->orderBy('name')
            ->get();

        // Generate next sequential Job Work number e.g. JW-202608-0001
        $prefix = 'JW-' . date('Ym') . '-';
        $lastOrder = JobWorkOrder::where('job_work_number', 'like', $prefix . '%')
            ->orderBy('job_work_number', 'desc')
            ->first();

        $nextNum = 1;
        if ($lastOrder) {
            $lastSeq = (int) substr($lastOrder->job_work_number, -4);
            $nextNum = $lastSeq + 1;
        }
        $nextJobWorkNumber = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        $duplicateFrom = null;

        return view('jobworks.create', compact('clients', 'products', 'nextJobWorkNumber', 'duplicateFrom'));
    }

    /**
     * Store new Job Work Order with automatic backend calculation & snapshot
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id'          => 'required|exists:job_work_clients,id',
            'job_work_number'    => 'required|string|max:50|unique:job_work_orders,job_work_number',
            'order_date'         => 'required|date',
            'due_date'           => 'nullable|date|after_or_equal:order_date',
            'reference_number'   => 'nullable|string|max:100',
            'status'             => 'required|in:Draft,Material Received,In Production,Partially Completed,Completed,Delivered,Cancelled',
            'rounding_method'    => 'required|in:floor,round,ceil,decimal',
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
            'items.*.wastage_type'        => 'required|in:percentage,fixed,none',
            'items.*.wastage_percentage'  => 'nullable|numeric|min:0|max:100',
            'items.*.fixed_wastage'       => 'nullable|numeric|min:0',
            'items.*.rate_type'           => 'required|in:per_piece,per_kg,fixed',
            'items.*.rate'                => 'nullable|numeric|min:0',
            'items.*.remarks'             => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $roundingMethod = $request->rounding_method ?? 'floor';
            $calculatedItems = [];

            foreach ($request->items as $rawItem) {
                $product = Product::findOrFail($rawItem['product_id']);

                // Use provided or snapshot product master weight
                $pieceWeight     = !empty($rawItem['product_weight']) && $rawItem['product_weight'] > 0 
                    ? (float) $rawItem['product_weight'] 
                    : (float) ($product->weight_per_piece ?: 10);

                $pieceWeightUnit = !empty($rawItem['product_weight_unit']) 
                    ? $rawItem['product_weight_unit'] 
                    : ($product->weight_unit ?: 'Gram');

                $wastageType = $rawItem['wastage_type'] ?? 'percentage';
                $wastagePct  = isset($rawItem['wastage_percentage']) 
                    ? (float) $rawItem['wastage_percentage'] 
                    : (float) ($product->wastage_percentage ?? 0);

                $fixedWastage = isset($rawItem['fixed_wastage']) ? (float) $rawItem['fixed_wastage'] : 0;
                $rateType     = $rawItem['rate_type'] ?? 'per_piece';
                $rate         = (float) ($rawItem['rate'] ?? 0);

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
                    'delivered_quantity'   => 0,
                ], $roundingMethod);

                $calc['product_id'] = $product->id;
                $calc['remarks']    = $rawItem['remarks'] ?? null;
                $calculatedItems[]  = $calc;
            }

            // Calculate Order Totals
            $additionalCharges = (float) ($request->additional_charges ?? 0);
            $discount          = (float) ($request->discount ?? 0);
            $tax               = (float) ($request->tax ?? 0);
            $paidAmount        = (float) ($request->paid_amount ?? 0);

            $totals = $this->calcService->calculateOrderTotals(
                $calculatedItems,
                $additionalCharges,
                $discount,
                $tax,
                $paidAmount
            );

            // Create Order Header
            $order = JobWorkOrder::create(array_merge([
                'job_work_number'    => $request->job_work_number,
                'client_id'          => $request->client_id,
                'order_date'         => $request->order_date,
                'due_date'           => $request->due_date,
                'reference_number'   => $request->reference_number,
                'status'             => $request->status,
                'rounding_method'    => $roundingMethod,
                'remarks'            => $request->remarks,
                'created_by'         => Auth::id(),
            ], $totals));

            // Create Order Items
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

            // Audit Log
            JobWorkAuditLog::create([
                'job_work_order_id' => $order->id,
                'user_id'           => Auth::id(),
                'action'            => 'Created',
                'field_name'        => 'Order Created',
                'new_value'         => "Created with {$order->items()->count()} product(s). Net: " . number_format($order->total_net_pieces) . " PCS, Grand Total: ₹" . number_format($order->grand_total, 2),
                'notes'             => 'Initial Job Work entry registered.',
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Job Work order ' . $order->job_work_number . ' created successfully.',
                    'order'   => $order->load('client', 'items.product'),
                ]);
            }

            return redirect()->route('jobworks.show', $order)->with('success', 'Job Work ' . $order->job_work_number . ' created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create Job Work order: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Error creating Job Work: ' . $e->getMessage());
        }
    }

    /**
     * Show Job Work Details & Lifecycle
     */
    public function show(JobWorkOrder $jobWorkOrder)
    {
        $jobWorkOrder->load([
            'client',
            'items.product',
            'deliveries.transporter',
            'deliveries.items.orderItem.product',
            'deliveries.creator',
            'auditLogs.user',
            'creator'
        ]);

        $transporters = Transporter::orderBy('name')->get();

        return view('jobworks.show', compact('jobWorkOrder', 'transporters'));
    }

    /**
     * Edit Job Work Order
     */
    public function edit(JobWorkOrder $jobWorkOrder)
    {
        $jobWorkOrder->load('client', 'items.product');
        $clients = JobWorkClient::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)
            ->where('job_work_applicable', true)
            ->orderBy('name')
            ->get();

        return view('jobworks.edit', compact('jobWorkOrder', 'clients', 'products'));
    }

    /**
     * Update existing Job Work Order
     */
    public function update(Request $request, JobWorkOrder $jobWorkOrder)
    {
        $request->validate([
            'client_id'          => 'required|exists:job_work_clients,id',
            'order_date'         => 'required|date',
            'due_date'           => 'nullable|date|after_or_equal:order_date',
            'reference_number'   => 'nullable|string|max:100',
            'status'             => 'required|in:Draft,Material Received,In Production,Partially Completed,Completed,Delivered,Cancelled',
            'rounding_method'    => 'required|in:floor,round,ceil,decimal',
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
            'items.*.wastage_type'        => 'required|in:percentage,fixed,none',
            'items.*.wastage_percentage'  => 'nullable|numeric|min:0|max:100',
            'items.*.fixed_wastage'       => 'nullable|numeric|min:0',
            'items.*.rate_type'           => 'required|in:per_piece,per_kg,fixed',
            'items.*.rate'                => 'nullable|numeric|min:0',
            'items.*.remarks'             => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $jobWorkOrder->status;
            $oldTotal  = $jobWorkOrder->grand_total;
            $roundingMethod = $request->rounding_method ?? 'floor';

            // Delete old items and recalculate
            $existingDeliveredMap = $jobWorkOrder->items->pluck('delivered_quantity', 'product_id')->toArray();
            $jobWorkOrder->items()->delete();

            $calculatedItems = [];
            foreach ($request->items as $rawItem) {
                $product = Product::findOrFail($rawItem['product_id']);

                $pieceWeight     = !empty($rawItem['product_weight']) && $rawItem['product_weight'] > 0 
                    ? (float) $rawItem['product_weight'] 
                    : (float) ($product->weight_per_piece ?: 10);

                $pieceWeightUnit = !empty($rawItem['product_weight_unit']) 
                    ? $rawItem['product_weight_unit'] 
                    : ($product->weight_unit ?: 'Gram');

                $wastageType = $rawItem['wastage_type'] ?? 'percentage';
                $wastagePct  = isset($rawItem['wastage_percentage']) 
                    ? (float) $rawItem['wastage_percentage'] 
                    : (float) ($product->wastage_percentage ?? 0);

                $fixedWastage = isset($rawItem['fixed_wastage']) ? (float) $rawItem['fixed_wastage'] : 0;
                $rateType     = $rawItem['rate_type'] ?? 'per_piece';
                $rate         = (float) ($rawItem['rate'] ?? 0);
                $deliveredQty = (float) ($existingDeliveredMap[$product->id] ?? 0);

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
                    'delivered_quantity'   => $deliveredQty,
                ], $roundingMethod);

                $calc['product_id'] = $product->id;
                $calc['remarks']    = $rawItem['remarks'] ?? null;
                $calculatedItems[]  = $calc;
            }

            $additionalCharges = (float) ($request->additional_charges ?? 0);
            $discount          = (float) ($request->discount ?? 0);
            $tax               = (float) ($request->tax ?? 0);
            $paidAmount        = (float) ($request->paid_amount ?? 0);

            $totals = $this->calcService->calculateOrderTotals(
                $calculatedItems,
                $additionalCharges,
                $discount,
                $tax,
                $paidAmount
            );

            $jobWorkOrder->update(array_merge([
                'client_id'          => $request->client_id,
                'order_date'         => $request->order_date,
                'due_date'           => $request->due_date,
                'reference_number'   => $request->reference_number,
                'status'             => $request->status,
                'rounding_method'    => $roundingMethod,
                'remarks'            => $request->remarks,
            ], $totals));

            foreach ($calculatedItems as $itemData) {
                $jobWorkOrder->items()->create([
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
                    'delivered_quantity'    => $itemData['delivered_quantity'],
                    'balance_quantity'      => $itemData['balance_quantity'],
                    'rate_type'             => $itemData['rate_type'],
                    'rate'                  => $itemData['rate'],
                    'amount'                => $itemData['amount'],
                    'remarks'               => $itemData['remarks'],
                ]);
            }

            // Audit Log
            JobWorkAuditLog::create([
                'job_work_order_id' => $jobWorkOrder->id,
                'user_id'           => Auth::id(),
                'action'            => 'Edited',
                'field_name'        => 'Order Details',
                'old_value'         => "Total: ₹" . number_format($oldTotal, 2) . ", Status: {$oldStatus}",
                'new_value'         => "Total: ₹" . number_format($jobWorkOrder->grand_total, 2) . ", Status: {$jobWorkOrder->status}",
                'notes'             => 'Job Work record updated.',
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Job Work order updated successfully.',
                    'order'   => $jobWorkOrder->load('client', 'items.product'),
                ]);
            }

            return redirect()->route('jobworks.show', $jobWorkOrder)->with('success', 'Job Work updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update order: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Error updating Job Work: ' . $e->getMessage());
        }
    }

    /**
     * Delete Job Work Order
     */
    public function destroy(JobWorkOrder $jobWorkOrder)
    {
        $jwNum = $jobWorkOrder->job_work_number;
        $jobWorkOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job Work order ' . $jwNum . ' deleted successfully.',
        ]);
    }

    /**
     * Clone an existing order
     */
    public function duplicate(JobWorkOrder $jobWorkOrder)
    {
        $jobWorkOrder->load('client', 'items.product');
        $clients = JobWorkClient::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->where('job_work_applicable', true)->orderBy('name')->get();

        $prefix = 'JW-' . date('Ym') . '-';
        $lastOrder = JobWorkOrder::where('job_work_number', 'like', $prefix . '%')
            ->orderBy('job_work_number', 'desc')
            ->first();
        $nextNum = $lastOrder ? ((int) substr($lastOrder->job_work_number, -4) + 1) : 1;
        $nextJobWorkNumber = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        return view('jobworks.create', [
            'clients'           => $clients,
            'products'          => $products,
            'nextJobWorkNumber' => $nextJobWorkNumber,
            'duplicateFrom'     => $jobWorkOrder,
        ]);
    }

    /**
     * Update status with audit log
     */
    public function updateStatus(Request $request, JobWorkOrder $jobWorkOrder)
    {
        $request->validate([
            'status' => 'required|in:Draft,Material Received,In Production,Partially Completed,Completed,Delivered,Cancelled',
            'notes'  => 'nullable|string',
        ]);

        $oldStatus = $jobWorkOrder->status;
        $jobWorkOrder->status = $request->status;
        $jobWorkOrder->save();

        JobWorkAuditLog::create([
            'job_work_order_id' => $jobWorkOrder->id,
            'user_id'           => Auth::id(),
            'action'            => 'Status Changed',
            'field_name'        => 'status',
            'old_value'         => $oldStatus,
            'new_value'         => $request->status,
            'notes'             => $request->notes ?: 'Status manually changed.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated to ' . $request->status,
            'status'  => $request->status,
        ]);
    }

    /**
     * Record a delivery batch against Job Work net finished pieces
     */
    public function recordDelivery(Request $request, JobWorkOrder $jobWorkOrder)
    {
        $request->validate([
            'delivery_date'          => 'required|date',
            'challan_number'         => 'nullable|string|max:100',
            'vehicle_number'         => 'nullable|string|max:50',
            'transporter_id'         => 'nullable|exists:transporters,id',
            'notes'                  => 'nullable|string',
            'items'                  => 'required|array|min:1',
            'items.*.item_id'        => 'required|exists:job_work_order_items,id',
            'items.*.quantity'       => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $totalDeliveredInThisBatch = 0;

            // Generate delivery number e.g. JWD-202608-0001
            $prefix = 'JWD-' . date('Ym') . '-';
            $lastDelivery = JobWorkDelivery::where('delivery_number', 'like', $prefix . '%')
                ->orderBy('delivery_number', 'desc')
                ->first();
            $nextNum = $lastDelivery ? ((int) substr($lastDelivery->delivery_number, -4) + 1) : 1;
            $deliveryNumber = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            $delivery = JobWorkDelivery::create([
                'job_work_order_id' => $jobWorkOrder->id,
                'delivery_number'   => $deliveryNumber,
                'delivery_date'     => $request->delivery_date,
                'challan_number'    => $request->challan_number,
                'vehicle_number'    => $request->vehicle_number,
                'transporter_id'    => $request->transporter_id,
                'notes'             => $request->notes,
                'created_by'        => Auth::id(),
            ]);

            foreach ($request->items as $row) {
                $qty = (float) $row['quantity'];
                if ($qty <= 0) continue;

                $orderItem = JobWorkOrderItem::where('job_work_order_id', $jobWorkOrder->id)
                    ->where('id', $row['item_id'])
                    ->firstOrFail();

                // Validate delivery quantity does not exceed remaining balance
                $availableBalance = (float) $orderItem->net_quantity - (float) $orderItem->delivered_quantity;
                if ($qty > ($availableBalance + 0.001)) {
                    throw new \Exception("Delivery quantity ({$qty} PCS) cannot exceed available balance ({$availableBalance} PCS) for product {$orderItem->product->name}.");
                }

                $delivery->items()->create([
                    'job_work_order_item_id' => $orderItem->id,
                    'delivered_quantity'     => $qty,
                    'remarks'                => $row['remarks'] ?? null,
                ]);

                $orderItem->delivered_quantity += $qty;
                $orderItem->balance_quantity = max(0, (float) $orderItem->net_quantity - (float) $orderItem->delivered_quantity);
                $orderItem->save();

                $totalDeliveredInThisBatch += $qty;
            }

            // Refresh order level totals & delivery status
            $jobWorkOrder->refreshTotals();

            // Audit Log
            JobWorkAuditLog::create([
                'job_work_order_id' => $jobWorkOrder->id,
                'user_id'           => Auth::id(),
                'action'            => 'Delivery Recorded',
                'field_name'        => 'Delivery Challan: ' . $delivery->delivery_number,
                'new_value'         => number_format($totalDeliveredInThisBatch) . " PCS delivered. Total delivered: " . number_format($jobWorkOrder->total_delivered_pieces) . " PCS.",
                'notes'             => $request->notes ?: 'Dispatched finished products.',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Delivery ' . $delivery->delivery_number . ' recorded successfully (' . number_format($totalDeliveredInThisBatch) . ' PCS).',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Real-time AJAX calculation helper
     */
    public function calculateAjax(Request $request)
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

    /**
     * Printable A4 Job Work document / Challan
     */
    public function print(JobWorkOrder $jobWorkOrder)
    {
        $jobWorkOrder->load('client', 'items.product', 'creator');
        return view('jobworks.print', compact('jobWorkOrder'));
    }

    /**
     * Job Work Analytics Reports
     */
    public function reports(Request $request)
    {
        $reportType = $request->get('type', 'client'); // client, product, date
        $preset     = $request->get('preset', 'this_month');
        $dateFrom   = $request->get('date_from', '');
        $dateTo     = $request->get('date_to', '');

        [$dateFrom, $dateTo] = $this->resolveDates($preset, $dateFrom, $dateTo);

        $clientReport = [];
        $productReport = [];
        $dateReport = [];

        if ($reportType === 'client' || $reportType === 'all') {
            $clientReport = JobWorkClient::with(['orders' => function ($q) use ($dateFrom, $dateTo) {
                if ($dateFrom) $q->whereDate('order_date', '>=', $dateFrom);
                if ($dateTo)   $q->whereDate('order_date', '<=', $dateTo);
            }])->get()->map(function ($c) {
                return [
                    'client_name'       => $c->name,
                    'company_name'      => $c->company_name,
                    'phone'             => $c->phone,
                    'total_orders'      => $c->orders->count(),
                    'total_material_kg' => $c->orders->sum('total_received_weight_kg'),
                    'total_gross_pcs'   => $c->orders->sum('total_gross_pieces'),
                    'total_wastage_pcs' => $c->orders->sum('total_wastage_pieces'),
                    'total_net_pcs'     => $c->orders->sum('total_net_pieces'),
                    'total_delivered'   => $c->orders->sum('total_delivered_pieces'),
                    'balance_pcs'       => $c->orders->sum('total_balance_pieces'),
                    'total_amount'      => $c->orders->sum('grand_total'),
                    'balance_amount'    => $c->orders->sum('balance_amount'),
                ];
            })->filter(fn($c) => $c['total_orders'] > 0);
        }

        if ($reportType === 'product' || $reportType === 'all') {
            $productReport = JobWorkOrderItem::whereHas('order', function ($q) use ($dateFrom, $dateTo) {
                if ($dateFrom) $q->whereDate('order_date', '>=', $dateFrom);
                if ($dateTo)   $q->whereDate('order_date', '<=', $dateTo);
            })->with('product', 'order')
              ->get()
              ->groupBy('product_id')
              ->map(function ($group) {
                  $prod = $group->first()->product;
                  return [
                      'product_name'      => $prod->name,
                      'sku'               => $prod->sku,
                      'piece_weight'      => $prod->weight_per_piece . ' ' . $prod->weight_unit,
                      'total_material_kg' => $group->sum(fn($i) => $i->received_weight_grams / 1000),
                      'total_gross_pcs'   => $group->sum('gross_quantity'),
                      'total_wastage_pcs' => $group->sum('wastage_quantity'),
                      'total_net_pcs'     => $group->sum('net_quantity'),
                      'total_delivered'   => $group->sum('delivered_quantity'),
                      'balance_pcs'       => $group->sum('balance_quantity'),
                      'total_amount'      => $group->sum('amount'),
                  ];
              });
        }

        if ($reportType === 'date' || $reportType === 'all') {
            $dateQuery = JobWorkOrder::query();
            if ($dateFrom) $dateQuery->whereDate('order_date', '>=', $dateFrom);
            if ($dateTo)   $dateQuery->whereDate('order_date', '<=', $dateTo);

            $dateReport = $dateQuery->orderBy('order_date', 'desc')
                ->get()
                ->groupBy(fn($o) => $o->order_date->format('Y-m-d'))
                ->map(function ($group, $date) {
                    return [
                        'date'              => $date,
                        'orders_count'      => $group->count(),
                        'total_material_kg' => $group->sum('total_received_weight_kg'),
                        'total_gross_pcs'   => $group->sum('total_gross_pieces'),
                        'total_wastage_pcs' => $group->sum('total_wastage_pieces'),
                        'total_net_pcs'     => $group->sum('total_net_pieces'),
                        'total_delivered'   => $group->sum('total_delivered_pieces'),
                        'balance_pcs'       => $group->sum('total_balance_pieces'),
                        'total_amount'      => $group->sum('grand_total'),
                    ];
                });
        }

        return view('jobworks.reports', compact(
            'reportType',
            'preset',
            'dateFrom',
            'dateTo',
            'clientReport',
            'productReport',
            'dateReport'
        ));
    }
}
