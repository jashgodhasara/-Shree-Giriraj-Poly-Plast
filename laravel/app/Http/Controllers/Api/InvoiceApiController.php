<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceApiController extends Controller
{
    public function index()
    {
        return InvoiceResource::collection(
            Invoice::with('customer:id,name')->latest()->get()
        );
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('customer', 'items.product', 'transporter', 'payments');

        return response()->json([
            'id'              => $invoice->id,
            'invoice_number'  => $invoice->invoice_number,
            'invoice_date'    => $invoice->invoice_date->format('Y-m-d'),
            'status'          => $invoice->status,
            'subtotal'        => (float) $invoice->subtotal,
            'cgst'            => (float) $invoice->cgst,
            'sgst'            => (float) $invoice->sgst,
            'igst'            => (float) $invoice->igst,
            'grand_total'     => (float) $invoice->grand_total,
            'paid_amount'     => (float) $invoice->paid_amount,
            'pending_amount'  => (float) max(0, $invoice->grand_total - $invoice->paid_amount),
            'payment_mode'    => $invoice->payment_mode,
            'payment_terms'   => $invoice->payment_terms,
            'po_number'       => $invoice->po_number,
            'lr_number'       => $invoice->lr_number,
            'eway_bill_no'    => $invoice->eway_bill_no,
            'challan_number'  => $invoice->challan_number,
            'delivery_at'     => $invoice->delivery_at,
            'notes'           => $invoice->notes,
            'customer'        => $invoice->customer,
            'transporter'     => $invoice->transporter,
            'items'           => $invoice->items->map(fn($item) => [
                'id'           => $item->id,
                'product_id'   => $item->product_id,
                'product_name' => $item->product?->name,
                'hsn_code'     => $item->product?->hsn_code,
                'quantity'     => (int)   $item->quantity,
                'unit_price'   => (float) $item->unit_price,
                'total_price'  => (float) $item->total_price,
                'gst_rate'     => (float) ($item->product?->gst_rate ?? 0),
            ]),
            'payments' => $invoice->payments->map(fn($p) => [
                'id'           => $p->id,
                'amount'       => (float) $p->amount,
                'payment_date' => $p->payment_date->format('Y-m-d'),
                'payment_mode' => $p->payment_mode,
                'reference_no' => $p->reference_no,
                'remarks'      => $p->remarks,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'        => 'required|exists:customers,id',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'transporter_id'     => 'nullable|exists:transporters,id',
            'lr_number'          => 'nullable|string|max:50',
            'payment_terms'      => 'nullable|string|max:100',
            'po_number'          => 'nullable|string|max:100',
            'delivery_at'        => 'nullable|string',
            'eway_bill_no'       => 'nullable|string|max:50',
            'challan_number'     => 'nullable|string|max:50',
            'notes'              => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $customer     = Customer::findOrFail($request->customer_id);
            $isInterState = !empty($customer->state) && strtolower($customer->state) !== 'gujarat';

            $subtotal = $cgst = $sgst = $igst = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product   = Product::findOrFail($item['product_id']);
                $qty       = (int) $item['quantity'];
                $unitPrice = (float) $product->price;
                $lineTotal = $qty * $unitPrice;
                $gstAmt    = $lineTotal * ((float) $product->gst_rate / 100);

                $subtotal += $lineTotal;
                if ($isInterState) { $igst += $gstAmt; }
                else { $cgst += $gstAmt / 2; $sgst += $gstAmt / 2; }

                $itemsData[] = [
                    'product_id'  => $product->id,
                    'quantity'    => $qty,
                    'unit_price'  => $unitPrice,
                    'total_price' => $lineTotal,
                ];
            }

            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'customer_id'    => $customer->id,
                'transporter_id' => $request->transporter_id,
                'lr_number'      => $request->lr_number,
                'invoice_date'   => now()->toDateString(),
                'subtotal'       => $subtotal,
                'cgst'           => $cgst,
                'sgst'           => $sgst,
                'igst'           => $igst,
                'grand_total'    => $subtotal + $cgst + $sgst + $igst,
                'paid_amount'    => 0,
                'status'         => 'Unpaid',
                'payment_terms'  => $request->payment_terms,
                'po_number'      => $request->po_number,
                'delivery_at'    => $request->delivery_at,
                'eway_bill_no'   => $request->eway_bill_no,
                'challan_number' => $request->challan_number,
                'notes'          => $request->notes,
            ]);

            foreach ($itemsData as $item) {
                $invoice->items()->create($item);
            }

            DB::commit();
            return response()->json([
                'success'        => true,
                'invoice_id'     => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'grand_total'    => (float) $invoice->grand_total,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'status'         => 'sometimes|in:Paid,Unpaid,Partial',
            'payment_mode'   => 'nullable|string|max:30',
            'payment_terms'  => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
            'lr_number'      => 'nullable|string|max:50',
            'eway_bill_no'   => 'nullable|string|max:50',
            'challan_number' => 'nullable|string|max:50',
            'delivery_at'    => 'nullable|string',
            'transporter_id' => 'nullable|exists:transporters,id',
        ]);
        $invoice->update($request->only([
            'status', 'payment_mode', 'payment_terms', 'notes',
            'lr_number', 'eway_bill_no', 'challan_number', 'delivery_at', 'transporter_id',
        ]));
        return response()->json(['success' => true]);
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return response()->json(['message' => 'Invoice deleted.']);
    }
}
