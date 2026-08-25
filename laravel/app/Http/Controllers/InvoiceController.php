<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Ledger;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Transporter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Material;

use App\Services\InventoryService;

class InvoiceController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
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

    public function index(Request $request)
    {
        $preset   = $request->get('preset', '');
        $dateFrom = $request->get('date_from', '');
        $dateTo   = $request->get('date_to', '');
        $status   = $request->get('status', '');
        [$dateFrom, $dateTo] = $this->resolveDates($preset, $dateFrom, $dateTo);

        $query = Invoice::with('customer', 'transporter')->latest('invoice_date')->latest('id');
        if ($dateFrom) $query->whereDate('invoice_date', '>=', $dateFrom);
        if ($dateTo)   $query->whereDate('invoice_date', '<=', $dateTo);
        if ($status) {
            if ($status === 'Unpaid') {
                $query->where(function ($q) {
                    $q->where('status', 'Unpaid')->orWhere('status', 'Partial');
                });
            } else {
                $query->where('status', $status);
            }
        }

        $invoices = $query->paginate(50)->appends($request->query());

        // Summary totals
        $sumQuery = Invoice::query();
        if ($dateFrom) $sumQuery->whereDate('invoice_date', '>=', $dateFrom);
        if ($dateTo)   $sumQuery->whereDate('invoice_date', '<=', $dateTo);
        if ($status) {
            if ($status === 'Unpaid') {
                $sumQuery->where(function ($q) {
                    $q->where('status', 'Unpaid')->orWhere('status', 'Partial');
                });
            } else {
                $sumQuery->where('status', $status);
            }
        }
        $totalAmount  = (clone $sumQuery)->sum('grand_total');
        $totalPaid    = (clone $sumQuery)->sum('paid_amount');
        $totalCount   = (clone $sumQuery)->count();

        return view('invoices.index', compact('invoices', 'preset', 'dateFrom', 'dateTo', 'status', 'totalAmount', 'totalPaid', 'totalCount'));
    }

    public function create()
    {
        // Auto-sync any Final Products from Material Inventory to Products table
        $finalMaterials = Material::where('type', 'Final Product')->get();
        foreach ($finalMaterials as $fm) {
            Product::firstOrCreate(
                ['name' => $fm->name],
                [
                    'price'          => 0,
                    'gst_rate'       => 18,
                    'stock_quantity' => $fm->stock_quantity ?? 0,
                    'image'          => $fm->image,
                    'description'    => 'Final Product (' . ($fm->unit ?? '') . ')'
                ]
            );
        }

        $customers    = Customer::orderBy('name')->get();
        $products     = Product::orderBy('name')->get();
        $transporters = Transporter::orderBy('name')->get();
        return view('invoices.create', compact('customers', 'products', 'transporters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'        => 'required|exists:customers,id',
            'invoice_date'       => 'nullable|date',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'transporter_id'     => 'nullable|exists:transporters,id',
            'lr_number'          => 'nullable|string|max:50',
            'notes'              => 'nullable|string',
            'payment_terms'      => 'nullable|string|max:100',
            'po_number'          => 'nullable|string|max:100',
            'po_date'            => 'nullable|string|max:30',
            'delivery_at'        => 'nullable|string|max:255',
            'eway_bill_no'       => 'nullable|string|max:50',
            'challan_number'     => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $customer  = Customer::findOrFail($request->customer_id);
            $taxRegime = \App\Services\GstTaxCalculationService::determineTaxRegime(
                $customer->country,
                $customer->state,
                $customer->gstin,
                $customer->tax_type
            );

            $invoiceDate = $request->filled('invoice_date')
                ? Carbon::parse($request->invoice_date)->toDateString()
                : session('working_date', now()->toDateString());

            $subtotal  = 0;
            $cgst      = 0;
            $sgst      = 0;
            $igst      = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product   = Product::findOrFail($item['product_id']);
                $qty       = (float) $item['quantity'];
                $unitPrice = (float) ($item['unit_price'] ?? $product->price);
                $lineTotal = $qty * $unitPrice;
                $gstAmt    = $lineTotal * ((float) $product->gst_rate / 100);

                $subtotal += $lineTotal;

                $cgst += $gstAmt * $taxRegime['cgst_split'];
                $sgst += $gstAmt * $taxRegime['sgst_split'];
                $igst += $gstAmt * $taxRegime['igst_split'];

                $itemsData[] = [
                    'product'     => $product,
                    'product_id'  => $product->id,
                    'quantity'    => $qty,
                    'unit_price'  => $unitPrice,
                    'total_price' => $lineTotal,
                ];
            }

            $grandTotal = round($subtotal + $cgst + $sgst + $igst, 2);

            $invoice = Invoice::create([
                'invoice_number'  => Invoice::generateInvoiceNumber(),
                'customer_id'     => $customer->id,
                'transporter_id'  => $request->transporter_id ?: null,
                'lr_number'       => $request->lr_number ?: null,
                'invoice_date'    => $invoiceDate,
                'subtotal'        => $subtotal,
                'cgst'            => $cgst,
                'sgst'            => $sgst,
                'igst'            => $igst,
                'grand_total'     => $grandTotal,
                'paid_amount'     => 0,
                'notes'           => $request->notes ?: null,
                'status'          => 'Unpaid',
                'payment_terms'   => $request->payment_terms ?: null,
                'po_number'       => $request->po_number ?: null,
                'po_date'         => $request->po_date ?: null,
                'delivery_at'     => $request->delivery_at ?: null,
                'eway_bill_no'    => $request->eway_bill_no ?: null,
                'challan_number'  => $request->challan_number ?: null,
            ]);

            foreach ($itemsData as $item) {
                $invoice->items()->create([
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);

                // Record Sale in Centralized Inventory Engine (Atomic stock deduction + Stock Ledger entry)
                $this->inventoryService->recordSale(
                    $item['product'],
                    $item['quantity'],
                    $item['unit_price'],
                    $invoice->invoice_number,
                    $invoice->id,
                    $invoiceDate,
                    "Sales Invoice #{$invoice->invoice_number} to {$customer->name}"
                );
            }

            // Auto-post Customer Debit Ledger entry
            Ledger::create([
                'entity_type'      => 'Customer',
                'entity_id'        => $customer->id,
                'transaction_date' => $invoiceDate,
                'type'             => 'Debit',
                'amount'           => $grandTotal,
                'description'      => 'Sales Invoice #' . $invoice->invoice_number,
            ]);

            DB::commit();

            return response()->json([
                'success'    => true,
                'invoice_id' => $invoice->id,
                'message'    => 'Invoice created successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('customer', 'transporter', 'items.product', 'payments');
        return view('invoices.show', compact('invoice'));
    }

    public function print(Invoice $invoice)
    {
        $invoice->load('customer', 'transporter', 'items.product');
        return view('invoices.print', compact('invoice'));
    }

    public function challan(Invoice $invoice)
    {
        $invoice->load('customer', 'transporter', 'items.product');
        return view('invoices.challan', compact('invoice'));
    }

    public function destroy(Invoice $invoice)
    {
        DB::beginTransaction();
        try {
            $invoice->load('items.product');

            // 1. Reversal in Stock Ledger & restore stock via Inventory Engine
            $this->inventoryService->reverseByReference('Invoices', $invoice->id, 'Sales Invoice Cancellation');

            // 2. Remove matching Customer Debit ledger entry
            Ledger::where('entity_type', 'Customer')
                ->where('entity_id', $invoice->customer_id)
                ->where('description', 'Sales Invoice #' . $invoice->invoice_number)
                ->delete();

            // 3. Delete associated payments & items
            $invoice->payments()->delete();
            $invoice->items()->delete();
            $invoice->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Invoice deleted and stock restored successfully.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function markStatus(Request $request, Invoice $invoice)
    {
        $request->validate([
            'status' => 'required|in:Paid,Unpaid,Partial',
        ]);
        $invoice->update(['status' => $request->status]);
        return response()->json(['success' => true, 'message' => 'Status updated.']);
    }

    public function payments(Invoice $invoice)
    {
        $invoice->load('payments');
        return response()->json([
            'payments'       => $invoice->payments,
            'grand_total'    => $invoice->grand_total,
            'paid_amount'    => $invoice->payments->sum('amount'),
            'pending_amount' => $invoice->pending_amount,
            'status'         => $invoice->status,
        ]);
    }
}
